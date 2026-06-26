<?php

namespace App\Services;

class SpintaxService
{
    public function process(string $text, array $variables = []): string
    {
        $text = $this->replaceVariables($text, $variables);
        $text = $this->parse($text);
        return $text;
    }

    protected function parse(string $text): string
    {
        return preg_replace_callback('/\{([^{}]+)\}/', function ($matches) {
            $options = explode('|', $matches[1]);
            return $options[array_rand($options)];
        }, $text);
    }

    protected function replaceVariables(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }
        return $text;
    }

    public function hasSpintax(string $text): bool
    {
        return (bool) preg_match('/\{[^{}|]+\|[^{}]+\}/', $text);
    }

    public function variantCount(string $text): int
    {
        $count = 1;
        preg_replace_callback('/\{([^{}]+)\}/', function ($m) use (&$count) {
            $count *= count(explode('|', $m[1]));
        }, $text);
        return $count;
    }
}
