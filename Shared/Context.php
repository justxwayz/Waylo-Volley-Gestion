<?php
// Tout ce dont une slice a besoin pour traiter une requête (injecté, donc testable).
final class Context
{
    public function __construct(
        public PDO $db,
        public Session $session,
        public DateTimeImmutable $now,
        public array $get = [],
        public array $post = [],
        public string $method = 'GET',
    ) {}

    public function user(): ?array { return $this->session->user(); }
    public function isPost(): bool { return $this->method === 'POST'; }
}
