<?php

include_once get_template_directory() . '/value-objects/User.php';


/**
 * Service för hantering av JWT.
 */
class TokenService
{
    private string $secretKey = JWT_AUTH_SECRET_KEY; // Definierad i wp-config.php genom .env
    private int $expiry = 86400; // 24 timmar i sekunder
    private string $cookieName = 'jwt_auth_token'; // Autentiseringscookie namn

    //  Cookieinställningar
    private bool $cookieSecure = true;
    private bool $cookieHttpOnly = true;
    private string $cookieSameSite = 'Lax';

    // Håller reda på den aktuella tokenens utgångstid.
    private $currentTokenExpiry = null;

    /**
     * Metod för att generera en JWT för en given användare.
     * @param WP_User $user - WordPress-användarobjektet.
     * @param string $role - Användarens roll (t.ex. 'admin', 'subscriber').
     * @return string
     */
    public function generateToken(WP_User $user, string $role = 'subscriber'): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        $payload = [
            'iss' => get_bloginfo('url'),
            'iat' => time(),
            'exp' => time() + $this->expiry,
            'user' => [
                'id' => $user->ID,
                'username' => $user->user_login,
                'email' => $user->user_email,
                'role' => $role
            ]
        ];

        $this->currentTokenExpiry = $payload['exp'];

        $headerEncoded = $this->base64url_encode(json_encode($header));
        $payloadEncoded = $this->base64url_encode(json_encode($payload));

        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $this->secretKey, true);
        $signatureEncoded = $this->base64url_encode($signature);
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    /**
     * Verifierar en JWT.
     * @param string $token - JWT att verifiera.
     * @return bool
     */
    public function verifyToken(string $token): bool
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        $header = json_decode($this->base64url_decode($parts[0]), true);
        $payload = json_decode($this->base64url_decode($parts[1]), true);
        $signature = $this->base64url_decode($parts[2]);

        $expectedSignature = hash_hmac('sha256', $parts[0] . '.' . $parts[1], $this->secretKey, true);
        if (!hash_equals($signature, $expectedSignature)) {
            return false;
        }

        if (time() > $payload['exp']) {
            return false;
        }

        return true;
    }

    /**
     * Sätter JWT som en HTTP-cookie.
     * @param string|null $token - JWT att sätta som cookie. Om null, används befintlig cookie.
     * @return void
     */
    public function setHTTPCookie(string | null $token = null): void
    {
        $tokenToSet = $token ?? $_COOKIE[$this->cookieName] ?? null;

        if (!$tokenToSet) {
            return;
        }

        $is_local = in_array($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', '::1']);
        $secure = $this->cookieSecure && !$is_local;

        setcookie(
            $this->cookieName,
            $tokenToSet,
            [
                'expires' => time() + $this->expiry,
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'] ?? '',
                'secure' => $secure,
                'httponly' => $this->cookieHttpOnly,
                'samesite' => $this->cookieSameSite
            ]
        );
    }

    /**
     * Avlägsnar HTTP-cookie.
     * @return void
     */
    public function unsetHTTPCookie(): void
    {
        setcookie($this->cookieName, '', ['expires' => time() - 3600, 'path' => '/']);
        unset($_COOKIE[$this->cookieName]);
    }

    /**
     * Kontrollerar om användaren är autentiserad.
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        if (!isset($_COOKIE[$this->cookieName])) {
            return false;
        }

        return $this->verifyToken($_COOKIE[$this->cookieName]) !== false;
    }

    /**
     * Hämtar den aktuella användaren från token.
     * @return User|null
     */
    public function getCurrentUser(): User | null
    {

        if (!$this->isAuthenticated()) {
            return null;
        }

        $parts   = explode('.', $_COOKIE[$this->cookieName]);
        $payload = json_decode(
            $this->base64url_decode($parts[1]),
            true
        );

        if (!isset($payload['user']) || !is_array($payload['user'])) {
            return null;
        }

        return User::fromArray($payload['user'] ?? []);
    }

    /**
     * Hämtar tokenens utgångstid.
     * @return int|null
     */
    public function getTokenExpiry(): int | null
    {
        if ($this->currentTokenExpiry) {
            return $this->currentTokenExpiry;
        }

        if ($this->isAuthenticated()) {
            $parts = explode('.', $_COOKIE[$this->cookieName]);
            $payload = json_decode($this->base64url_decode($parts[1]), true);
            return $payload['exp'] ?? null;
        } else {
            return null;
        }
    }

    /**
     * Kontrollerar om den aktuella användaren har en specifik roll.
     * @param string $role - Rollen att kontrollera mot.
     * @return bool
     */
    public function hasRole($role): bool
    {
        $user = $this->getCurrentUser();
        if ($user && isset($user->role)) {
            return $user->role === $role;
        }
        return false;
    }

    /**
     * Hjälpfunktion för base64url-kodning.
     * @param string $data - Data att enkoda.
     * @return string
     */
    private function base64url_encode($data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Hjälpfunktion för base64url-dekodning.
     * @param string $data - Data att dekoda.
     * @return bool|string
     */
    private function base64url_decode($data): bool|string
    {
        $padding = 4 - (strlen($data) % 4);
        if ($padding < 4) {
            $data .= str_repeat('=', times: $padding);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
