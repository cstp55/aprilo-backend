<?php

namespace App\Services\Knowledge;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use ZipArchive;

class DocumentParser
{
    public function parse(string $path, string $type): string
    {
        $type = strtolower($type);

        return match ($type) {
            'txt', 'md', 'markdown' => $this->parsePlainText($path),
            'docx' => $this->parseDocx($path),
            'pdf' => $this->parsePdfBestEffort($path),
            default => throw new RuntimeException("Unsupported source type: {$type}"),
        };
    }

    private function parsePlainText(string $path): string
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('Unable to read source file.');
        }

        return $this->normalizeText($contents);
    }

    private function parseDocx(string $path): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('DOCX parsing requires the PHP Zip extension.');
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('Unable to open DOCX source file.');
        }

        $document = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($document === false) {
            throw new RuntimeException('DOCX document body was not found.');
        }

        $document = preg_replace('/<\/w:p>/', "\n", $document) ?? $document;
        $document = preg_replace('/<w:tab\/>/', "\t", $document) ?? $document;
        $text = html_entity_decode(strip_tags($document), ENT_QUOTES | ENT_XML1, 'UTF-8');

        return $this->normalizeText($text);
    }

    private function parsePdfBestEffort(string $path): string
    {
        // 1. Try production Smalot PDF Parser
        try {
            if (class_exists(\Smalot\PdfParser\Parser::class)) {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($path);
                $text = $pdf->getText();
                $text = $this->normalizeText($text);
                if (trim($text) !== '') {
                    return $text;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Smalot PDF parser failed: ' . $e->getMessage() . '. Falling back to regex extraction.');
        }

        // 2. Fallback to regex extraction
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('Unable to read PDF source file.');
        }

        preg_match_all('/\(([^()]*)\)/', $contents, $matches);
        $text = implode(' ', $matches[1] ?? []);
        $text = stripcslashes($text);
        $text = $this->normalizeText($text);

        if ($text === '') {
            throw new RuntimeException('PDF text extraction failed. Document has no extractable text or is corrupted.');
        }

        return $text;
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }
}
