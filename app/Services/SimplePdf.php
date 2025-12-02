<?php

namespace App\Services;

class SimplePdf
{
    private float $width;
    private float $height;

    /** @var SimplePdfPage[] */
    private array $pages = [];

    public function __construct(float $width = 612.0, float $height = 792.0)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function addPage(): SimplePdfPage
    {
        $page = new SimplePdfPage($this->width, $this->height);
        $this->pages[] = $page;

        return $page;
    }

    public function height(): float
    {
        return $this->height;
    }

    public function output(): string
    {
        $objects = [];
        $objectId = 1;

        $fontObjectId = $objectId++;
        $pageObjectIds = [];
        $contentObjectIds = [];

        foreach ($this->pages as $page) {
            $contentObjectIds[] = $objectId++;
            $pageObjectIds[] = $objectId++;
        }

        $pagesObjectId = $objectId++;
        $catalogObjectId = $objectId++;

        $objects[$fontObjectId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

        foreach ($this->pages as $index => $page) {
            $contentStream = $page->renderContent();
            $contentLength = strlen($contentStream);
            $contentObjectId = $contentObjectIds[$index];
            $objects[$contentObjectId] = "<< /Length {$contentLength} >>\nstream\n{$contentStream}endstream";

            $pageObjectId = $pageObjectIds[$index];
            $objects[$pageObjectId] = '<< /Type /Page /Parent ' . $pagesObjectId . ' 0 R ' .
                '/MediaBox [0 0 ' . $this->width . ' ' . $this->height . '] ' .
                '/Contents ' . $contentObjectId . ' 0 R ' .
                '/Resources << /Font << /F1 ' . $fontObjectId . ' 0 R >> >> >>';
        }

        $kids = implode(' ', array_map(static fn ($id) => $id . ' 0 R', $pageObjectIds));
        $objects[$pagesObjectId] = '<< /Type /Pages /Count ' . count($this->pages) . ' /Kids [' . $kids . '] >>';
        $objects[$catalogObjectId] = '<< /Type /Catalog /Pages ' . $pagesObjectId . ' 0 R >>';

        $buffer = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $id => $content) {
            $offsets[$id] = strlen($buffer);
            $buffer .= $id . " 0 obj\n" . $content . "\nendobj\n";
        }

        $xrefPosition = strlen($buffer);
        $buffer .= "xref\n0 " . ($catalogObjectId + 1) . "\n";
        $buffer .= sprintf("%010d %05d f \n", 0, 65535);

        for ($i = 1; $i <= $catalogObjectId; $i++) {
            $offset = $offsets[$i] ?? 0;
            $buffer .= sprintf("%010d %05d n \n", $offset, 0);
        }

        $buffer .= "trailer\n<< /Size " . ($catalogObjectId + 1) . ' /Root ' . $catalogObjectId . " 0 R >>\n";
        $buffer .= "startxref\n{$xrefPosition}\n%%EOF";

        return $buffer;
    }
}

class SimplePdfPage
{
    private float $width;
    private float $height;

    private array $lines = [];

    public function __construct(float $width, float $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function addText(float $x, float $y, string $text, int $fontSize = 12): void
    {
        $this->lines[] = [
            'x' => $x,
            'y' => $y,
            'text' => $text,
            'size' => $fontSize,
        ];
    }

    public function addTextFromTop(float $x, float $topOffset, string $text, int $fontSize = 12): void
    {
        $y = $this->height - $topOffset;
        $this->addText($x, $y, $text, $fontSize);
    }

    public function renderContent(): string
    {
        $chunks = [];

        foreach ($this->lines as $line) {
            $escapedText = $this->escapeText($line['text']);
            $chunks[] = 'BT'
                . " /F1 {$line['size']} Tf"
                . " {$line['x']} {$line['y']} Td"
                . " ({$escapedText}) Tj"
                . ' ET';
        }

        return implode("\n", $chunks) . "\n";
    }

    private function escapeText(string $text): string
    {
        $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);

        return preg_replace("/\r|\n/", ' ', $escaped);
    }
}
