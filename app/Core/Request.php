<?php
namespace App\Core;

class Request
{
    private array $get;
    private array $post;
    private array $server;
    private array $files;
    private ?array $json = null;

    public function __construct(
        ?array $get = null,
        ?array $post = null,
        ?array $server = null,
        ?array $files = null
    ) {
        $this->get = $get ?? $_GET;
        $this->post = $post ?? $_POST;
        $this->server = $server ?? $_SERVER;
        $this->files = $files ?? $_FILES;
    }

    public static function capture(): self
    {
        return new self();
    }

    public function getMethod(): string
    {
        $method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST') {
            if (isset($this->post['_method'])) {
                $override = strtoupper($this->post['_method']);
                if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                    return $override;
                }
            } elseif (isset($this->server['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                return strtoupper($this->server['HTTP_X_HTTP_METHOD_OVERRIDE']);
            }
        }
        return $method;
    }

    public function getPath(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $position = strpos($uri, '?');
        if ($position !== false) {
            $uri = substr($uri, 0, $position);
        }

        // Strip subfolder if app is served from a subdirectory
        $scriptName = dirname($this->server['SCRIPT_NAME'] ?? '');
        if ($scriptName !== '/' && $scriptName !== '\\' && strpos($uri, $scriptName) === 0) {
            $uri = substr($uri, strlen($scriptName));
        }

        $uri = '/' . trim($uri, '/');
        return $uri === '/' ? '/' : rtrim($uri, '/');
    }

    public function isJson(): bool
    {
        $contentType = $this->server['CONTENT_TYPE'] ?? '';
        return strpos($contentType, 'application/json') !== false;
    }

    public function getJson(): array
    {
        if ($this->json === null) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            $this->json = is_array($data) ? $data : [];
        }
        return $this->json;
    }

    public function get(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->get;
        }
        return $this->get[$key] ?? $default;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        return $this->get($key, $default);
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        $data = $this->all();
        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        $data = $this->all();
        return array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '';
    }

    public function exists(string $key): bool
    {
        $data = $this->all();
        return array_key_exists($key, $data);
    }

    public function wantsJson(): bool
    {
        $accept = $this->server['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json');
    }

    public function all(): array
    {
        $data = array_merge($this->get, $this->post);
        if ($this->isJson()) {
            $jsonData = $this->getJson();
            if (!empty($jsonData)) {
                $data = array_merge($data, $jsonData);
            }
        }
        return $data;
    }

    public function getHeader(string $name): ?string
    {
        $formatted = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$formatted] ?? null;
    }

    public function getClientIp(): string
    {
        if (!empty($this->server['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $this->server['HTTP_X_FORWARDED_FOR']);
            $candidate = trim($ips[0]);
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }

        if (!empty($this->server['HTTP_CLIENT_IP']) && filter_var($this->server['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
            return $this->server['HTTP_CLIENT_IP'];
        }

        return $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    public function getFile(string $key): ?array
    {
        return $this->hasFile($key) ? $this->files[$key] : null;
    }

    /**
     * Store uploaded file securely outside document root or in designated storage
     */
    public function storeUploadedFile(string $key, string $subDirectory = 'documents'): ?string
    {
        $file = $this->getFile($key);
        if (!$file) {
            return null;
        }

        $uploadConfig = Config::get('security.uploads');
        if ($file['size'] > ($uploadConfig['max_file_size'] ?? 5242880)) {
            throw new \InvalidArgumentException('Uploaded file exceeds maximum permitted size of 5MB.');
        }

        // Verify MIME type using FileInfo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        $allowedMimes = $uploadConfig['allowed_mimes'] ?? [];
        if (!array_key_exists($mime, $allowedMimes)) {
            throw new \InvalidArgumentException('Unsupported file format. Only JPG, PNG, WEBP and PDF files are permitted.');
        }

        $extension = $allowedMimes[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;

        $targetDir = Config::get('paths.private_uploads') . '/' . trim($subDirectory, '/');
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0750, true);
        }

        $destination = $targetDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('Failed to save uploaded file safely.');
        }

        return trim($subDirectory, '/') . '/' . $filename;
    }
}
