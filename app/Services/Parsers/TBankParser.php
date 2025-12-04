<?php

namespace App\Services\Parsers;

use App\Services\Category\CategoryDetector;
use Carbon\Carbon;
use InvalidArgumentException;
use Smalot\PdfParser\Parser;

class TBankParser implements BankParserInterface
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
            if (
                preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $dataLine) &&
                isset($lines[$i + 1]) && preg_match('/^\d{2}:\d{2}$/', trim($lines[$i + 1])) &&
                isset($lines[$i + 2]) && preg_match('/^\d{2}\.\d{2}\.\d{4}$/', trim($lines[$i + 2])) &&
                isset($lines[$i + 3]) && preg_match('/^\d{2}:\d{2}$/', trim($lines[$i + 3])) &&
                isset($lines[$i + 4])
            ) {
                $amountsLine = trim($lines[$i + 4]);
                if (preg_match('/([+-]\s*[\d\s]+.\d{2})\s*₽\s+([+-]\s*[\d\s]+.\d{2})\s*₽\s+(.+)/u', $amountsLine, $matches)) {

                    $amount = trim($matches[1]);
                    $type = str_contains($amount, '+') ? 'income' : 'expense';
                    $normalizedAmount = str_replace(['+', ' ', '₽', '-'], '', $amount);
                    $normalizedAmount = str_replace([','], '.', $normalizedAmount);
                    $normalizedAmount = str_replace(["\xc2\xa0", " "], '', $normalizedAmount);

                    $normalizedAmount = (float)$normalizedAmount;
                    // $normalizedAmount = str_replace(['.'], ',', $normalizedAmount);

                    $transaction = [
                        'date' => Carbon::parse(trim($lines[$i + 2])),
                        'time' => trim($lines[$i + 1]),
                        'bank_category' => null,
                        'category' => '',
                        'bank_name' => 'tbank',
                        'amount' => $normalizedAmount,
                        'type' => $type,
                        'balance' => null,
                        'description' => trim($matches[3])
                    ];
                    $descriptionLines = [trim($matches[3])];
                    // Ищем описание в следующих строках (может быть несколько строк)
                    $cardNumber = '';
                    for ($j = $i + 5; $j < count($lines); $j++) {
                        $descLine = trim($lines[$j]);

                        if (preg_match('/^\d{4}$/', $descLine)) {
                            $cardNumber = $descLine;
                            break;
                        }
                        if (
                            empty($descLine) ||
                            preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $descLine)
                        ) {
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
                    $i += 4 + count($descriptionLines);
                }
            }
        }

        return $transactions;
    }

    public function validateStatement(string|array $content): void
    {
        $normalizedContent = mb_strtolower($content);
        if (!(str_contains($normalizedContent, 'акционерное общество «тбанк»'))) {
            throw new InvalidArgumentException('Загруженный файл не содержит маркеров "ТБанк".');
        }
    }
}
