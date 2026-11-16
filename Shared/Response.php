<?php
final class Response
{
    private function __construct(public string $body, public ?string $redirect, public int $status) {}

    public static function html(string $body, int $status = 200): self { return new self($body, null, $status); }
    public static function redirect(string $url): self { return new self('', $url, 302); }

    public function send(): void
    {
        http_response_code($this->status);
        if ($this->redirect !== null) {
            header('Location: ' . $this->redirect);
            return;
        }
        header('Content-Type: text/html; charset=utf-8');
        echo $this->body;
    }
}
