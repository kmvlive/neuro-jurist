<?php

namespace App\Services;

use Smalot\PdfParser\Parser as PdfParser;
use ZipArchive;

class DocumentParser
{
    public const MAX_LENGTH = 15000;
    public const ALLOWED_EXT = ['pdf', 'docx', 'txt', 'doc'];

    public function extract(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        $text = match ($ext) {
            'pdf' => $this->parsePdf($path),
            'docx' => $this->parseDocx($path),
            'txt' => file_get_contents($path),
            default => '',
        };
        
        // Очищаем от лишних пробелов
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        // Обрезаем до лимита
        if (mb_strlen($text) > self::MAX_LENGTH) {
            $text = mb_substr($text, 0, self::MAX_LENGTH) . '... [документ обрезан]';
        }
        
        return $text;
    }

    private function parsePdf(string $path): string
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($path);
            return $pdf->getText();
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function parseDocx(string $path): string
    {
        try {
            $zip = new ZipArchive();
            if ($zip->open($path) !== true) return '';
            
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            
            if (!$xml) return '';
            
            // Извлекаем текст из <w:t> тегов
            preg_match_all('/<w:t[^>]*>([^<]*)<\/w:t>/', $xml, $matches);
            return implode(' ', $matches[1] ?? []);
        } catch (\Throwable $e) {
            return '';
        }
    }
}
