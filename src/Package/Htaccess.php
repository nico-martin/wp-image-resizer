<?php

namespace SayHello\ImageResizer;

class Htaccess
{
    public string $key = 'SayHelloImageResizer';
    public string $startFile = '';
    public string $endFile = '';
    public string $file = '';
    public string $regex = '';
    public string $folder = 'imager';
    public string $imagePrepend = '';

    public function __construct()
    {
        $this->startFile = "# BEGIN " . $this->key;
        $this->endFile = "# END " . $this->key;
        $this->file = ABSPATH . '.htaccess';
        $this->regex = '/(' . $this->startFile . '\n)(.*?)\n*(' . $this->endFile . ')/ms';
        $contentDir = str_replace(trailingslashit(get_home_url()), '', trailingslashit(wp_upload_dir()['baseurl']));
        $this->imagePrepend = "{$contentDir}{$this->folder}/";
    }

    public function run()
    {
        if (!is_writable($this->file)) {
            add_action('admin_notices', function () {
                $class = 'notice notice-error';
                $message = 'Irks! Your .htacces is not writable';
                printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
            });

            return;
        }

        add_action('SayHello/ImageResizer/onActivate', function () {
            $namespage = untrailingslashit(trailingslashit(sayhelloImageResizer()->apiNamespace) . sayhelloImageResizer()->Image->routeImage);
            $restEndpoint = "index.php?rest_route=/{$namespage}";
            $extensions = implode('|', array_keys(Helpers::getSupportedExtensions()));

            $this->set(implode(
                "\n",
                [
                    'RewriteEngine On',
                    'RewriteCond %{REQUEST_FILENAME} !-f',
                    "RewriteRule ^{$this->imagePrepend}(.+\.({$extensions}))$ /{$restEndpoint}&path=$1 [L,QSA]",
                ]
            ));
        });

        add_action('SayHello/ImageResizer/onDeactivate', function () {
            $this->delete();
        });
    }

    /**
     * Public Methods
     */

    public function set($msg)
    {
        $this->delete();
        $this->prepend($msg);
    }

    public function append($msg)
    {
        $content = str_replace($this->endFile, "$msg\n$this->startFile", $this->get_contents());
        $this->save($content);
    }

    public function prepend($msg)
    {
        $content = str_replace($this->startFile, "$this->startFile\n$msg", $this->get_contents());
        $this->save($content);
    }

    public function delete()
    {
        $content = $this->get_contents();
        preg_match_all($this->regex, $content, $matches, PREG_SET_ORDER, 0);
        if (empty($matches)) {
            return;
        } else {
            $content = str_replace($matches[0][0], '', $content);
            $this->save($content);
        }
    }

    /**
     * Helpers
     */

    private function get_contents()
    {
        $content = file_get_contents($this->file);
        preg_match_all($this->regex, $content, $matches, PREG_SET_ORDER, 0);
        if (empty($matches)) {
            return "$this->startFile\n$this->endFile\n$content";
        } elseif (count($matches) == 1) {
            return $content;
        } else {
            $i = 0;
            foreach ($matches as $match) {
                $i++;
                if (1 == $i) {
                    continue;
                }
                $content = str_replace($match[0], '', $content);
            }

            return $content;
        }
    }

    private function save($content)
    {
        // remove linebreaks if three or more in a line
        $content = preg_replace("/\n{3,}+/", "\n\n", $content);
        file_put_contents($this->file, $content);
    }
}