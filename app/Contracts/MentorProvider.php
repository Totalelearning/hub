<?php

namespace App\Contracts;

interface MentorProvider
{
    public function answer(string $question, array $contextUnits, array $options = []): array;
}

