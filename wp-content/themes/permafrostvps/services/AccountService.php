<?php

require_once get_template_directory() . '/services/TokenService.php';


/**
 * Service för hantering av användarkonton.
 */
class AccountService
{
    private TokenService $tokenService;

    public function __construct()
    {
        $this->tokenService = new TokenService();
    }

    /**
     * Hanterar inloggning av användare.
     * @param WP_REST_Request $request - REST-förfrågan med inloggningsdata.
     * @return array|WP_Error
     */
    public function login($request): array | WP_Error
    {
        $params = $request->get_json_params();
        $username = $params['username'] ?? null;
        $email = $params['email'] ?? null;
        $password = $params['password'] ?? null;

        if (!isset($username) && !isset($email) || !isset($password)) {
            return new WP_Error('invalid_request', 'Username/Email and password are required.', ['status' => 400]);
        }

        $user = null;
        if (!empty($username)) {
            $user = get_user_by('login', $username);
        } elseif (!empty($email)) {
            $user = get_user_by('email', $email);
        }

        if (!$user || !wp_check_password($password, $user->user_pass, $user->ID)) {
            return new WP_Error('invalid_credentials', 'Invalid username/email or password.', ['status' => 401]);
        }

        $user_role = $user->roles;
        $role = in_array('administrator', $user_role) ? 'admin' : 'subscriber';

        $token = $this->tokenService->generateToken($user, $role);
        $this->tokenService->setHTTPCookie($token);

        $expiry = $this->tokenService->getTokenExpiry();

        return [
            'success' => true,
            'expiry' => $expiry,
            'user' => [
                'id' => $user->ID,
                'username' => $user->user_login,
                'email' => $user->user_email,
                'role' => $role
            ]
        ];
    }

    /**
     * Hanterar utloggning av användare.
     * @return array
     */
    public function logout(): array {
        $this->tokenService->unsetHTTPCookie();
        return [
            'success' => true,
            'message' => 'Logged out successfully.'
        ];
    }

    /**
     * Verifierar om användaren är autentiserad.
     * @return array
     */
    public function verify(): array {
        $isAuthenticated = $this->tokenService->isAuthenticated();
        return [
            'authenticated' => $isAuthenticated
        ];
    }

    /**
     * Hämtar profilinformation för den autentiserade användaren.
     * @return array|WP_Error
     */
    public function profile(): array|WP_Error {
        if (!$this->tokenService->isAuthenticated()) {
            return new WP_Error('unauthorized', 'User is not authenticated.', ['status' => 401]);
        }

        $userData = $this->tokenService->getCurrentUser();
        if (!$userData) {
            return new WP_Error('user_not_found', 'User data not found.', ['status' => 404]);
        }

        return [
            'success' => true,
            'user' => $userData
        ];
    }

    /**
     * Kontrollerar om användaren är autentiserad.
     * @return bool
     */
    public function isAuthenticated(): bool {
        return $this->tokenService->isAuthenticated();
    }

    /**
     * Kontrollerar om den autentiserade användaren är administratör.
     * @return bool
     */
    public function onlyAdmin(): bool {
        return $this->tokenService->hasRole('admin');
    }
}
