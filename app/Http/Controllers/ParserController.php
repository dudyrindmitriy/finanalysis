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
                    'description' => $transaction['description'],
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
}
