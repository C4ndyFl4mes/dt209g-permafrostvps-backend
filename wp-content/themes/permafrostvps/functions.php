<?php

// Hämtar anpassade posttyper.
require_once get_template_directory() . '/wp-editor/cpt.php';

// Hämtar site config.
require_once get_template_directory() . '/wp-editor/site-config.php';

// Hämtar sektioner.
require_once get_template_directory() . '/wp-editor/section.php';

// Hämtar menyn.
require_once get_template_directory() . '/wp-editor/menus.php';

// Hämtar API rutter
require_once get_template_directory() . '/routes.php';


add_theme_support('post-thumbnails');
// Image sizes
add_image_size("banner-small", 1200, 400, true);
add_image_size("banner-large", 3600, 1200, true);

// Fixar path för ACF JSON lagring och inläsning.
add_filter('acf/settings/save_json', function () {
    return WP_CONTENT_DIR . '/acf-json';
});

add_filter('acf/settings/load_json', function ($paths) {
    $paths[] = WP_CONTENT_DIR . '/acf-json';
    return $paths;
});