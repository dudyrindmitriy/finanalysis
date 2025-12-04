<?php

namespace App\Services\Parsers;

use App\Services\Category\CategoryDetector;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Smalot\PdfParser\Parser;

class SberParser implements BankParserInterface
{
    public function parse(string $filePath): array
    {
        $pdf = new Parser();
        $pdfDocument = $pdf->parseFile($filePath);
        $text = $pdfDocument->getText();
        $this->validateStatement($text);
        $transactions = $this->extractTransactions($text);
        // Пока просто возвращаем сырой текст для анализа
        return [
            'raw_text' => $text,
            'transactions' => $transactions // позже будем парсить
        ];
    }

    public function extractTransactions(string|array $text): array
    {
        $transactions = [];
        $lines = explode("\n", $text);

        for ($i = 0; $i < count($lines); $i++) {
            $dataLine = $lines[$i];



            // Ищем данные транзакции
            if (preg_match('/(\d{2}\.\d{2}\.\d{4})(?:\s+(\d{2}:\d{2}))?\s+\d+\s*([^\d+-]+?)\s*([+-]?\s*[\d\s]+(?:,\d{2})?)\s+([\d\s]+(?:,\d{2})?)/u', $dataLine, $matches)) {

                $amount = trim($matches[4]);
                $type = str_contains($amount, '+') ? 'income' : 'expense';

                $normalizedAmount = str_replace(['+', ' '], '', $amount);
                $normalizedAmount = str_replace([','], '.', $normalizedAmount);
                $normalizedAmount = str_replace(["\xc2\xa0", " "], '', $normalizedAmount);
                $normalizedAmount = (float)$normalizedAmount;

                $transaction = [
                    'date' => Carbon::parse($matches[1]),
                    'time' => $matches[2],
                    'bank_category' => trim($matches[3]),
                    'bank_name' => 'sber',
                    'category' => '',
                    'amount' => $normalizedAmount,
                    'type' => $type,
                    'balance' => trim($matches[5]),
                    'description' => ''
                ];

                // Ищем описание в следующих строках (может быть несколько строк)
                $descriptionLines = [];
                for ($j = $i + 1; $j < count($lines); $j++) {
                    $descLine = trim($lines[$j]);

                    // Если наткнулись на новую транзакцию или пустую строку - заканчиваем
                    if (empty($descLine) || preg_match('/\d{2}\.\d{2}\.\d{4}\s+\d{2}:\d{2}/', $descLine) ||  str_contains($descLine, 'Дата формирования')) {
                        break;
                    }

                    $descriptionLines[] = $descLine;
                }

                // Объединяем все строки описания
                $categoryDetector = new CategoryDetector;
                $transaction['description'] = implode(' ', $descriptionLines);
                $transaction['category'] = $categoryDetector->detectCategory(implode(' ', $descriptionLines));
                $transactions[] = $transaction;

                // Пропускаем строки с описанием
                $i += count($descriptionLines);
            }
        }

        return $transactions;
    }

    public function validateStatement(string|array $content): void
    {
        $normalizedContent = mb_strtolower($content);

        if (!(str_contains($normalizedContent, 'заказано в сбербанк онлайн'))) {
            throw new InvalidArgumentException('Загруженный файл не содержит маркеров "СберБанк".');
        }
    }
}
