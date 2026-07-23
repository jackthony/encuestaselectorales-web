<?php

namespace App\Application\Import\Data;

final readonly class CatalogImportOptions
{
    public function __construct(
        public ?string $sourceCycle = null,
        public string $sourceSystem = 'JNE',
        public ?string $operatorIdentifier = null,
        public bool $publish = false,
        public bool $dryRun = false,
    ) {}
}
