<?php

namespace SayHello\ImageResizer;

class Helpers
{
    public static function getSupportedExtensions(): array
    {
        return [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            //'webp' => 'image/webp',
        ];
    }
}