<?php

namespace Tests\Unit\Services;

use App\Services\Chunking\TextChunker;
use PHPUnit\Framework\TestCase;

class TextChunkerTest extends TestCase
{
    public function test_it_preserves_paragraphs_when_chunking(): void
    {
        $chunker = new TextChunker();

        $paragraphA = str_repeat('A', 40);
        $paragraphB = str_repeat('B', 45);
        $paragraphC = str_repeat('C', 50);

        $chunks = $chunker->chunk(
            $paragraphA."\n\n".$paragraphB."\n\n".$paragraphC,
            60,
            100
        );

        $this->assertCount(2, $chunks);
        $this->assertSame($paragraphA."\n\n".$paragraphB, $chunks[0]);
        $this->assertSame($paragraphC, $chunks[1]);
    }

    public function test_it_splits_long_paragraphs_deterministically(): void
    {
        $chunker = new TextChunker();

        $text = implode(' ', [
            str_repeat('D', 55).'.',
            str_repeat('E', 55).'.',
            str_repeat('F', 55).'.',
        ]);

        $chunks = $chunker->chunk($text, 60, 120);

        $this->assertCount(2, $chunks);
        $this->assertSame(str_repeat('D', 55).'. '.str_repeat('E', 55).'.', $chunks[0]);
        $this->assertSame(str_repeat('F', 55).'.', $chunks[1]);
    }
}

