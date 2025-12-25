<?php


function register_custom_post_types()
{
    /**
     * Registrerar den anpassade posttypen "site_config".
     */
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

    /**
     * Registrerar den anpassade posttypen "section".
     */
    register_post_type('section', [
        'labels' => [
            'name' => 'Sections',
            'singular_name' => 'Section'
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-welcome-widgets-menus',
        'supports' => ['title', 'page-attributes']
    ]);

    /**
     * Registrerar den anpassade posttypen "tier-set".
     */
    register_post_type('tier-set', [
        'label' => 'Global Tier Set',
        'menu_icon' => 'dashicons-admin-generic',
        'public' => false,
        'show_ui' => true,
        'supports' => ['title', 'menu-order']
    ]);

    /**
     * Registrerar den anpassade posttypen "support".
     */
    register_post_type('support', [
        'label' => 'Support',
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-sos',
        'supports' => ['title', 'editor']
    ]);
}

add_action('init', 'register_custom_post_types');
