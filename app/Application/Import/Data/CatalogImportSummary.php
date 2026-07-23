<?php

namespace App\Application\Import\Data;

final readonly class CatalogImportSummary
{
    /**
     * @param  list<array<string, mixed>>  $runs
     */
    public function __construct(
        public string $checksum,
        public bool $dryRun,
        public int $totalRows,
        public int $createdRows,
        public int $updatedRows,
        public int $unchangedRows,
        public int $rejectedRows,
        public array $runs,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'checksum' => $this->checksum,
            'dry_run' => $this->dryRun,
            'total_rows' => $this->totalRows,
            'created_rows' => $this->createdRows,
            'updated_rows' => $this->updatedRows,
            'unchanged_rows' => $this->unchangedRows,
            'rejected_rows' => $this->rejectedRows,
            'runs' => $this->runs,
        ];
    }
}
