<?php

namespace App\Services\Knowledge;

class ChunkingService
{
    public function chunk(string $text, int $targetLength = 1200, int $overlapLength = 180): array
    {
        $text = trim($text);

        if ($text === '') {
            return [];
        }

        $paragraphs = preg_split("/\n\s*\n/", $text) ?: [];
        $chunks = [];
        $current = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);

            if ($paragraph === '') {
                continue;
            }

            if (strlen($paragraph) > $targetLength) {
                $chunks = array_merge($chunks, $this->splitLongText($paragraph, $targetLength, $overlapLength));
                continue;
            }

            $candidate = trim($current === '' ? $paragraph : "{$current}\n\n{$paragraph}");

            if (strlen($candidate) > $targetLength && $current !== '') {
                $chunks[] = $current;
                $current = $this->tail($current, $overlapLength)."\n\n".$paragraph;
            } else {
                $current = $candidate;
            }
        }

        if (trim($current) !== '') {
            $chunks[] = trim($current);
        }

        return array_values(array_filter(array_map('trim', $chunks)));
    }

    private function splitLongText(string $text, int $targetLength, int $overlapLength): array
    {
        $chunks = [];
        $offset = 0;
        $length = strlen($text);

        while ($offset < $length) {
            $chunk = substr($text, $offset, $targetLength);
            $chunks[] = trim($chunk);
            $offset += max(1, $targetLength - $overlapLength);
        }

        return $chunks;
    }

    private function tail(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return trim(substr($text, -$length));
    }
}
