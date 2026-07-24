<?php

namespace App\Infrastructure\Media;

final class OgThumbnailCache
{
    public function __construct(private readonly string $directory = '') {}

    /**
     * @param  callable(): string  $generator  Produces the PNG binary on a cache miss.
     */
    public function remember(string $territoryId, ?string $lastVoteAtIso, callable $generator): string
    {
        $path = $this->path($territoryId, $lastVoteAtIso);

        $cached = is_file($path) ? file_get_contents($path) : false;
        if ($cached !== false) {
            return $cached;
        }

        $png = $generator();

        if (! is_dir($this->directoryPath())) {
            mkdir($this->directoryPath(), 0755, true);
        }
        file_put_contents($path, $png);

        return $png;
    }

    private function path(string $territoryId, ?string $lastVoteAtIso): string
    {
        $key = substr(sha1($lastVoteAtIso ?? 'no-votes'), 0, 16);

        return $this->directoryPath()."/{$territoryId}-{$key}.png";
    }

    private function directoryPath(): string
    {
        return $this->directory !== '' ? $this->directory : storage_path('app/og-thumbnails');
    }
}
