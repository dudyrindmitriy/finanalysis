<?php

namespace App\Services\Parsers;

interface BankParserInterface
{
    public function parse(string $filePath): array;
    public function extractTransactions(string|array $content): array;
    public function validateStatement(string|array $content): void;
}
