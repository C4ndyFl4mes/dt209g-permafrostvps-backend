<?php

/**
 * Värdeobjekt för typehinting av användare.
 */
final class User {

    /**
     * Konstruktor för User-objektet.
     * @param int $id - Användarens ID.
     * @param string $username - Användarnamn.
     * @param string $email - E-postadress.
     * @param string $role - Användarens roll.
     */
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly string $email,
        public readonly string $role
    ) {}

    /**
     * Skapar ett User-objekt från en array.
     * @param array $data - Array med användardata.
     * @return User|null - Returnerar ett User-objekt eller null om data är ogiltig.
     */
    public static function fromArray(array $data): self | null {
        if (!isset($data['id'], $data['username'], $data['email'], $data['role'])) {
            return null;
        }

        return new User(
            id: (int)$data['id'],
            username: (string)$data['username'],
            email: (string)$data['email'],
            role: (string)$data['role']
        );
    }
}