<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

final class PdfLicenseStamper
{
    public function stampFile(string $path, string $customerName): string
    {
        $pdf = @file_get_contents($path);

        if ($pdf === false) {
            throw new RuntimeException('Unable to read the PDF file for download.');
        }

        return $this->stamp($pdf, $customerName);
    }

    public function stamp(string $pdf, string $customerName): string
    {
        if (str_contains($pdf, '/Type /XRef') || !str_contains($pdf, 'xref')) {
            throw new RuntimeException('This PDF format is not supported for licensed downloads.');
        }

        $objects = $this->parseObjects($pdf);
        $pages = $this->findPageObjects($objects);

        if ($pages === []) {
            throw new RuntimeException('No printable pages were found in this PDF.');
        }

        $trailer = $this->parseTrailer($pdf);
        $nextObjectNumber = max(array_keys($objects)) + 1;
        $fontObjectNumber = $nextObjectNumber++;
        $updates = [
            [
                'number' => $fontObjectNumber,
                'generation' => 0,
                'body' => "<<\n/Type /Font\n/Subtype /Type1\n/BaseFont /Helvetica\n/Encoding /WinAnsiEncoding\n>>",
            ],
        ];

        $resourceCache = [];
        $licenseLine = sprintf(
            "\u{00A9} 2026 The Resonanz Music Studio Publishing. Commissioned by %s. All Rights Reserved.",
            $customerName !== '' ? $customerName : 'Customer',
        );

        foreach ($pages as $page) {
            $pageBody = $page['body'];
            $contentReference = $this->extractReference($pageBody, 'Contents');
            $resourceReference = $this->extractReference($pageBody, 'Resources');

            if ($contentReference === null || $resourceReference === null) {
                throw new RuntimeException('This PDF page structure is not supported for licensed downloads.');
            }

            [$width, $height] = $this->extractPageSize($pageBody);
            $fontSize = $this->determineFontSize($width, $licenseLine);
            $x = $this->determineHorizontalPosition($width, $licenseLine, $fontSize);
            $y = max(14.0, min(22.0, $height * 0.02));

            $contentObjectNumber = $nextObjectNumber++;
            $updates[] = [
                'number' => $contentObjectNumber,
                'generation' => 0,
                'body' => $this->buildFooterStream($licenseLine, $fontSize, $x, $y),
            ];

            $resourceKey = $resourceReference['number'] . ':' . $resourceReference['generation'];

            if (!isset($resourceCache[$resourceKey])) {
                $resourceObjects = $this->buildResourceUpdates(
                    $objects,
                    $resourceReference,
                    $fontObjectNumber,
                    $nextObjectNumber,
                );

                foreach ($resourceObjects['updates'] as $resourceUpdate) {
                    $updates[] = $resourceUpdate;
                }

                $resourceCache[$resourceKey] = $resourceObjects['resource_reference'];
                $nextObjectNumber = $resourceObjects['next_object_number'];
            }

            $updatedPageBody = $this->replaceReference(
                $pageBody,
                'Resources',
                $resourceCache[$resourceKey],
            );
            $updatedPageBody = $this->appendContentReference($updatedPageBody, $contentObjectNumber);

            $updates[] = [
                'number' => $page['number'],
                'generation' => $page['generation'],
                'body' => $updatedPageBody,
            ];
        }

        return $this->appendIncrementalUpdate($pdf, $updates, $trailer, $nextObjectNumber);
    }

    private function parseObjects(string $pdf): array
    {
        preg_match_all('/(\d+)\s+(\d+)\s+obj\b(.*?)\bendobj\b/s', $pdf, $matches, PREG_SET_ORDER);
        $objects = [];

        foreach ($matches as $match) {
            $objects[(int) $match[1]] = [
                'number' => (int) $match[1],
                'generation' => (int) $match[2],
                'body' => trim($match[3]),
            ];
        }

        if ($objects === []) {
            throw new RuntimeException('Unable to read the PDF object structure.');
        }

        return $objects;
    }

    private function findPageObjects(array $objects): array
    {
        $pages = [];

        foreach ($objects as $object) {
            if (preg_match('/\/Type\s*\/Page\b/', $object['body']) === 1) {
                $pages[] = $object;
            }
        }

        return $pages;
    }

    private function parseTrailer(string $pdf): array
    {
        if (
            preg_match('/trailer\s*<<(.*?)>>\s*startxref\s*(\d+)\s*%%EOF\s*$/s', $pdf, $match) !== 1
        ) {
            throw new RuntimeException('Unable to read the PDF trailer.');
        }

        $dictionary = $match[1];
        $trailer = [
            'prev' => (int) $match[2],
            'root' => $this->extractTrailerValue($dictionary, 'Root'),
            'info' => $this->extractTrailerValue($dictionary, 'Info'),
            'encrypt' => $this->extractTrailerValue($dictionary, 'Encrypt'),
            'id' => $this->extractTrailerId($dictionary),
        ];

        if ($trailer['root'] === null) {
            throw new RuntimeException('Unable to locate the PDF catalog reference.');
        }

        return $trailer;
    }

    private function extractTrailerValue(string $dictionary, string $key): ?string
    {
        if (preg_match('/\/' . preg_quote($key, '/') . '\s+(\d+\s+\d+\s+R)/', $dictionary, $match) === 1) {
            return trim($match[1]);
        }

        return null;
    }

    private function extractTrailerId(string $dictionary): ?string
    {
        if (preg_match('/\/ID\s*(\[[^\]]+\])/s', $dictionary, $match) === 1) {
            return trim($match[1]);
        }

        return null;
    }

    private function extractReference(string $body, string $key): ?array
    {
        if (preg_match('/\/' . preg_quote($key, '/') . '\s+(\d+)\s+(\d+)\s+R\b/', $body, $match) !== 1) {
            return null;
        }

        return [
            'number' => (int) $match[1],
            'generation' => (int) $match[2],
            'value' => $match[1] . ' ' . $match[2] . ' R',
        ];
    }

    private function extractPageSize(string $pageBody): array
    {
        if (
            preg_match(
                '/\/MediaBox\s*\[\s*(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s*\]/',
                $pageBody,
                $match,
            ) !== 1
        ) {
            return [595.0, 842.0];
        }

        $width = (float) $match[3] - (float) $match[1];
        $height = (float) $match[4] - (float) $match[2];

        return [$width, $height];
    }

    private function determineFontSize(float $pageWidth, string $text): float
    {
        $fontSize = 8.0;
        $maxWidth = max(120.0, $pageWidth - 72.0);

        while ($fontSize > 5.5 && $this->estimateTextWidth($text, $fontSize) > $maxWidth) {
            $fontSize -= 0.5;
        }

        return $fontSize;
    }

    private function determineHorizontalPosition(float $pageWidth, string $text, float $fontSize): float
    {
        $textWidth = $this->estimateTextWidth($text, $fontSize);
        return max(18.0, ($pageWidth - $textWidth) / 2);
    }

    private function estimateTextWidth(string $text, float $fontSize): float
    {
        return strlen($this->encodePdfText($text)) * $fontSize * 0.52;
    }

    private function buildFooterStream(
        string $text,
        float $fontSize,
        float $x,
        float $y,
    ): string {
        $encodedText = $this->escapePdfLiteral($this->encodePdfText($text));
        $stream = implode("\n", [
            '<<',
            '/Length ' . strlen('q BT /TRMS ' . $fontSize . ' Tf 0 g 1 0 0 1 ' . sprintf('%.2F %.2F', $x, $y) . ' Tm (' . $encodedText . ') Tj ET Q' . "\n"),
            '>>',
            'stream',
            'q BT /TRMS ' . $fontSize . ' Tf 0 g 1 0 0 1 ' . sprintf('%.2F %.2F', $x, $y) . ' Tm (' . $encodedText . ') Tj ET Q',
            'endstream',
        ]);

        return $stream;
    }

    private function encodePdfText(string $text): string
    {
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);

        return $encoded !== false ? $encoded : $text;
    }

    private function escapePdfLiteral(string $text): string
    {
        $escaped = '';

        for ($index = 0, $length = strlen($text); $index < $length; $index++) {
            $byte = ord($text[$index]);

            if ($byte === 92 || $byte === 40 || $byte === 41) {
                $escaped .= '\\' . chr($byte);
                continue;
            }

            if ($byte < 32 || $byte > 126) {
                $escaped .= sprintf('\\%03o', $byte);
                continue;
            }

            $escaped .= chr($byte);
        }

        return $escaped;
    }

    private function buildResourceUpdates(
        array $objects,
        array $resourceReference,
        int $fontObjectNumber,
        int $nextObjectNumber,
    ): array {
        $resourceObject = $objects[$resourceReference['number']] ?? null;

        if ($resourceObject === null) {
            throw new RuntimeException('Unable to locate the PDF resource dictionary.');
        }

        $resourceBody = trim($resourceObject['body']);
        $resourceUpdates = [];

        if (preg_match('/\/Font\s*<<(.*?)>>/s', $resourceBody, $match) === 1) {
            $mergedFontDictionary = "/Font <<\n" . trim($match[1]) . "\n/TRMS {$fontObjectNumber} 0 R\n>>";
            $resourceBody = preg_replace('/\/Font\s*<<(.*?)>>/s', $mergedFontDictionary, $resourceBody, 1) ?? $resourceBody;
        } elseif (preg_match('/\/Font\s+(\d+)\s+(\d+)\s+R\b/', $resourceBody, $match) === 1) {
            $fontDictionaryObject = $objects[(int) $match[1]] ?? null;

            if ($fontDictionaryObject === null) {
                throw new RuntimeException('Unable to locate the PDF font dictionary.');
            }

            $updatedFontObjectNumber = $nextObjectNumber++;
            $updatedFontBody = $this->appendNamedResource(trim($fontDictionaryObject['body']), 'TRMS', "{$fontObjectNumber} 0 R");
            $resourceUpdates[] = [
                'number' => $updatedFontObjectNumber,
                'generation' => 0,
                'body' => $updatedFontBody,
            ];

            $resourceBody = preg_replace(
                '/\/Font\s+\d+\s+\d+\s+R\b/',
                '/Font ' . $updatedFontObjectNumber . ' 0 R',
                $resourceBody,
                1,
            ) ?? $resourceBody;
        } else {
            $resourceBody = $this->appendNamedResource($resourceBody, 'Font', "<<\n/TRMS {$fontObjectNumber} 0 R\n>>");
        }

        $updatedResourceObjectNumber = $nextObjectNumber++;
        $resourceUpdates[] = [
            'number' => $updatedResourceObjectNumber,
            'generation' => 0,
            'body' => $resourceBody,
        ];

        return [
            'updates' => $resourceUpdates,
            'resource_reference' => $updatedResourceObjectNumber . ' 0 R',
            'next_object_number' => $nextObjectNumber,
        ];
    }

    private function appendNamedResource(string $dictionary, string $name, string $value): string
    {
        $position = strrpos($dictionary, '>>');

        if ($position === false) {
            throw new RuntimeException('Unable to update the PDF resource dictionary.');
        }

        return substr($dictionary, 0, $position)
            . "\n/{$name} {$value}\n"
            . substr($dictionary, $position);
    }

    private function replaceReference(string $body, string $key, string $reference): string
    {
        $updated = preg_replace(
            '/\/' . preg_quote($key, '/') . '\s+\d+\s+\d+\s+R\b/',
            '/' . $key . ' ' . $reference,
            $body,
            1,
        );

        if ($updated === null || $updated === $body) {
            throw new RuntimeException('Unable to update the PDF page reference.');
        }

        return $updated;
    }

    private function appendContentReference(string $pageBody, int $contentObjectNumber): string
    {
        if (preg_match('/\/Contents\s+(\d+\s+\d+\s+R)\b/', $pageBody, $match) === 1) {
            return preg_replace(
                '/\/Contents\s+\d+\s+\d+\s+R\b/',
                '/Contents [' . trim($match[1]) . ' ' . $contentObjectNumber . ' 0 R]',
                $pageBody,
                1,
            ) ?? $pageBody;
        }

        if (preg_match('/\/Contents\s*\[(.*?)\]/s', $pageBody, $match) === 1) {
            $contentArray = trim($match[1]);
            $replacement = '/Contents [' . trim($contentArray . ' ' . $contentObjectNumber . ' 0 R') . ']';

            return preg_replace('/\/Contents\s*\[(.*?)\]/s', $replacement, $pageBody, 1) ?? $pageBody;
        }

        throw new RuntimeException('Unable to update the PDF page contents.');
    }

    private function appendIncrementalUpdate(
        string $originalPdf,
        array $updates,
        array $trailer,
        int $size,
    ): string {
        usort(
            $updates,
            static fn (array $left, array $right): int => $left['number'] <=> $right['number'],
        );

        $increment = '';
        $offsets = [];
        $baseOffset = strlen($originalPdf);

        foreach ($updates as $update) {
            $offsets[$update['number']] = $baseOffset + strlen($increment);
            $increment .= $update['number'] . ' ' . $update['generation'] . " obj\n"
                . rtrim($update['body']) . "\nendobj\n";
        }

        $xrefOffset = $baseOffset + strlen($increment);
        $increment .= "xref\n";

        $groups = $this->groupObjectNumbers(array_keys($offsets));

        foreach ($groups as $group) {
            $increment .= $group['start'] . ' ' . count($group['numbers']) . "\n";

            foreach ($group['numbers'] as $number) {
                $increment .= sprintf("%010d %05d n \n", $offsets[$number], 0);
            }
        }

        $increment .= "trailer\n<<\n";
        $increment .= '/Size ' . $size . "\n";
        $increment .= '/Root ' . $trailer['root'] . "\n";

        if ($trailer['info'] !== null) {
            $increment .= '/Info ' . $trailer['info'] . "\n";
        }

        if ($trailer['encrypt'] !== null) {
            $increment .= '/Encrypt ' . $trailer['encrypt'] . "\n";
        }

        if ($trailer['id'] !== null) {
            $increment .= '/ID ' . $trailer['id'] . "\n";
        }

        $increment .= '/Prev ' . $trailer['prev'] . "\n";
        $increment .= ">>\n";
        $increment .= "startxref\n{$xrefOffset}\n%%EOF";

        return $originalPdf . $increment;
    }

    private function groupObjectNumbers(array $numbers): array
    {
        sort($numbers, SORT_NUMERIC);
        $groups = [];
        $currentGroup = [];

        foreach ($numbers as $number) {
            if ($currentGroup === [] || $number === end($currentGroup) + 1) {
                $currentGroup[] = $number;
                continue;
            }

            $groups[] = [
                'start' => $currentGroup[0],
                'numbers' => $currentGroup,
            ];
            $currentGroup = [$number];
        }

        if ($currentGroup !== []) {
            $groups[] = [
                'start' => $currentGroup[0],
                'numbers' => $currentGroup,
            ];
        }

        return $groups;
    }
}
