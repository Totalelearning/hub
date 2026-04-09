<?php

namespace App\Contracts;

interface PdfTextExtractor
{
    public function extractText(string $binaryPdf): string;
}

