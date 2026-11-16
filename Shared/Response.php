<?php
final class Response
{
    private function __construct(
        public string $body,
        public ?string $redirect,
        public int $status,
        public string $contentType = 'text/html; charset=utf-8',
        public ?string $filename = null,
    ) {}

    public static function html(string $body, int $status = 200): self { return new self($body, null, $status); }
    public static function redirect(string $url): self { return new self('', $url, 302); }

    /** Téléchargement d'un fichier (CSV, ...). */
    public static function fichier(string $contenu, string $nom, string $type = 'text/csv; charset=utf-8'): self
    {
        return new self($contenu, null, 200, $type, $nom);
    }

    public function send(): void
    {
        http_response_code($this->status);
        if ($this->redirect !== null) {
            header('Location: ' . $this->redirect);
            return;
        }
        header('Content-Type: ' . $this->contentType);
        if ($this->filename !== null) {
            header('Content-Disposition: attachment; filename="' . str_replace(['"', "\r", "\n"], '', $this->filename) . '"');
            header('X-Content-Type-Options: nosniff');
        }
        echo $this->body;
    }
}
