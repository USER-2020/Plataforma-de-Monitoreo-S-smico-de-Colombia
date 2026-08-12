<?php

namespace Tests\Feature;

use Tests\TestCase;

class AgentAccessibilityTest extends TestCase
{
    public function test_llms_file_has_a_title_summary_and_markdown_links(): void
    {
        $contents = file_get_contents(public_path('llms.txt'));

        $this->assertStringStartsWith("# terracosismos\n", $contents);
        $this->assertMatchesRegularExpression('/^> .+/m', $contents);
        $this->assertMatchesRegularExpression('/^- \[[^]]+\]\(https:\/\/[^)]+\)$/m', $contents);
        $this->assertStringContainsString('[Contexto ampliado](https://terracosismos.online/llms-full.txt)', $contents);
        $this->assertStringContainsString('[Privacidad y cookies](https://terracosismos.online/privacidad)', $contents);
        $this->assertSame(1, preg_match('/^[\x00-\x7F]*$/s', $contents));
    }

    public function test_llms_resources_are_publicly_available(): void
    {
        $this->assertFileExists(public_path('llms.txt'));
        $this->assertFileExists(public_path('llms-full.txt'));
    }
}
