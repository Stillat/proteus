<?php

namespace Stillat\Proteus\Document;

/**
 * Class Transformer.
 *
 * Transforms a temporary document structure into its final, consumable form.
 *
 * @since 1.0.0
 */
class Transformer
{
    const PROTEUS_NL = '/*PROTEUS:NL*/';

    /**
     * A mapping of internal type adjustments and their final values.
     *
     * @var string[]
     */
    protected $simpleReplacements = [
        "(bool) ''" => 'false',
        "(bool) '1'" => 'true',
        "(double) '/*W:D:ST*/" => '',
        "(int) '/*W:INT:ST*/" => '',
        "/*W:D:SQ*/'" => '',
        "/*W:INT:SQ*/'" => '',
        Transformer::PROTEUS_NL => "\n",
    ];

    /**
     * Prepares a final document for consumption.
     *
     * @param  string  $content The content.
     * @return string
     */
    public function transform($content)
    {
        foreach ($this->simpleReplacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        return self::normalizeLineEndings($content);
    }

    /**
     * Normalizes all line endings in the provided string.
     *
     * @param  string  $string The value to normalize.
     * @return string
     */
    public static function normalizeLineEndings($string)
    {
        $string = str_replace(["\r\n", "\r", "\n"], "\n", $string);
        $string = preg_replace("/\n{3,}/", "\n\n", $string);

        return $string;
    }
}
