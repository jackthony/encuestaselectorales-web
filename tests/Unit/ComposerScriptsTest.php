<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ComposerScriptsTest extends TestCase
{
    public function test_setup_and_dev_scripts_do_not_require_node_or_vite(): void
    {
        $composer = json_decode(
            file_get_contents(dirname(__DIR__, 2).'/composer.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $scripts = implode("\n", [
            ...$composer['scripts']['setup'],
            ...$composer['scripts']['dev'],
        ]);

        $this->assertStringNotContainsStringIgnoringCase('npm', $scripts);
        $this->assertStringNotContainsStringIgnoringCase('npx', $scripts);
        $this->assertStringNotContainsStringIgnoringCase('node', $scripts);
        $this->assertStringNotContainsStringIgnoringCase('vite', $scripts);
        $this->assertStringContainsString('@php artisan serve', $scripts);
    }
}
