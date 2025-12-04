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
            return redirect(route('login'));
        }
        $request->validate([
            'parserType' => 'required|in:sber,tbank,alfa',
        ]);
        switch ($request['parserType']) {
            case 'sber':
                $request->validate([
                    'statement' => 'required|file|mimes:pdf',
                ]);
                $parser = new SberParser();
                break;
            case 'tbank':
                $request->validate([
                    'statement' => 'required|file|mimes:pdf',
                ]);
                $parser = new TBankParser();
                break;
            case 'alfa':
                $request->validate([
                    'statement' => 'required|file|mimes:xlsx,xls',
                ]);
                $parser = new AlfaParser();
                break;
        }
        $filePath = $request->file('statement')?->path();

        if (!$filePath) {
            return back()->withErrors('Файл не найден');
        }
        try {
            $result = $parser->parse($filePath);
            if (!isset($result['transactions'])) {
                return back()->withErrors('Ошибка парсинга файла');
            }
            $savingInfo = $this->saveTransactions($result['transactions']);
        } catch (\Throwable $e) {
            Log::error("Failed to parse or save transactions.", [
                'user_id' => Auth::id(),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors('Произошла ошибка при обработке файла. Пожалуйста, проверьте формат файла или свяжитесь со службой поддержки.');
        }
        return $savingInfo;
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
