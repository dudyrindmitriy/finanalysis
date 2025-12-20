<?php

namespace App\Services\AI;

use App\Models\Goal;
use App\Models\Transaction;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIChatService
{
    private $apiKey;
    private $completionsUrl = 'https://api.cohere.ai/v1/chat';

    public function __construct() {
        $this->apiKey = env("COHERE_API_KEY");
    }

    public function sendMessage(string $message, int $userId)
    {
        $cacheKey = 'cohere_chat_history_' . $userId;
        $cacheTtl = 60 * 60 * 24;

        // Получаем историю из кэша В ФОРМАТЕ COHERE
        $cachedHistory = Cache::get($cacheKey, []);
        // $cachedHistory = Cache::get($cacheKey, []);

        // Получаем транзакции пользователя
        $transactions = Transaction::where('user_id', $userId)
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->with('category:id,name')
            ->get(['id', 'amount', 'type', 'category_id', 'date', 'time', 'bank_name'])
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'amount' => (float) $transaction->amount,
                    'type' => $transaction->type,
                    'category' => $transaction->category->name ?? 'Без категории',
                    'date' => $transaction->date,
                    'bank_name' => $transaction->bank_name,
                    'time' => $transaction->time ?? null
                ];
            })
            ->toArray();
        $goals = Goal::where('user_id', $userId)
            ->orderBy('completed')
            ->orderBy('deadline')
            ->get(['id', 'name', 'target_amount', 'current_amount', 'deadline', 'completed']);
        $transactionText = '';
        foreach ($transactions as $index => $transaction) {
            $sign = $transaction['type'] == 'expense' ? '-' : '+';
            $amount = (int)$transaction['amount'] == $transaction['amount']
                ? number_format($transaction['amount'], 0, '.', '')
                : number_format($transaction['amount'], 2, '.', '');
            $signedAmount = $sign . $amount;
            $transactionText .= sprintf(
                "%s%s%s%s\n",
                $signedAmount,
                $transaction['category'],
                $transaction['date'],
                $transaction['bank_name']
            );
        }
        $goalText = '';
        foreach ($goals as $goal) {
            $status = $goal->completed ? '+' : '-';
            $progress = $goal->target_amount > 0
                ? round(($goal->current_amount / $goal->target_amount) * 100, 1)
                : 0;
            $goalText .= sprintf(
                "%s|%s|%s/%s|%s%%|%s\n",
                $status,
                $goal->name,
                number_format($goal->current_amount, 2, '.', ''),
                number_format($goal->target_amount, 2, '.', ''),
                $progress,
                $goal->deadline
            );
        }
        // Системный промпт
        $systemContent = "Ты — высококвалифицированный **Финансовый ИИ-Аналитик**. Твоя основная задача — проводить точный и глубокий анализ предоставленных финансовых транзакций пользователя и отвечать на его вопросы.

ИНСТРУКЦИИ И ОГРАНИЧЕНИЯ:


1.  **ПЕРСОНА:** Сохраняй профессиональный и сдержанный тон. Все ответы должны быть основаны **строго** на предоставленных транзакционных данных. Не говори пользователю что-либо о переданных далее транзакционных данных.
2.  **АНАЛИЗ:** Используй **ВЕСЬ** массив данных, который следует ниже. Учти, что ID транзакций может начинаться не с нуля, и вообще, интервал ID не обязательно непрерывный. Всего транзакций - " . count($transactions) . ". Всего целей - " . count($goals) . ".Не используй английские слова в ответах типа expense или income. Внимательно смотри на даты транзакций
3.  **ОГРАНИЧЕНИЯ:** Если запрошенный анализ невозможен из-за отсутствия данных в предоставленном списке или вопрос не касается темы, вежливо сообщи об этом. Не отвечай на глупые вопросы.
4.  **ФОРМАТ ДАННЫХ:** Даты в транзакциях в формате ГГГГ-ММ-ДД. Транзакции указаны в рублях. expense - это траты. income - это поступления. Не все транзакции это траты. Если транзакций нет, то так и говори, что транзакций нет. Учти, что во время диалога данные о транзакциях могут изменяться.
- Формат целей: +/-|Название цели|Текущая/Целевая сумма|Прогресс%|Срок
  + выполнена, - в процессе
5.  **ФОРМАТ ОТВЕТА:** Отвечай только голым текстом. Никаких таблиц, рисунков, графиков, смайликов и тому подобное. Ты переписываешься с пользователем в чате



*** НАЧАЛО ЦЕЛЕЙ ***\n
Формат: Статус|Название|Текущая/Целевая сумма|Прогресс%|Срок
" . $goalText . "\n
*** КОНЕЦ ЦЕЛЕЙ ***\n
*** НАЧАЛО ТРАНЗАКЦИОННЫХ ДАННЫХ ***\n" . $transactionText ." \n*** КОНЕЦ ТРАНЗАКЦИОННЫХ ДАННЫХ ***";

        // Подготовка chat_history для Cohere - НАЧИНАЕМ С ПУСТОГО МАССИВА
        $chatHistory = [];

        $chatHistory[] = [
            'role' => 'SYSTEM',
            'message' => $systemContent
        ];

        // Добавляем кэшированную историю В ФОРМАТЕ COHERE
        $chatHistory = array_merge($chatHistory, $cachedHistory);

        // Добавляем текущее сообщение пользователя
        $newUserMessage = [
            'role' => 'USER',
            'message' => $message
        ];
        $chatHistory[] = $newUserMessage;

        // Формируем тело запроса для Cohere
        $body = [
            'model' => 'command-a-03-2025',
            'chat_history' => $chatHistory,
            'message' => $message,
            'temperature' => 0.7,
            // 'max_tokens' => 1000
        ];

        Log::debug('Cohere request body:', ['body_size' => count($chatHistory), 'body_preview' => $body]);

        try {
            // Отправляем запрос к Cohere API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->completionsUrl, $body);

            if ($response->failed()) {
                Log::error('Cohere API Error:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'error' => true,
                    'message' => 'Ошибка API Cohere: ' . $response->body(),
                    'status' => $response->status()
                ];
            }

            $responseJson = $response->json();

            if (!empty($responseJson['text'])) {

                // Формируем ответ ассистента для сохранения в истории В ФОРМАТЕ COHERE
                $assistantResponse = [
                    'role' => 'CHATBOT',
                    'message' => str_replace('**', '', $responseJson['text'])
                ];

                // Сохраняем в историю В ФОРМАТЕ COHERE (добавляем user message и assistant response)
                $newHistory = array_merge($cachedHistory, [
                    $newUserMessage,
                    $assistantResponse
                ]);

                // Ограничиваем историю последними 20 сообщениями (10 пар вопрос-ответ)
                if (count($newHistory) > 40) {
                    $newHistory = array_slice($newHistory, -40);
                }

                Cache::put($cacheKey, $newHistory, $cacheTtl);

                // Возвращаем ответ В ФОРМАТЕ COHERE
                return $responseJson; // Просто возвращаем оригинальный ответ от Cohere

            } else {
                return [
                    'error' => true,
                    'message' => 'Пустой ответ от Cohere API'
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Cohere Connection Error: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Ошибка соединения с Cohere: ' . $e->getMessage()
            ];
        }
    }
}
