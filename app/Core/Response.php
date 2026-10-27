<?php
namespace App\Core;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private ?string $content = null;

    public function __construct(?string $content = null, int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public static function make(?string $content = null, int $statusCode = 200, array $headers = []): self
    {
        return new self($content, $statusCode, $headers);
    }

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): ?string
    {
        foreach ($this->headers as $k => $v) {
            if (strcasecmp($k, $name) === 0) {
                return $v;
            }
        }
        return null;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getBody(): ?string
    {
        return $this->content;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public static function json(mixed $data, int $status = 200, array $headers = []): self
    {
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        return new self(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $status, $headers);
    }

    public static function redirect(string $url, int $status = 302): self
    {
        return new self(null, $status, ['Location' => $url]);
    }

    /**
     * Send secure file download
     */
    public static function download(string $absolutePath, string $downloadFilename, string $contentType = 'application/octet-stream'): void
    {
        if (!file_exists($absolutePath) || !is_readable($absolutePath)) {
            http_response_code(404);
            echo "File not found.";
            exit;
        }

        self::applySecurityHeaders();

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . basename($downloadFilename) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($absolutePath));

        readfile($absolutePath);
        exit;
    }

    public static function applySecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        $headers = Config::get('security.headers', []);
        foreach ($headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // Add HSTS if running over HTTPS
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->statusCode);
            self::applySecurityHeaders();

            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }
        }

        if ($this->content !== null) {
            echo $this->content;
        }
    }
}
