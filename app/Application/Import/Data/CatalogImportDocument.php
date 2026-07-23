<?php

namespace App\Application\Import\Data;

final readonly class CatalogImportDocument
{
    /**
     * @param  list<SourceRecord>  $records
     */
    public function __construct(
        public string $path,
        public string $checksum,
        public int $sizeBytes,
        public string $schemaVersion,
        public string $mappingVersion,
        public array $records,
    ) {}
}
