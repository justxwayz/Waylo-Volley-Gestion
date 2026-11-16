<?php
// Session utilisateur + messages flash + jeton CSRF.
final class Session
{
    public function __construct(private array &$data) {}

    public static function native(): self
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
            session_start();
        }
        return new self($_SESSION);
    }

    /** Session en mémoire (tests) */
    public static function memory(): self
    {
        $d = [];
        return new self($d);
    }

    public function user(): ?array
    {
        return $this->data['user'] ?? null;
    }

    public function login(array $membre): void
    {
        $this->data['user'] = ['id' => (int)$membre['id'], 'nom' => $membre['nom'], 'role' => $membre['role']];
        $this->data['csrf'] = bin2hex(random_bytes(16));
    }

    public function logout(): void
    {
        $this->data = [];
    }

    public function flash(string $message, string $type = 'ok'): void
    {
        $this->data['flash'][] = ['m' => $message, 't' => $type];
    }

    public function takeFlash(): array
    {
        $f = $this->data['flash'] ?? [];
        unset($this->data['flash']);
        return $f;
    }

    public function csrf(): string
    {
        return $this->data['csrf'] ??= bin2hex(random_bytes(16));
    }

    public function checkCsrf(?string $token): bool
    {
        return $token !== null && hash_equals($this->csrf(), $token);
    }
}
