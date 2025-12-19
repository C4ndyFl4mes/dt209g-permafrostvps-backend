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
 
