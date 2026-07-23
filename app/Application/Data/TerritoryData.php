<?php

namespace App\Application\Data;

final readonly class TerritoryData
{
    /**
     * @param  array<int, array{id:string,name:string,scope_type:string}>  $ancestors
     */
    public function __construct(
        public string $id,
        public string $officialCode,
        public string $name,
        public string $slug,
        public string $scopeType,
        public array $ancestors = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'official_code' => $this->officialCode,
            'name' => $this->name,
            'slug' => $this->slug,
            'scope_type' => $this->scopeType,
            'ancestors' => $this->ancestors,
        ];
    }
}
