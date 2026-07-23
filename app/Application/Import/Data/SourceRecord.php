<?php

namespace App\Application\Import\Data;

final readonly class SourceRecord
{
    /**
     * @param  array<string, mixed>  $values
     */
    public function __construct(
        public int $rowNumber,
        public array $values,
    ) {}
}
