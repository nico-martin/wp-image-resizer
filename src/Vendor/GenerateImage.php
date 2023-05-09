<?php

namespace SayHello\ImageResizer;

use Imagick;

class GenerateImage
{
    private ?Imagick $imagick = null;

    function __construct(string $srcPath)
    {
        $this->imagick = new Imagick($srcPath);
    }

    public function setQuality(int $quality)
    {
        if ($quality !== 0) {
            $this->imagick->setImageCompressionQuality($quality);
        }
    }

    public function setBlur(int $blur): void
    {
        if ($blur !== 0) {
            $this->imagick->blurImage($blur, 10);
        }
    }

    public function setFormat(string $format): void
    {
        if (in_array($format, Helpers::getImagickFormats())) {
            $this->imagick->setImageFormat($format);
        }
    }

    public function setSizes(int $width, int $height): void
    {
        $imagickFilter = Imagick::FILTER_POINT;
        $imagickBlur = 1;
        $imageOrgAspect = $this->imagick->getImageWidth() / $this->imagick->getImageHeight();

        if ($width && $height) {
            $this->imagick->cropThumbnailImage($width, $height);
        } elseif ($width) {
            $this->imagick->resizeImage(
                $width,
                intval($width / $imageOrgAspect),
                $imagickFilter,
                $imagickBlur
            );
        } elseif ($height) {
            $this->imagick->resizeImage(
                intval($height * $imageOrgAspect),
                $height,
                $imagickFilter,
                $imagickBlur
            );
        }
    }

    public function save($dest): void
    {
        $parts = explode('/', $dest);
        array_pop($parts);
        $folder = implode('/', $parts) . '/';
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }
        $this->imagick->writeImage($dest);
    }

    public function echoImage()
    {
        $mime = $this->imagick->getImageMimeType();
        $mime = str_replace('x-', '', $mime);
        $blob = $this->imagick->getImageBlob();
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . strlen($blob));
        echo $blob;
        exit;
    }
}