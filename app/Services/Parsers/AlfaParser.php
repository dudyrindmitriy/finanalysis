<?php

namespace App\Services\Parsers;

use App\Services\Category\CategoryDetector;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;

class AlfaParser implements BankParserInterface
{
    public function parse(string $filePath, $originalName = null): array
    {
        $mimeType = mime_content_type($filePath);
        $allowedMimeTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
            'application/vnd.ms-excel' // xls
        ];

        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new InvalidArgumentException('Для Альфа-Банка требуется Excel файл (.xlsx или .xls).');
        }
        Log::debug($originalName);
        if ($originalName) {
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        }
        $readerType = match ($extension) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'xls' => \Maatwebsite\Excel\Excel::XLS,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'ods' => \Maatwebsite\Excel\Excel::ODS,
            default => \Maatwebsite\Excel\Excel::XLSX
        };
        $data = Excel::toArray([], $filePath, null, $readerType);
        $this->validateStatement($data[0]);
        $transactions = $this->extractTransactions($data[0]);
        return [
            'raw_text' => $data[0],
            'transactions' => $transactions
        ];
    }

    public function extractTransactions(string|array $content): array
    {
        $transactions = [];
        $startParsing = false;

        foreach ($content as $row) {
            $isEmpty = true;
            foreach ($row as $cell) {
                if ($cell !== null && $cell !== '') {
                    $isEmpty = false;
                    break;
                }
            }
            if ($isEmpty) {
                continue;
            }

            if (!$startParsing) {
                $headerKeywords = ['Дата операции', 'Категория', 'Описание', 'Сумма'];
                $foundCount = 0;

                foreach ($row as $cell) {
                    if ($cell && is_string($cell)) {
                        foreach ($headerKeywords as $keyword) {
                            if (str_contains($cell, $keyword)) {
                                $foundCount++;
                                break;
                            }
                        }
                    }
                }

                if ($foundCount >= 4) {
                    $startParsing = true;
                }
                continue;
            }

            if (isset($row[0]) && preg_match('/\d{2}\.\d{2}\.\d{4}/', $row[0]) && isset($row[12])) {
                $amount = trim($row[12]);
                $type = str_starts_with(trim($amount), '-') ? 'expense' : 'income';

                $normalizedAmount = str_replace([' ', ' ', 'RUR', '₽'], '', $amount);
                $normalizedAmount = ltrim($normalizedAmount, '-+');
                $normalizedAmount = str_replace([','], '.', $normalizedAmount);
                $normalizedAmount = str_replace(["\xc2\xa0", " "], '', $normalizedAmount);

                $normalizedAmount = (float)$normalizedAmount;
                $categoryDetector = new CategoryDetector;
                $transaction = [
                    'date' => Carbon::parse(trim($row[0])),
                    'time' => null,
                    'category' =>    $transaction['category'] = $categoryDetector->detectCategory(isset($row[11]) ? trim($row[11]) : ''),
                    'bank_category' => isset($row[4]) ? trim($row[4]) : '',
                    'amount' => $normalizedAmount,
                    'type' => $type,
                    'balance' => null,
                    'description' => isset($row[11]) ? trim($row[11]) : '',
                    'bank_name' => 'alfa',
                    'status' => isset($row[14]) ? trim($row[14]) : '',
                    'code' => isset($row[3]) ? trim($row[3]) : ''
                ];

                $transactions[] = $transaction;
            }
        }

        return $transactions;
    }

    public function validateStatement(string|array $content): void
    {
        $marker = 'ао «альфа-банк»';

        $searchFromEnd = function ($array) use ($marker, &$searchFromEnd): bool {
            for ($i = count($array) - 1; $i >= 0; $i--) {
                $value = $array[$i];

                if (is_string($value)) {
                    if (str_contains(mb_strtolower($value), $marker)) {
                        return true;
                    }
                } elseif (is_array($value)) {
                    if ($searchFromEnd($value)) {
                        return true;
                    }
                }
            }
            return false;
        };

        if (!$searchFromEnd($content)) {
            throw new InvalidArgumentException('Загруженный файл не содержит маркеров "АО «Альфа-Банк»".');
        }
    }
}
