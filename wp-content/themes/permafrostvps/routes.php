<?php

require_once get_template_directory() . '/services/AccountService.php';

// ==================== API Rutter ====================

/**
 * Registera alla API rutter
 */
function register_all_routes() {
    $accountService = new AccountService();

    // ==================== Autentiseringsrutter ====================
     
    /**
     * Inloggningsrutt
     */
    register_rest_route('auth/v1', '/login', [
        'methods' => 'POST',
        'callback' => [$accountService, 'login'],
        'permission_callback' => '__return_true'
    ]);
    
    /**
     * Utloggningsrutt
     */
    register_rest_route('auth/v1', '/logout', [
        'methods' => 'POST',
        'callback' => [$accountService, 'logout'],
        'permission_callback' => '__return_true'
    ]);
    
    /**
     * Verifieringsrutt
     */
    register_rest_route('auth/v1', '/verify', [
        'methods' => 'POST',
        'callback' => [$accountService, 'verify'],
        'permission_callback' => '__return_true'
    ]);
    
    /**
     * Profilrutt
     */
    register_rest_route('auth/v1', '/profile', [
        'methods' => 'GET',
        'callback' => [$accountService, 'profile'],
        'permission_callback' => '__return_true'
    ]);

    
    // ==================== WP-editor rutter ====================

    /**
     * Meny-rutt
     */
    register_rest_route('wp-editor/v1', '/menus/(?P<location>[a-zA-Z0-9_-]+)', [
        'methods' => 'GET',
        'callback' => fn($data): array|WP_Error => get_menu_by_location($data),
        'permission_callback' => '__return_true'
    ]);

    /**
     * Site Config-rutt
     */
    register_rest_route('wp-editor/v1', '/site-config', [
        'methods' => 'GET',
        'callback' => fn(): array|WP_ERROR => get_site_config(),
        'permission_callback' => '__return_true'
    ]);

    /**
     * Sections-rutt
     */
    register_rest_route('wp-editor/v1', '/sections/(?P<page_id>\d+)', [
        'methods' => 'GET',
        'callback' => fn($data): array|WP_Error => get_sections($data),
        'permission_callback' => '__return_true'
    ]);

    
    // Övriga rutter
    register_rest_route('permafrost/v1', '/data', [
        'methods' => 'GET',
        'callback' => function() {
            return [
                'message' => 'This is some custom data from the Permafrost VPS API!!'
            ];
        },
        'permission_callback' => [$accountService, 'onlyAdmin']
    ]);
}

add_action('rest_api_init', 'register_all_routes');
 
