<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('truncate_words', [$this, 'truncateWords']),
        ];
    }

    public function truncateWords(string $text, int $limit = 20, string $suffix = '...'): string
    {
        $words = preg_split('/\s+/', trim($text));

        if (count($words) <= $limit) {
            return $text;
        }

        return implode(' ', array_slice($words, 0, $limit)) . $suffix;
    }
}