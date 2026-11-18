<?php

namespace App\Support;

class InputCapitalization
{
    public static function normalize(string $field, string $value): string
    {
        $title = in_array($field, config('input_capitalization.title', []), true);
        if ($title) {
            $value = mb_strtolower($value, 'UTF-8');
        } elseif (! in_array($field, config('input_capitalization.words', []), true)) {
            return $value;
        }

        $preserve = $title ? [] : config('input_capitalization.preserve_words', []);

        return preg_replace_callback('/\p{L}[\p{L}\p{M}]*/u', function (array $match) use ($preserve): string {
            $word = $match[0];
            if (in_array($word, $preserve, true) || $word !== mb_strtolower($word, 'UTF-8')) {
                return $word;
            }

            return mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8')
                .mb_substr($word, 1, null, 'UTF-8');
        }, $value) ?? $value;
    }
}
