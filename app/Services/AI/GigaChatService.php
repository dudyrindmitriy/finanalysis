<?php

namespace App\Services\AI;

use App\Models\Transaction;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GigaChatService
{
    private $accessUrl = 'https://ngw.devices.sberbank.ru:9443/api/v2/oauth';
    private $completionsUrl = 'https://gigachat.devices.sberbank.ru/api/v1/chat/completions';

    public function __construct() {}

    private function getAccessToken()
    {
        $token = Cache::get('gigachat_access_token');
        if ($token) {
            return $token;
        }
        try {
            $response = Http::asForm()
                ->acceptJson()
                ->withHeaders([
                    'RqUID' => '25414432-fb59-4cca-b8e4-a897137d39a3',
                    'Authorization' => 'Basic ' . env('GIGACHAT_AUTH_BASIC'),
                ])
                ->withoutVerifying()
                ->post($this->accessUrl, [
                    'scope' => 'GIGACHAT_API_PERS',
                ]);
            if ($response->failed() || !isset($response['access_token'])) {
                throw new \Exception('Не удалось получить Access Token GigaChat: ' . $response->body());
            }
            $expiresAt = Carbon::createFromTimestamp(floor($response['expires_at'] / 1000));
            Cache::put('gigachat_access_token', $response['access_token'], $expiresAt);
            return $response['access_token'];
        } catch (\Throwable $e) {
            Log::error('GigaChat Token Error: ' . $e->getMessage());
            throw new \RuntimeException('Ошибка аутентификации GigaChat. Проверьте VPN');
        }
    }

    public function sendMessage(string $message, int $userId)
    {
        $cacheKey = 'giga_chat_history_' . $userId;
        $cacheTtl = 60 * 60 * 24;

        try {
            $token = $this->getAccessToken();
        } catch (\RuntimeException $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $cachedHistory = Cache::get($cacheKey, []);

        $transactions = Transaction::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->with('category:id,name')
            ->get(['id', 'amount', 'type', 'category_id', 'date', 'time', 'bank_name'])
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'type' => $transaction->type,
                    'category' => $transaction->category->name ?? 'Без категории',
                    'date' => $transaction->date,
                    'bank_name' => $transaction->bank_name,
                    'time' => $transaction->time ?? null
                ];
            })
            ->toArray();

        $transactionData = json_encode($transactions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $systemContent = "Ты — высококвалифицированный **Финансовый ИИ-Аналитик**. Твоя основная задача — проводить точный и глубокий анализ предоставленных финансовых транзакций пользователя и отвечать на его вопросы.

ИНСТРУКЦИИ И ОГРАНИЧЕНИЯ:
*** КРИТИЧЕСКИ ВАЖНЫЕ ПРАВИЛА ***
1. ЗАПРЕЩЕНО называть какие-либо цифры, суммы, проценты, количества.
2. ЗАПРЕЩЕНО делать какие-либо расчеты — даже приблизительные.
3. ЗАПРЕЩЕНО использовать числовые выражения в любом виде.

*** РАЗРЕШЕНО ТОЛЬКО ***
- Общая характеристика трат: 'преимущественно небольшие', 'средние по объему', 'крупные'
- Качественная оценка категорий: 'чаще всего в категории X', 'реже в категории Y'
- Временные паттерны: 'больше трат в начале месяца', 'равномерное распределение'
- Общие наблюдения: 'разнообразные транзакции', 'преобладают расходы над доходами'

1.  **ПЕРСОНА:** Сохраняй профессиональный и сдержанный тон. Все ответы должны быть основаны **строго** на предоставленных транзакционных данных.
2.  **АНАЛИЗ:** Используй **ВЕСЬ** массив данных, который следует ниже. Учти, что ID транзакций может начинаться не с нуля, и вообще, интервал ID не обязательно непрерывный. Всего транзакций - " . count($transactions).". Не используй английские слова в ответах типа expense или income.
3.  **ОГРАНИЧЕНИЯ:** Если запрошенный анализ невозможен из-за отсутствия данных в предоставленном списке или вопрос не касается темы, вежливо сообщи об этом. Не отвечай на глупые вопросы.
4.  **ФОРМАТ ДАННЫХ:** Даты в транзакциях в формате ГГГГ-ММ-ДД. Транзакции указаны в рублях. expense - это траты. income - это поступления. Не все транзакции это траты. Если транзакций нет, то так и говори, что транзакций нет. Учти, что во время диалога данные о транзакциях могут изменяться.
5.  **ФОРМАТ ОТВЕТА:** Отвечай только голым текстом. Никаких таблиц, рисунков, графиков, смайликов и тому подобное. Ты переписываешься с пользователем в чате

*** НАЧАЛО ТРАНЗАКЦИОННЫХ ДАННЫХ ***\n" . $transactionData;

        $messages = [];

        $messages[] = [
            'role' => 'system',
            'content' => $systemContent,
        ];

        $messages = array_merge($messages, $cachedHistory);

        $newUserMessage = [
            'role' => 'user',
            'content' => $message
        ];
        $messages[] = $newUserMessage;

        $body = [
            'model' => 'GigaChat-2',
            'messages' => $messages,
            // 'temperature' => 0.3,
        ];

        try {
            $response = Http::withBody(json_encode($body), 'application/json')
                ->withToken($token)
                ->withoutVerifying()
                ->withHeaders([
                    'X-Session-ID' => "user_{$userId}",
                ])
                ->post($this->completionsUrl);

            if ($response->failed()) {
                return [
                    'error' => true,
                    'message' => 'Ошибка API GigaChat: ' . $response->body(),
                    'status' => $response->status()
                ];
            }

            $responseJson = $response->json();

            if (!empty($responseJson['choices'][0]['message']['content'])) {

                $assistantResponse = [
                    'role' => 'assistant',
                    'content' => $responseJson['choices'][0]['message']['content']
                ];

                $newHistory = array_merge($cachedHistory, [
                    $newUserMessage,
                    $assistantResponse
                ]);

                Cache::put($cacheKey, $newHistory, $cacheTtl);
            }

            return $responseJson;
        } catch (\Throwable $e) {
            Log::error('GigaChat API Error: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Ошибка соединения с GigaChat: ' . $e->getMessage()
            ];
        }
    }
}
