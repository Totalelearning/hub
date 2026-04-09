<?php

namespace App\Services\Chunking;

class TextChunker
{
    public function chunk(string $text, int $targetMin = 800, int $targetMax = 1200): array
    {
        $normalized = $this->normalize($text);

        if ($normalized === '') {
            return [];
        }

        $paragraphs = array_values(array_filter(
            array_map('trim', preg_split('/\n{2,}/', $normalized) ?: []),
            static fn (string $paragraph): bool => $paragraph !== ''
        ));

        $chunks = [];
        $buffer = '';

        foreach ($paragraphs as $paragraph) {
            if (mb_strlen($paragraph) > $targetMax) {
                if ($buffer !== '') {
                    $chunks[] = $buffer;
                    $buffer = '';
                }

                foreach ($this->splitLongParagraph($paragraph, $targetMax) as $piece) {
                    $candidate = $buffer === '' ? $piece : $buffer."\n\n".$piece;

                    if (mb_strlen($candidate) <= $targetMax) {
                        $buffer = $candidate;
                        continue;
                    }

                    if ($buffer !== '') {
                        $chunks[] = $buffer;
                    }

                    $buffer = $piece;
                }

                continue;
            }

            $candidate = $buffer === '' ? $paragraph : $buffer."\n\n".$paragraph;

            if (mb_strlen($candidate) <= $targetMax) {
                $buffer = $candidate;
                continue;
            }

            if ($buffer !== '') {
                $chunks[] = $buffer;
            }

            $buffer = $paragraph;
        }

        if ($buffer !== '') {
            $chunks[] = $buffer;
        }

        return array_values(array_filter(
            array_map(static fn (string $chunk): string => trim($chunk), $chunks),
            static fn (string $chunk): bool => $chunk !== ''
        ));
    }

    private function splitLongParagraph(string $paragraph, int $targetMax): array
    {
        $sentences = preg_split('/(?<=[.!?])\s+/u', $paragraph) ?: [];
        $sentences = array_values(array_filter(array_map('trim', $sentences), static fn (string $sentence): bool => $sentence !== ''));

        if ($sentences === []) {
            return $this->splitByLength($paragraph, $targetMax);
        }

        $parts = [];
        $buffer = '';

        foreach ($sentences as $sentence) {
            if (mb_strlen($sentence) > $targetMax) {
                if ($buffer !== '') {
                    $parts[] = $buffer;
                    $buffer = '';
                }

                foreach ($this->splitByLength($sentence, $targetMax) as $segment) {
                    $parts[] = $segment;
                }

                continue;
            }

            $candidate = $buffer === '' ? $sentence : $buffer.' '.$sentence;

            if (mb_strlen($candidate) <= $targetMax) {
                $buffer = $candidate;
                continue;
            }

            if ($buffer !== '') {
                $parts[] = $buffer;
            }

            $buffer = $sentence;
        }

        if ($buffer !== '') {
            $parts[] = $buffer;
        }

        return $parts;
    }

    private function splitByLength(string $text, int $targetMax): array
    {
        $text = trim($text);

        if ($text === '') {
            return [];
        }

        $parts = [];
        $cursor = 0;
        $length = mb_strlen($text);

        while ($cursor < $length) {
            $parts[] = trim(mb_substr($text, $cursor, $targetMax));
            $cursor += $targetMax;
        }

        return $parts;
    }

    private function normalize(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }
}

