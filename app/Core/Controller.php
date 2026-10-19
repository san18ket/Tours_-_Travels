<?php
namespace App\Core;

use RuntimeException;

abstract class Controller
{
    protected function render(string $viewPath, array $data = [], ?string $layout = 'layouts/main'): Response
    {
        $html = View::render($viewPath, $data, $layout);
        return Response::make($html);
    }

    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function redirect(string $url, ?string $flashType = null, ?string $flashMessage = null): Response
    {
        if ($flashType && $flashMessage) {
            Session::setFlash($flashType, $flashMessage);
        }
        return Response::redirect($url);
    }

    /**
     * Centralized request input validation
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        $sanitized = [];

        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            $ruleList = is_array($ruleString) ? $ruleString : explode('|', $ruleString);

            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$ruleName, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                } else {
                    $ruleName = $rule;
                }

                switch ($ruleName) {
                    case 'required':
                        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                            $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
                        }
                        break;

                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = 'Please enter a valid email address.';
                        }
                        break;

                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' must be numeric.';
                        }
                        break;

                    case 'min':
                        $min = (int) $params[0];
                        if (!empty($value) && mb_strlen((string)$value) < $min) {
                            $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters.";
                        }
                        break;

                    case 'max':
                        $max = (int) $params[0];
                        if (!empty($value) && mb_strlen((string)$value) > $max) {
                            $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . " cannot exceed {$max} characters.";
                        }
                        break;

                    case 'in':
                        if (!empty($value) && !in_array($value, $params, true)) {
                            $errors[$field][] = 'Selected value is invalid.';
                        }
                        break;

                    case 'date':
                        if (!empty($value) && strtotime((string)$value) === false) {
                            $errors[$field][] = 'Please provide a valid date.';
                        }
                        break;
                }
            }

            $sanitized[$field] = is_string($value) ? trim($value) : $value;
        }

        if (!empty($errors)) {
            $firstMessage = reset($errors)[0];
            throw new ValidationException($firstMessage, $errors);
        }

        return $sanitized;
    }

    /**
     * Record-level ownership check
     */
    protected function authorizeOwnership(int $resourceOwnerId): void
    {
        $currentUserId = Session::getUserId();
        $role = Session::getUserRole();

        // Admins and dispatchers can view across records; drivers & customers only their own
        if ($role === 'admin' || $role === 'dispatcher') {
            return;
        }

        if ($currentUserId !== $resourceOwnerId) {
            Logger::security('Forbidden record access attempt', [
                'user_id' => $currentUserId,
                'owner_id' => $resourceOwnerId
            ]);
            throw new RuntimeException('You are not authorized to view or modify this record.');
        }
    }
}
