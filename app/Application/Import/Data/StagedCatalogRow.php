<?php

namespace App\Application\Import\Data;

final readonly class StagedCatalogRow
{
    /**
     * @param  list<string>  $diagnostics
     */
    public function __construct(
        public SourceRecord $source,
        public ?NormalizedCatalogRow $normalized,
        public array $diagnostics = [],
        public ?string $batchKey = null,
    ) {}

    public function isValid(): bool
    {
        return $this->normalized !== null && $this->diagnostics === [];
    }
}
