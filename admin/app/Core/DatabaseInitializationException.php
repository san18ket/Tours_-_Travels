<?php
namespace App\Core;

use RuntimeException;
use Throwable;
use PDOException;

/**
 * Base Exception for Database Initialization and Deployment Failures.
 * Provides user-friendly, non-technical error messages and secure logging.
 */
class DatabaseInitializationException extends RuntimeException
{
    protected string $userMessage;
    protected string $userDescription;
    protected string $incidentId;
    protected ?string $technicalDetails;

    public function __construct(
        string $userMessage,
        string $userDescription = '',
        ?Throwable $previous = null,
        ?string $technicalDetails = null
    ) {
        $this->userMessage = $userMessage;
        $this->userDescription = $userDescription;
        $this->incidentId = 'REF-DB-' . strtoupper(substr(hash('sha256', uniqid('dbi_', true)), 0, 8));
        $this->technicalDetails = $technicalDetails ?? ($previous ? $previous->getMessage() : null);

        // Parent message includes incident code and safe message
        parent::__construct("[{$this->incidentId}] {$userMessage}", 0, $previous);
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    public function getUserDescription(): string
    {
        return $this->userDescription;
    }

    public function getIncidentId(): string
    {
        return $this->incidentId;
    }

    public function getTechnicalDetails(): ?string
    {
        return $this->technicalDetails;
    }

    /**
     * Map a raw PDOException to a safe, user-friendly DatabaseInitializationException.
     * Prevents any raw SQL or credential leakage to end users.
     */
    public static function fromPDOException(PDOException $e): self
    {
        $code = (int)$e->getCode();
        $msg = $e->getMessage();

        // 1. Database creation denied / insufficient permissions (Shared hosting)
        if ($code === 1044 || $code === 1142 || str_contains($msg, 'Access denied for user') && str_contains($msg, 'to database')) {
            return new DatabaseCreationDeniedException(
                "Database setup is incomplete. Please create the database and check the database settings.",
                "Your hosting provider does not permit automatic database creation. Please create the database manually in your hosting control panel (e.g. cPanel MySQL Databases) and update DB_DATABASE in your .env file.",
                $e,
                $msg
            );
        }

        // 2. Authentication failure / Bad credentials / Server unavailable
        if ($code === 1045 || $code === 2002 || str_contains($msg, 'using password') || str_contains($msg, 'Connection refused')) {
            return new DatabaseConnectionException(
                "Unable to connect to the database. Please check the database settings.",
                "Please verify your database host, port, username, and password in your server configuration (.env).",
                $e,
                $msg
            );
        }

        // 3. Unknown database (does not exist and could not be reached)
        if ($code === 1049 || str_contains($msg, 'Unknown database')) {
            return new DatabaseCreationDeniedException(
                "Database setup is incomplete. Please create the database and check the database settings.",
                "The configured database does not exist. Please create it in your hosting control panel and verify your .env settings.",
                $e,
                $msg
            );
        }

        // 4. Generic safe database error
        return new DatabaseConnectionException(
            "Unable to connect to the database. Please check the database settings.",
            "Please check the database configuration settings in your .env file.",
            $e,
            $msg
        );
    }

    /**
     * Render a professional, safe HTTP response (JSON for API, HTML for web)
     */
    public function renderResponse(?Request $request = null): void
    {
        $isJson = false;
        if ($request !== null && method_exists($request, 'isJson')) {
            $isJson = $request->isJson();
        } else {
            $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
            $isJson = str_contains($accept, 'application/json') || str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/');
        }

        if (!headers_sent()) {
            http_response_code(503); // Service Unavailable
        }

        // Securely log technical details
        Logger::error("Database Initialization Interrupted [{$this->incidentId}]: {$this->userMessage}", [
            'incident_id' => $this->incidentId,
            'description' => $this->userDescription,
            'technical'   => $this->technicalDetails,
            'trace'       => $this->getTraceAsString(),
        ]);

        if ($isJson) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode([
                'success'     => false,
                'message'     => $this->userMessage,
                'description' => $this->userDescription,
                'incident_id' => $this->incidentId
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            return;
        }

        // Web rendering
        $title = 'Database Status';
        $icon = '⚙️';
        if ($this instanceof DatabaseCreationDeniedException) {
            $title = 'Database Setup Incomplete';
            $icon = '📦';
        } elseif ($this instanceof DatabaseConnectionException) {
            $title = 'Database Connection Notice';
            $icon = '🔌';
        } elseif ($this instanceof DatabaseMigrationException) {
            $title = 'Database Update Notice';
            $icon = '🛠️';
        }

        try {
            echo View::render('setup/db_error', [
                'title'           => $title,
                'icon'            => $icon,
                'userMessage'     => $this->userMessage,
                'userDescription' => $this->userDescription,
                'incidentId'      => $this->incidentId,
            ], 'layouts/setup');
        } catch (Throwable $renderEx) {
            // Self-contained fallback HTML if View system cannot load
            $safeMessage = htmlspecialchars($this->userMessage, ENT_QUOTES, 'UTF-8');
            $safeDescription = htmlspecialchars($this->userDescription, ENT_QUOTES, 'UTF-8');
            $safeIncident = htmlspecialchars($this->incidentId, ENT_QUOTES, 'UTF-8');

            echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>{$title}</title><style>body{margin:0;padding:2.5rem 1rem;font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:radial-gradient(circle at top right,#1E3E62,#0B192C);color:#FFF;min-height:100vh;display:flex;align-items:center;justify-content:center}.box{max-width:600px;width:100%;background:#FFF;border-radius:12px;overflow:hidden;box-shadow:0 20px 40px rgba(0,0,0,0.5);color:#1E293B}.head{background:#152943;color:#FFF;padding:1.25rem 1.5rem;font-size:1.15rem;font-weight:700;display:flex;align-items:center;gap:0.5rem}.body{padding:2rem}.alert{background:#FFFBEB;border:1px solid #FCD34D;border-radius:8px;padding:1.25rem;margin-bottom:1.25rem}.alert h3{color:#92400E;margin:0 0 0.5rem 0;font-size:1.05rem}.alert p{color:#B45309;margin:0 0 0.5rem 0;font-size:0.92rem;line-height:1.5}.alert .ref{font-family:monospace;font-size:0.8rem;color:#78350F}.note{color:#64748B;font-size:0.85rem;line-height:1.6;margin-bottom:1.5rem}.btn{display:inline-block;background:#1E3E62;color:#FFF;padding:0.65rem 1.25rem;border-radius:6px;text-decoration:none;font-weight:600;font-size:0.9rem}</style></head><body><div class='box'><div class='head'><span>{$icon}</span> <span>{$title}</span></div><div class='body'><div class='alert'><h3>{$safeMessage}</h3><p>{$safeDescription}</p><div class='ref'>Incident Reference: <strong>{$safeIncident}</strong></div></div><p class='note'>Technical details have been securely logged. Passwords, SQL queries, and server traces are masked for security.</p><div style='text-align:right;'><a href='/' class='btn'>Retry Connection</a></div></div></div></body></html>";
        }
    }

    /**
     * Render generic fallback when unexpected error occurs
     */
    public static function renderGenericError(?Request $request = null, ?Throwable $e = null): void
    {
        $instance = new self(
            "Database setup is incomplete. Please create the database and check the database settings.",
            "An error occurred while connecting or initializing the database. Please check your .env settings.",
            $e
        );
        $instance->renderResponse($request);
    }
}

/**
 * Thrown when credentials fail or MySQL host is unreachable
 */
class DatabaseConnectionException extends DatabaseInitializationException
{
    public function __construct(
        string $userMessage = "Unable to connect to the database. Please check the database settings.",
        string $userDescription = "Please check your database host, port, username, and password in the .env configuration.",
        ?Throwable $previous = null,
        ?string $technicalDetails = null
    ) {
        parent::__construct($userMessage, $userDescription, $previous, $technicalDetails);
    }
}

/**
 * Thrown when database does not exist and automatic creation is denied by hosting permissions
 */
class DatabaseCreationDeniedException extends DatabaseInitializationException
{
    public function __construct(
        string $userMessage = "Database setup is incomplete. Please create the database and check the database settings.",
        string $userDescription = "Your hosting provider does not permit automatic database creation. Please create the database manually in your hosting control panel (e.g. cPanel MySQL Databases) and update DB_DATABASE in your .env file.",
        ?Throwable $previous = null,
        ?string $technicalDetails = null
    ) {
        parent::__construct($userMessage, $userDescription, $previous, $technicalDetails);
    }
}

/**
 * Thrown when a migration fails execution
 */
class DatabaseMigrationException extends DatabaseInitializationException
{
    public function __construct(
        string $userMessage = "Database update could not be completed. Please try again or contact the administrator.",
        string $userDescription = "A database migration could not be applied. Technical details have been logged securely and all uncommitted changes were rolled back.",
        ?Throwable $previous = null,
        ?string $technicalDetails = null
    ) {
        parent::__construct($userMessage, $userDescription, $previous, $technicalDetails);
    }
}
