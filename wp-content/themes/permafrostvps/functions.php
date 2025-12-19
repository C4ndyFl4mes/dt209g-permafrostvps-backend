<?php

// Load API Routes
require_once get_template_directory() . '/routes.php';

function register_my_menus() {
    register_nav_menus(
        array(
            'sidebar-menu' => __( 'Sidebar Menu' )
        )
    );
}
add_action( 'init', 'register_my_menus' );
