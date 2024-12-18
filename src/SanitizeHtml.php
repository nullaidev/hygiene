<?php

namespace Nullai\Hygiene;

class SanitizeHtml
{
    public const string ENCODING = 'UTF-8';

    /**
     * Filters the given HTML content, allowing only specified tags and attributes.
     *
     * Escapes attribute values and removes unsupported tags and attributes based on the provided allowed tags and attributes configuration.
     *
     * @param string|\Stringable $html The input HTML content as a string.
     * @param array|string|\Stringable $tags A list of tags (with attributes) to allow, either as an array or a comma-separated string (a:href|style,br).
     * @param string|null $selector Optional CSS selector to specify which elements should be filtered.
     * @param bool $allow Whitelist or blacklist tags.
     * @param int $flags Optional flags for HTML parsing and error handling, defaults to LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED.
     *
     * @return string The sanitized HTML content.
     */
    public static function filterTags(string|\Stringable $html, array|string|\Stringable $tags = [], ?string $selector = null, bool $allow = true, int $flags = LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED) : string
    {
        if(is_string($tags) || $tags instanceof \Stringable) {
            $tagsExploded = explode(',', (string) $tags);
            $tags = [];

            foreach ($tagsExploded as $tag) {
                [$tag, $attributes] = explode(':', $tag, 2);
                if ($attributes) {
                    $attributes = explode('|', $attributes);

                }
                $tags[$tag] = $attributes ?? [];
            }
        }

        $dom = \Dom\HTMLDocument::createFromString((string) $html, $flags);
        $allowedTagsList = array_keys($tags);

        foreach ($dom->querySelectorAll($selector ?? '*') as $node) {
            if (in_array($node->localName, $allowedTagsList) !== $allow) {
                $node->parentNode->removeChild($node);
                continue;
            }

            foreach (iterator_to_array($node->attributes) as $attribute) {
                if (!$attribute->localName || in_array($attribute->localName, $tags[$node->localName] ?? []) !== $allow) {
                    $node->removeAttribute($attribute->localName);
                }
            }
        }

        return $dom->saveHTML();
    }

    /**
     * Escapes special characters in a string for use in HTML.
     *
     * Converts special characters to HTML entities to prevent rendering issues
     * and potential security vulnerabilities when displaying untrusted content.
     *
     * @param string $html The input string to be escaped.
     * @param int $flags Optional flags for the htmlspecialchars function, defaults to ENT_NOQUOTES.
     *
     * @return string The escaped HTML string.
     */
    public static function escHtml($html, $flags = ENT_NOQUOTES) : string
    {
        return htmlspecialchars($html, $flags, static::ENCODING);
    }

    /**
     * Escapes special characters in a string for use in HTML attributes.
     *
     * Converts special characters to HTML entities to prevent security issues such as XSS attacks.
     *
     * @param string $html The input string containing raw HTML content to be escaped.
     * @param int $flags Optional flags for the htmlspecialchars function, defaults to ENT_QUOTES.
     *
     * @return string The escaped string safe for use in HTML attributes.
     */
    public static function escAttr($html, $flags = ENT_QUOTES) : string
    {
        return htmlspecialchars($html, $flags, static::ENCODING);
    }

    /**
     * Escape data for JSON encoding.
     *
     * @param mixed $data The data to be encoded.
     * @param int $flags Optional. Bitmask consisting of JSON encode options.
     * @return string The JSON encoded string.
     */
    public static function escJson(mixed $data, int $flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) : string
    {
        return json_encode($data, $flags);
    }
}