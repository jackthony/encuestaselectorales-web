<?php

namespace App\Application\Data;

final readonly class CandidateOptionData
{
    public function __construct(
        public string $optionId,
        public string $candidacyId,
        public string $candidateId,
        public string $candidateName,
        public ?string $candidatePhotoUrl,
        public string $partyId,
        public string $partyName,
        public ?string $partyAcronym,
        public ?string $partyLogoUrl,
        public int $displayOrder,
        public int $voteCount,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'option_id' => $this->optionId,
            'candidacy_id' => $this->candidacyId,
            'candidate' => [
                'id' => $this->candidateId,
                'name' => $this->candidateName,
                'photo_url' => $this->candidatePhotoUrl,
                'uses_default_photo' => $this->candidatePhotoUrl === null,
            ],
            'party' => [
                'id' => $this->partyId,
                'name' => $this->partyName,
                'acronym' => $this->partyAcronym,
                'logo_url' => $this->partyLogoUrl,
            ],
            'display_order' => $this->displayOrder,
            'vote_count' => $this->voteCount,
        ];
    }
}
