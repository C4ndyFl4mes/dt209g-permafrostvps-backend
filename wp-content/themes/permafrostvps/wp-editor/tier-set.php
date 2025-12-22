<?php

/**
 * Registrerar den anpassade posttypen "tier-set".
 */
add_action('init', function () {
    register_post_type('tier-set', [
        'label' => 'Global Tier Set',
        'menu_icon' => 'dashicons-admin-generic',
        'public' => false,
        'show_ui' => true,
        'supports' => ['title', 'menu-order']
    ]);
});