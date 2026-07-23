<?php

namespace Tests\Unit\Domain;

use App\Domain\Shared\OpaqueId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class OpaqueIdTest extends TestCase
{
    public function test_it_generates_and_parses_ulids(): void
    {
        $id = OpaqueId::generate();

        $this->assertSame(26, strlen($id->value));
        $this->assertSame($id->value, OpaqueId::fromString($id->value)->value);
    }

    public function test_it_rejects_sequential_or_malformed_identifiers(): void
    {
        $this->expectException(InvalidArgumentException::class);

        OpaqueId::fromString('123');
    }
}
