<?php

namespace Tests\Unit\Media;

use App\Infrastructure\Media\OgThumbnailCache;
use Tests\TestCase;

final class OgThumbnailCacheTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/og-thumbnail-cache-test-'.uniqid();
    }

    protected function tearDown(): void
    {
        if (is_dir($this->directory)) {
            foreach (glob($this->directory.'/*') ?: [] as $file) {
                unlink($file);
            }
            rmdir($this->directory);
        }
        parent::tearDown();
    }

    public function test_reuses_the_cached_file_when_last_vote_at_is_unchanged(): void
    {
        $cache = new OgThumbnailCache($this->directory);
        $calls = 0;
        $generator = function () use (&$calls): string {
            $calls++;

            return "png-content-{$calls}";
        };

        $first = $cache->remember('territory-1', '2026-07-23T19:00:00-05:00', $generator);
        $second = $cache->remember('territory-1', '2026-07-23T19:00:00-05:00', $generator);

        self::assertSame(1, $calls);
        self::assertSame($first, $second);
    }

    public function test_regenerates_when_last_vote_at_advances(): void
    {
        $cache = new OgThumbnailCache($this->directory);
        $calls = 0;
        $generator = function () use (&$calls): string {
            $calls++;

            return "png-content-{$calls}";
        };

        $first = $cache->remember('territory-1', '2026-07-23T19:00:00-05:00', $generator);
        $second = $cache->remember('territory-1', '2026-07-23T19:05:00-05:00', $generator);

        self::assertSame(2, $calls);
        self::assertNotSame($first, $second);
    }
}
