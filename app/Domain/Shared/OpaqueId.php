<?php

namespace App\Domain\Shared;

use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class OpaqueId
{
    private function __construct(public string $value) {}

    public static function generate(): self
    {
        return new self((string) Str::ulid());
    }

    public static function fromString(string $value): self
    {
        $value = trim($value);

        if (! Str::isUlid($value)) {
            throw new InvalidArgumentException('Invalid opaque identifier.');
        }

        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
