<?php

class AppController {
    // Check if current request method is GET
    protected function isGet(): bool {
        return $_SERVER["REQUEST_METHOD"] === 'GET';
    }

    // Check if current request method is POST
    protected function isPost(): bool {
        return $_SERVER["REQUEST_METHOD"] === 'POST';
    }

    // Render view template with optional variables
    protected function render(string $template = null, array $variables = []) {
        $templatePath = 'public/views/' . $template . '.html';
        $templatePath404 = 'public/views/404.html';
        $output = "";

        // If template exists, load it with passed variables
        if (file_exists($templatePath)) {
            // Convert array keys into variables for template usage
            extract($variables);
            ob_start();
            include $templatePath;
            $output = ob_get_clean();

        } else {
            // Fallback to 404 page if template not found
            ob_start();
            include $templatePath404;
            $output = ob_get_clean();
        }
        
        // Output final rendered HTML
        echo $output;
    }
}