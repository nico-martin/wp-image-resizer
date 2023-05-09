<?php

namespace SayHello\ImageResizer;

use WP_Error;

class Image
{
    public string $routeImage = 'image';

    public function run()
    {
        add_action('rest_api_init', [$this, 'registerRoute']);
    }

    public function registerRoute()
    {
        register_rest_route(sayhelloImageResizer()->apiNamespace, $this->routeImage . '/', [
            'methods' => ['GET',],
            'callback' => [$this, 'createImageFromPath'],
            'permission_callback' => '__return_true'
        ]);
    }

    private function getImageIdBySlug(string $slug): int
    {
        $args = [
            'post_type' => 'attachment',
            'name' => sanitize_title($slug),
            'posts_per_page' => 1,
            'post_status' => 'inherit',
        ];
        $_header = get_posts($args);
        $image = $_header ? array_pop($_header) : null;
        return $image ? intval($image->ID) : 0;
    }

    private function getSettings(array $folderParts): array
    {
        $width = 0;
        $height = 0;
        $quality = 100;
        $blur = 0;

        foreach ($folderParts as $part) {
            if (str_starts_with($part, 'size-')) {
                $size = str_replace('size-', '', $part);
                $sizeParts = explode('x', $size);
                if (count($sizeParts) >= 1) {
                    $width = intval($sizeParts[0]);
                }
                if (count($sizeParts) >= 2) {
                    $height = intval($sizeParts[1]);
                }
            }
            if (str_starts_with($part, 'blur-')) {
                $blur = intval(str_replace('blur-', '', $part));
            }
            if (str_starts_with($part, 'quality-')) {
                $quality = intval(str_replace('quality-', '', $part));
            }
        }

        return [
            'width' => $width,
            'height' => $height,
            'quality' => $quality,
            'blur' => $blur,
        ];
    }

    public function createImageFromPath($data): null|WP_Error
    {
        if (!array_key_exists('path', $_GET)) return new WP_Error('invalid_image_path', 'Invalid Image Path', ['status' => 400]);

        $path = $_GET['path'];
        $parts = explode('.', $path);
        $extension = $parts[1];
        $folder = $parts[0];
        $allowedExtensions = array_keys(Helpers::getSupportedExtensions());

        if (!in_array($extension, $allowedExtensions)) return new WP_Error('invalid_image_extension', 'Invalid Image Extension', ['status' => 400, 'allowedExtensions' => $allowedExtensions]);

        $folderParts = $folder ? explode('/', $folder) : [];
        $imageName = array_pop($folderParts);
        $imageId = $this->getImageIdBySlug($imageName);
        if (!$imageId) return new WP_Error('invalid_image_slug', 'Invalid Image Slug', ['status' => 400]);

        $settings = $this->getSettings($folderParts);

        // todo: limit possible settings
        $uploadDir = wp_upload_dir();

        $dir = trailingslashit($uploadDir['basedir']) . sayhelloImageResizer()->Htaccess->folder . '/';
        $destPath = $dir . $path;
        $srcPath = get_attached_file($imageId);

        $image = new GenerateImage($srcPath);
        $image->setSizes($settings['width'], $settings['height']);
        $image->setQuality($settings['quality']);
        $image->setBlur($settings['blur']);
        $image->setFormat($extension);

        $image->save($destPath);
        $image->echoImage();
        return null;
    }

    public function getImageUrl(int $imageId, int $width = 0, int $height = 0, int $quality = 0, int $blur = 0): string
    {
        $uploadDir = wp_upload_dir();
        $baseUl = trailingslashit($uploadDir['baseurl']) . sayhelloImageResizer()->prefix . '/';

        $file = get_post($imageId);
        $p = explode('.', $file->guid);
        $ext = end($p);
        $name = $file->post_name;

        $parts = [];
        if ($width || $height) {
            $parts[] = "size-{$width}x{$height}";
        }
        if ($quality) {
            $parts[] = "quality-{$quality}";
        }
        if ($blur) {
            $parts[] = "blur-{$blur}";
        }

        $parts[] = $name;

        return $baseUl . implode('/', $parts) . ".{$ext}";
    }
}