<?php

namespace SayHello\ImageResizer;

class Plugin
{
    private static ?Plugin $instance = null;
    public string $name = '';
    public string $version = '';
    public string $prefix = '';
    public string $apiNamespace = '';
    public bool $debug = false;
    public string $file = '';
    public string $url = '';

    public string $uploadDir = '';
    public string $optionKey = 'shir_data';

    public Htaccess $Htaccess;
    public Image $Image;

    public static function getInstance($file): Plugin
    {
        if (!isset(self::$instance)) {
            self::$instance = new Plugin();

            if (get_option(sayhelloImageResizer()->optionKey)) {
                $data = get_option(sayhelloImageResizer()->optionKey);
            } elseif (function_exists('get_plugin_data')) {
                $data = get_plugin_data($file);
            } else {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
                $data = get_plugin_data($file);
            }

            self::$instance->name = $data['Name'];
            self::$instance->version = $data['Version'];

            self::$instance->prefix = 'shir';
            self::$instance->apiNamespace = 'image-resizer/v1';
            self::$instance->debug = true;
            self::$instance->file = $file;
            self::$instance->url = plugin_dir_url($file);

            self::$instance->run();
        }

        return self::$instance;
    }

    public function run()
    {
        add_action('plugins_loaded', [$this, 'loadPluginTextdomain']);
        add_action('admin_init', [$this, 'updatePluginData']);
        register_deactivation_hook(sayhelloImageResizer()->file, [$this, 'deactivate']);
        register_activation_hook(sayhelloImageResizer()->file, [$this, 'activate']);

        add_filter('shir/PluginStrings', [$this, 'pluginStrings']);
    }

    /**
     * Load translation files from the indicated directory.
     */
    public function loadPluginTextdomain()
    {
        load_plugin_textdomain(
            'shir',
            false,
            dirname(plugin_basename(sayhelloImageResizer()->file)) . '/languages'
        );
    }

    /**
     * Update Assets Data
     */
    public function updatePluginData()
    {

        $db_data = get_option(sayhelloImageResizer()->optionKey);
        $file_data = get_plugin_data(sayhelloImageResizer()->file);

        if (!$db_data || version_compare($file_data['Version'], $db_data['Version'], '>')) {

            sayhelloImageResizer()->name = $file_data['Name'];
            sayhelloImageResizer()->version = $file_data['Version'];

            update_option(sayhelloImageResizer()->optionKey, $file_data);
            if (!$db_data) {
                do_action('shir_on_first_activate');
            } else {
                do_action('shir_on_update', $db_data['Version'], $file_data['Version']);
            }
        }
    }

    public function activate()
    {
        do_action('SayHello/ImageResizer/onActivate');
    }

    public function deactivate()
    {
        do_action('SayHello/ImageResizer/onDeactivate');
        delete_option(sayhelloImageResizer()->optionKey);
    }

    public function pluginStrings($strings)
    {
        $strings['plugin.name'] = sayhelloImageResizer()->name;

        return $strings;
    }
}
