<?php

namespace Barryvdh\DomPDF;

if (class_exists(PdfFactory::class)) {
    return;
}

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class PdfFactory
{
    public function loadView(string $view, array $data = [], array $mergeData = [], array $options = []): SimplePdf
    {
        $rendered = view($view, $data, $mergeData)->render();

        return new SimplePdf($rendered, $options);
    }
}

class SimplePdf
{
    protected string $content;

    /** @var array<string, mixed> */
    protected array $options;

    public function __construct(string $html, array $options = [])
    {
        $this->content = strip_tags($html, '<br>');
        $this->options = $options;
    }

    public function download(string $fileName)
    {
        $pdf = $this->buildPdf($this->content);

        return Response::make($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function buildPdf(string $html): string
    {
        $text = $this->normalizeText($html);
        $lines = explode("\n", $text);

        $contentStream = $this->buildContentStream($lines);

        $objects = [
            '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj',
            '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj',
            '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >> endobj',
            sprintf('4 0 obj << /Length %d >> stream\n%s\nendstream endobj', strlen($contentStream), $contentStream),
            '5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj',
        ];

        $header = "%PDF-1.4\n";
        $body = '';
        $offsets = [];

        foreach ($objects as $object) {
            $offsets[] = strlen($header) + strlen($body);
            $body .= $object . "\n";
        }

        $xrefStart = strlen($header) + strlen($body);
        $xref = 'xref\n0 ' . (count($objects) + 1) . "\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $xref .= str_pad((string) $offset, 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $trailer = 'trailer << /Size ' . (count($objects) + 1) . ' /Root 1 0 R >>\nstartxref\n' . $xrefStart . "\n%%EOF";

        return $header . $body . $xref . $trailer;
    }

    /** @param array<int, string> $lines */
    private function buildContentStream(array $lines): string
    {
        $stream = [
            'BT',
            '/F1 12 Tf',
            '12 TL',
            '1 0 0 1 50 800 Tm',
        ];

        foreach ($lines as $index => $line) {
            if ($index === 0) {
                $stream[] = '(' . $this->escapePdfText($line) . ') Tj';
                continue;
            }

            $stream[] = 'T*';
            $stream[] = '(' . $this->escapePdfText($line) . ') Tj';
        }

        $stream[] = 'ET';

        return implode("\n", $stream);
    }

    private function normalizeText(string $html): string
    {
        $text = str_replace(['<br>', '<br/>', '<br />'], "\n", $html);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $lines = array_map(fn ($line) => trim($line), preg_split('/\r?\n/', $text));

        $filtered = array_filter($lines, fn ($line) => $line !== '');

        if (empty($filtered)) {
            $filtered = ['Laporan kosong'];
        }

        return implode("\n", $filtered);
    }

    private function escapePdfText(string $text): string
    {
        $replacements = [
            '\\' => '\\\\',
            '(' => '\\(',
            ')' => '\\)',
        ];

        return strtr($text, $replacements);
    }
}

namespace Barryvdh\DomPDF\Facade;

use Illuminate\Support\Facades\Facade;

class Pdf extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'pdf';
    }
}
