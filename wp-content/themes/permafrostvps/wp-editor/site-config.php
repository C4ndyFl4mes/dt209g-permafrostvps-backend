<?php

/**
 * Registrerar den anpassade posttypen "site_config".
 */
add_action('init', function () {
    register_post_type('site_config', [
        'label' => 'Site Configuration',
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-admin-generic',
        'show-in-rest' => true,
        'map_meta_cap' => true,
        'supports' => ['title'],
        'rest_base' => 'site-config'
    ]);
});

/**
 * Hämtar site configuration data.
 *
 * @return array|WP_Error - array med site configuration data eller WP_Error om ingen data finns.
 */
function get_site_config(): array | WP_ERROR {
    $posts = get_posts([
        'post_type' => 'site_config',
        'numberposts' => 1,
        'post_status' => 'publish'
    ]);

    if (!$posts || count($posts) === 0) {
        return new WP_Error('no_site_config', 'No Site Configuration found.', ['status' => 404]);
    }

    $site_config = [
        'layout_settings' => get_fields($posts[0]->ID),
        'other_settings' => [] // Framtida inställningar...
    ];

    return $site_config ?? new WP_Error('no_site_config_data', 'No Site Configuration data found.', ['status' => 404]);
}
