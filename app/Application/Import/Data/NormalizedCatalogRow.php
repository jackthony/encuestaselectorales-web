<?php

namespace App\Application\Import\Data;

final readonly class NormalizedCatalogRow
{
    public function __construct(
        public int $sourceRowNumber,
        public string $sourceSystem,
        public string $electionCycle,
        public string $scopeType,
        public string $territorySourceKey,
        public string $territoryOfficialCode,
        public string $territoryName,
        public ?string $regionOfficialCode,
        public ?string $regionName,
        public ?string $provinceOfficialCode,
        public ?string $provinceName,
        public string $officeType,
        public string $partySourceKey,
        public string $partyName,
        public ?string $partyAcronym,
        public ?string $partyLogoUrl,
        public ?string $partySourceUrl,
        public string $candidateSourceKey,
        public string $candidateName,
        public ?string $candidatePhotoUrl,
        public ?string $candidateSourceUrl,
        public string $candidacySourceKey,
        public ?int $ballotOrder,
        public string $candidacyStatus,
        public ?string $sourceFile,
        public ?int $declaredSourceRow,
        public ?string $sourceUrl,
        public ?string $retrievedAt,
    ) {}

    public function batchKey(): string
    {
        return implode('|', [
            $this->sourceSystem,
            $this->territorySourceKey,
            $this->officeType,
            $this->electionCycle,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
