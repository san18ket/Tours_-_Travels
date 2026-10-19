<?php
namespace App\Core;

class View
{
    /**
     * Render a view wrapped in an optional layout
     */
    public static function render(string $viewPath, array $data = [], ?string $layout = 'layouts/main'): string
    {
        // Global variables available in all views
        $data['app'] = [
            'name'     => Config::get('app.name'),
            'tagline'  => Config::get('app.tagline'),
            'currency' => Config::get('app.currency_symbol', '₹'),
        ];
        $data['user'] = Session::getUser();
        $data['userId'] = Session::getUserId();
        $data['userRole'] = Session::getUserRole();
        $data['flashes'] = Session::getAllFlashes();
        $data['csrf_token'] = Csrf::getToken();

        // Extract view data
        extract($data, EXTR_SKIP);

        $viewsDir = dirname(__DIR__) . '/Views/';
        $viewFile = $viewsDir . trim($viewPath, '/') . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View file not found: {$viewPath}");
        }

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        if ($layout === null) {
            return $content;
        }

        $layoutFile = $viewsDir . trim($layout, '/') . '.php';
        if (!file_exists($layoutFile)) {
            throw new \RuntimeException("Layout file not found: {$layout}");
        }

        ob_start();
        include $layoutFile;
        return ob_get_clean();
    }

    /**
     * Include a reusable view component
     */
    public static function component(string $componentName, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        $file = dirname(__DIR__) . '/Views/components/' . trim($componentName, '/') . '.php';
        if (!file_exists($file)) {
            return "<!-- Component {$componentName} not found -->";
        }

        ob_start();
        include $file;
        return ob_get_clean();
    }

    /**
     * Safe HTML escape
     */
    public static function e(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
