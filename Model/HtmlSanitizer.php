<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model;

class HtmlSanitizer
{
    private const ROOT_ID = 'panth-pbai-sanitize-root';

    private const BLOCKED_TAGS = [
        'script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet',
        'form', 'meta', 'link', 'base', 'svg', 'math', 'noscript', 'template',
    ];

    private const URL_ATTRIBUTES = [
        'href', 'src', 'action', 'formaction', 'xlink:href', 'poster', 'background', 'data',
    ];

    public function sanitize(string $html): string
    {
        if ($html === '' || !str_contains($html, '<')) {
            return $html;
        }

        $previous = libxml_use_internal_errors(true);
        $document = new \DOMDocument('1.0', 'UTF-8');
        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"?><html><body><div id="' . self::ROOT_ID . '">' . $html . '</div></body></html>',
            LIBXML_NONET
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $loaded ? $this->findRoot($document) : null;
        if ($root === null) {
            return htmlspecialchars(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $this->cleanChildren($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= (string) $document->saveHTML($child);
        }
        return $output;
    }

    private function findRoot(\DOMDocument $document): ?\DOMElement
    {
        $xpath = new \DOMXPath($document);
        $nodes = $xpath->query('//div[@id="' . self::ROOT_ID . '"]');
        $node = $nodes !== false ? $nodes->item(0) : null;
        return $node instanceof \DOMElement ? $node : null;
    }

    private function cleanChildren(\DOMNode $parent): void
    {
        for ($i = $parent->childNodes->length - 1; $i >= 0; $i--) {
            $child = $parent->childNodes->item($i);
            if ($child === null) {
                continue;
            }
            if ($child instanceof \DOMComment || $child instanceof \DOMProcessingInstruction) {
                $parent->removeChild($child);
                continue;
            }
            if (!$child instanceof \DOMElement) {
                continue;
            }
            if (in_array(strtolower($child->nodeName), self::BLOCKED_TAGS, true)) {
                $parent->removeChild($child);
                continue;
            }
            $this->cleanAttributes($child);
            $this->cleanChildren($child);
        }
    }

    private function cleanAttributes(\DOMElement $element): void
    {
        $remove = [];
        foreach ($element->attributes as $attribute) {
            $name = strtolower($attribute->nodeName);
            $value = (string) $attribute->nodeValue;
            if (str_starts_with($name, 'on')
                || $name === 'srcdoc'
                || (in_array($name, self::URL_ATTRIBUTES, true) && $this->isUnsafeUrl($name, $value))
                || ($name === 'style' && $this->isUnsafeStyle($value))
            ) {
                $remove[] = $attribute->nodeName;
            }
        }
        foreach ($remove as $name) {
            $element->removeAttribute($name);
        }
    }

    private function isUnsafeUrl(string $name, string $value): bool
    {
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $compact = strtolower((string) preg_replace('/[\x00-\x20]+/', '', $decoded));
        if (preg_match('/^(javascript|vbscript|file|livescript):/', $compact)) {
            return true;
        }
        if (str_starts_with($compact, 'data:')) {
            return !($name === 'src' && preg_match('#^data:image/(png|jpe?g|gif|webp);#', $compact));
        }
        return false;
    }

    private function isUnsafeStyle(string $value): bool
    {
        return (bool) preg_match('/expression\s*\(|javascript:|vbscript:|behavior\s*:|-moz-binding/i', $value);
    }
}
