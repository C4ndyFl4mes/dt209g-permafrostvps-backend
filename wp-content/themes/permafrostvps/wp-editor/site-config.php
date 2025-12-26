<?php


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
        'layout_settings' => [
            'sidebar_position' => get_field('sidebar_position', $posts[0]->ID),
            'banner_image' => get_field('banner_image', $posts[0]->ID)
        ],
        'color_settings' => [
            'primary_color' => get_field('primary_color', $posts[0]->ID),
            'secondary_color' => get_field('secondary_color', $posts[0]->ID),
            'primary_text_color' => get_field('primary_text_color', $posts[0]->ID),
            'secondary_text_color' => get_field('secondary_text_color', $posts[0]->ID),
            'button_color' => get_field('button_color', $posts[0]->ID),
            'button_text_color' => get_field('button_text_color', $posts[0]->ID),
            'link_color' => get_field('link_color', $posts[0]->ID)
        ],
        'seo_settings' => [
            'favicon' => get_field('favicon', $posts[0]->ID)
        ]
    ];

    return $site_config ?? new WP_Error('no_site_config_data', 'No Site Configuration data found.', ['status' => 404]);
}
