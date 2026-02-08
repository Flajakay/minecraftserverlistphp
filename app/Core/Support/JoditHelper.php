<?php

// Helper functions for integrating the Jodit editor in PHP views.

if (!function_exists('joditAssets')) {

    function joditAssets(): void
    {
        static $included = false;
        if ($included) return;
        $included = true;
        
        echo '<script src="' . asset('js/jodit-helper.js') . '"></script>' . "\n";
        echo '<script>' . "\n";
        echo 'document.addEventListener("DOMContentLoaded", function() {' . "\n";
        echo '    // Set editor language from PHP.' . "\n";
        echo '    window.joditHelper.setLanguage("' . lang('_jodit_code', 'en') . '");' . "\n";
        echo '});' . "\n";
        echo '</script>' . "\n";
    }
}

if (!function_exists('joditInit')) {
    function joditInit($selector, $type = 'page', $options = [], $placeholder = null): string
    {
        $placeholderText = $placeholder ?: lang('content_placeholder', 'Write your content here...');
        
        $jsOptions = '';
        if (!empty($options)) {
            $jsOptions = ', ' . json_encode($options);
        }

        $initMethod = match ($type) {
            'blog' => 'initBlogEditor',
            'page' => 'initPageEditor',
            default => 'init',
        };
        
        return "window.joditHelper.{$initMethod}('{$selector}', '{$placeholderText}'{$jsOptions});";
    }
}

if (!function_exists('joditValidation')) {
    function joditValidation($selector, $minLength = 10, $errorMessage = null): string
    {
        $errorMsg = $errorMessage ?: lang('blog_content_required', 'Content must be at least 10 characters long');
        
        return "
        const validation = window.joditHelper.validateContent('{$selector}', {$minLength});
        if (!validation.valid) {
            e.preventDefault();
            alert('{$errorMsg}');
            return false;
        }";
    }
}

if (!function_exists('joditScript')) {
    function joditScript($editors = [], $onReady = ''): string
    {
        $script = '<script>' . "\n";
        $script .= 'document.addEventListener("DOMContentLoaded", async function() {' . "\n";
        
        foreach ($editors as $config) {
            $selector = $config['selector'];
            $type = $config['type'] ?? 'page';
            $options = $config['options'] ?? [];
            $placeholder = $config['placeholder'] ?? null;
            $variable = $config['variable'] ?? null;
            
            $initCode = joditInit($selector, $type, $options, $placeholder);
            
            if ($variable) {
                $script .= "    const {$variable} = await {$initCode}\n";
            } else {
                $script .= "    await {$initCode}\n";
            }
        }
        
        if ($onReady) {
            $script .= "\n    " . $onReady . "\n";
        }
        
        $script .= '});' . "\n";
        $script .= '</script>' . "\n";
        
        return $script;
    }
}
