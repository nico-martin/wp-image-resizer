<?php

namespace SayHello\ImageResizer;

use Imagick;

class Helpers
{
    public static function getImagickFormats(): array
    {
        return array_map(
            function ($e) {
                return strtolower($e);
            },
            Imagick::queryFormats()
        );
    }

    public static function getSupportedExtensions(): array
    {
        return array_filter([
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'avif' => 'image/avif',
        ], function ($key) {
            return in_array($key, self::getImagickFormats());
        }, ARRAY_FILTER_USE_KEY);
    }
}