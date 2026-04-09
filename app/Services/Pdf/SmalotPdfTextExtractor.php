<?php

namespace App\Services\Pdf;

use App\Contracts\PdfTextExtractor;
use RuntimeException;

class SmalotPdfTextExtractor implements PdfTextExtractor
{
    public function extractText(string $binaryPdf): string
    {
        if (! class_exists(\Smalot\PdfParser\Parser::class)) {
            throw new RuntimeException('smalot/pdfparser is not installed. Run: composer require smalot/pdfparser');
        }

        $parser = new \Smalot\PdfParser\Parser();
        $document = $parser->parseContent($binaryPdf);

        return trim($document->getText());
    }
}

