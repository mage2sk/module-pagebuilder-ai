<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Model;

use Panth\PageBuilderAi\Model\HtmlSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    private HtmlSanitizer $sanitizer;

    protected function setUp(): void
    {
        $this->sanitizer = new HtmlSanitizer();
    }

    public function testPlainTextIsReturnedUnchanged(): void
    {
        $this->assertSame('', $this->sanitizer->sanitize(''));
        $this->assertSame('Fish & Chips "today"', $this->sanitizer->sanitize('Fish & Chips "today"'));
    }

    public function testPageBuilderMarkupSurvives(): void
    {
        $html = '<div data-content-type="row" data-appearance="contained" data-element="main">'
            . '<div data-content-type="column" data-background-images="{}" data-element="main" '
            . 'style="justify-content: flex-start; display: flex; flex-direction: column;">'
            . '<h2 data-content-type="heading" data-element="main">Caf&eacute; offers</h2>'
            . '<div data-content-type="text" data-appearance="default" data-element="main">'
            . '<p>Visit <a href="/sale" class="pagebuilder-button-primary">'
            . '<span data-element="link_text">Shop now</span></a></p></div>'
            . '</div></div>';

        $out = $this->sanitizer->sanitize($html);

        $this->assertStringContainsString('data-background-images="{}"', $out);
        $this->assertStringContainsString('style="justify-content: flex-start; display: flex; flex-direction: column;"', $out);
        $this->assertStringContainsString('<a href="/sale" class="pagebuilder-button-primary">', $out);
        $this->assertStringContainsString('<span data-element="link_text">Shop now</span>', $out);
        $this->assertStringContainsString('Caf', $out);
        $this->assertStringStartsWith('<div data-content-type="row"', $out);
    }

    public function testMultipleTopLevelNodesAreKeptInOrder(): void
    {
        $this->assertSame(
            '<h2>One</h2><p>Two</p>tail',
            $this->sanitizer->sanitize('<h2>One</h2><p>Two</p>tail')
        );
    }

    #[DataProvider('dangerousProvider')]
    public function testDangerousMarkupIsRemoved(string $html, string $forbidden, string $kept): void
    {
        $out = $this->sanitizer->sanitize($html);

        $this->assertStringNotContainsStringIgnoringCase($forbidden, $out);
        $this->assertStringContainsString($kept, $out);
    }

    public static function dangerousProvider(): array
    {
        return [
            'script block' => ['<p>ok</p><script>alert(1)</script>', '<script', '<p>ok</p>'],
            'unclosed script' => ['<p>ok</p><script>alert(1)', 'alert', '<p>ok</p>'],
            'unquoted handler' => ['<img src=x onerror=alert(1)>', 'onerror', '<img src="x">'],
            'quoted handler' => ['<p onclick="alert(1)">t</p>', 'onclick', '<p>t</p>'],
            'mixed case handler' => ['<p OnMouseOver="alert(1)">t</p>', 'onmouseover', '<p>t</p>'],
            'svg onload' => ['<svg/onload=alert(1)></svg><p>t</p>', 'svg', '<p>t</p>'],
            'javascript href' => ['<a href="javascript:alert(1)">x</a>', 'javascript', '<a>x</a>'],
            'obfuscated href' => ['<a href=" j&#x61;vascript:alert(1)">x</a>', 'alert', '<a>x</a>'],
            'tab in scheme' => ["<a href=\"java\tscript:alert(1)\">x</a>", 'alert', '<a>x</a>'],
            'vbscript href' => ['<a href="vbscript:msgbox">x</a>', 'vbscript', '<a>x</a>'],
            'data html href' => ['<a href="data:text/html;base64,PHNjcmlwdD4=">x</a>', 'data:', '<a>x</a>'],
            'iframe' => ['<iframe src="https://example.com"></iframe><p>t</p>', 'iframe', '<p>t</p>'],
            'iframe srcdoc' => ['<div srcdoc="<script>x</script>">t</div>', 'srcdoc', '<div>t</div>'],
            'form' => ['<form action="/x"><input name="a"></form><p>t</p>', '<form', '<p>t</p>'],
            'style tag' => ['<style>body{}</style><p>t</p>', '<style', '<p>t</p>'],
            'meta refresh' => ['<meta http-equiv="refresh" content="0"><p>t</p>', '<meta', '<p>t</p>'],
            'css expression' => ['<p style="width: expression(alert(1))">t</p>', 'expression', '<p>t</p>'],
            'comment' => ['<!--[if IE]><script>x</script><![endif]--><p>t</p>', '<!--', '<p>t</p>'],
            'object' => ['<object data="x.swf"></object><p>t</p>', '<object', '<p>t</p>'],
        ];
    }

    public function testSafeDataImageSourceIsKept(): void
    {
        $out = $this->sanitizer->sanitize('<img src="data:image/png;base64,iVBORw0KGgo=" alt="dot">');

        $this->assertStringContainsString('src="data:image/png;base64,iVBORw0KGgo="', $out);
        $this->assertStringContainsString('alt="dot"', $out);
    }

    public function testDataImageIsNotAllowedAsLink(): void
    {
        $this->assertSame('<a>x</a>', $this->sanitizer->sanitize('<a href="data:image/png;base64,AAAA">x</a>'));
    }

    public function testHttpsAndRelativeLinksAreKept(): void
    {
        $this->assertSame(
            '<a href="https://example.com/a?b=1">x</a><a href="/about">y</a>',
            $this->sanitizer->sanitize('<a href="https://example.com/a?b=1">x</a><a href="/about">y</a>')
        );
    }
}
