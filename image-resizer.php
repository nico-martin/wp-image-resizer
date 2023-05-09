<?php

/*
Plugin Name: Image Resizer
Description: Resize and crop images on the fly
Author: Nico Martin - mail@nico.dev
Author URI: https://nico.dev
Version: 1.0.0
Text Domain: shir
Domain Path: /languages
Requires PHP: 7.4
Tested up to: 6.1.1
License: MIT
*/

defined('ABSPATH') or die();

require_once 'src/Helpers.php';
require_once 'src/Vendor/GenerateImage.php';
require_once 'src/Plugin.php';
require_once 'src/Package/Htaccess.php';
require_once 'src/Package/Image.php';

function sayhelloImageResizer(): \SayHello\ImageResizer\Plugin
{
    return SayHello\ImageResizer\Plugin::getInstance(__FILE__);
}

sayhelloImageResizer()->Htaccess = new SayHello\ImageResizer\Htaccess();
sayhelloImageResizer()->Htaccess->run();

sayhelloImageResizer()->Image = new SayHello\ImageResizer\Image();
sayhelloImageResizer()->Image->run();
