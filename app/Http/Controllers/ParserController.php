<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Services\Parsers\SberParser;
use App\Services\Parsers\TBankParser;
use App\Services\Parsers\AlfaParser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ParserController extends Controller
{
    public function showForm()
    {
        return view('test-parser');
    }

    public function parse(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется авторизация'
            ], 401);
        }
        try {
            $request->validate(['parserType' => 'required|in:sber,tbank,alfa']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Выберите банк',
                'errors' => $e->errors()
            ], 400);
        }
        switch ($request['parserType']) {
            case 'sber':
                try {
                    $request->validate([
                        'statement' => 'required|file|mimes:pdf',
                    ]);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Для Сбера требуется PDF файл',
                        'errors' => $e->errors()
                    ], 400);
                }
                $parser = new SberParser();
                break;
            case 'tbank':
                try {
                    $request->validate([
                        'statement' => 'required|file|mimes:pdf',
                    ]);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Для ТБанка требуется PDF файл',
                        'errors' => $e->errors()
                    ], 400);
                }
                $parser = new TBankParser();
                break;
            case 'alfa':
                try {
                    $request->validate([
                        'statement' => 'required|file|mimes:xlsx,xls',
                    ]);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Для Альфа-Банка требуется Excel файл',
                        'errors' => $e->errors()
                    ], 400);
                }
                $parser = new AlfaParser();
                break;
        }
        $file = $request->file('statement');
        $filePath = $file->path();
        $originalName = $file->getClientOriginalName();
        if (!$filePath) {
            return response()->json([
                'success' => false,
                'message' => 'Файл не найден'
            ], 400);
        }
        try {
            $result = $parser->parse($filePath, $originalName);
            if (!isset($result['transactions'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка парсинга файла'
                ], 400);
            }
            $savingInfo = $this->saveTransactions($result['transactions']);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Throwable $e) {
            Log::error("Failed to parse or save transactions.", [
                'user_id' => Auth::id(),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при обработке файла',
            ], 500);
        }
        return response()->json([
            'success' => true,
            'message' => 'Выписка успешно обработана',
            'data' => $savingInfo,
            'stats' => [
                'saved' => $savingInfo['saved'],
                'duplicated' => $savingInfo['duplicated']
            ]
        ]);
    }

    private function saveTransactions($transactions)
    {
        $savedCount = 0;
        $duplicatedCount = 0;
        $userId = Auth::id();
        $processedHashes = [];

        DB::beginTransaction();

        try {
            foreach ($transactions as $transaction) {
                $hashData = [
                    $transaction['date'],
                    $transaction['time'],
                    $transaction['amount'],
                    $transaction['type'],
                    $transaction['description'],
                    $transaction['bank_name'],
                    $userId,
                ];
                $transactionHash = hash('sha256', implode('|', $hashData));

                if (Transaction::where('transaction_hash', $transactionHash)->exists()) {
                    $duplicatedCount++;
                    continue;
                }

                if (in_array($transactionHash, $processedHashes)) {
                    $duplicatedCount++;
                    continue;
                }

                $processedHashes[] = $transactionHash;

                $category = Category::firstOrCreate(['name' => $transaction['category']]);

                Transaction::create([
                    'amount' => $transaction['amount'],
                    'type' => $transaction['type'],
                    'date' => $transaction['date'],
                    'time' => $transaction['time'] ?? null,
                    'description' => $transaction['description'] ?? null,
                    'bank_name' => $transaction['bank_name'],
                    'bank_category' => $transaction['bank_category'] ?? null,
                    'mcc_code' => $transaction['mcc_code'] ?? '',
                    'category_id' => $category->id,
                    'user_id' => $userId,
                    'transaction_hash' => $transactionHash,
                ]);
                $savedCount++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return ['saved' => $savedCount, 'duplicated' => $duplicatedCount];
    }

    public function storeManual(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется авторизация'
            ], 401);
        }
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'type' => 'required|in:income,expense',
                'date' => 'required|date',
                'description' => 'nullable|string|max:500',
                'category_id' => 'nullable|exists:categories,id',
                'bank_name' => 'nullable|string|max:100',
                'time' => 'nullable|date_format:H:i',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 400);
        }

        try {
            $transactionData = [
                'amount' => $validated['amount'],
                'type' => $validated['type'],
                'date' => $validated['date'],
                'time' => $validated['time'] ?? null,
                'description' => $validated['description'],
                'bank_name' => $validated['bank_name'] ?? 'Ручной ввод',
                'bank_category' => null,
                'mcc_code' => null,
                'category' => $validated['category_id'] ?
                    Category::find($validated['category_id'])->name : 'Прочее',
            ];

            $savingInfo = $this->saveTransactions([$transactionData]);
        } catch (\Throwable $e) {
            Log::error("Failed save transactions.", [
                'user_id' => Auth::id(),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при сохранении транзакции',
            ], 500);
        }
        return response()->json([
            'success' => true,
            'message' => 'Транзакция успешно сохранена',
            'data' => $savingInfo,
            'stats' => [
                'saved' => $savingInfo['saved'],
                'duplicated' => $savingInfo['duplicated']
            ]
        ]);
    }
}
