<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMProcessingInstruction;
use DOMXPath;

class ReportPdfDocument
{
    /** @return array{html: string, orientation: string} */
    public function prepare(string $html): array
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        // Dompdf's HTML5 parser must not receive the encoding processing instruction.
        foreach (iterator_to_array($document->childNodes) as $node) {
            if ($node instanceof DOMProcessingInstruction) {
                $document->removeChild($node);
            }
        }

        $xpath = new DOMXPath($document);
        $maxColumns = 0;
        foreach ($xpath->query('//table/thead/tr') as $row) {
            $columns = 0;
            foreach ($row->childNodes as $cell) {
                if ($cell instanceof DOMElement && in_array($cell->tagName, ['th', 'td'], true)) {
                    $columns += max(1, (int) $cell->getAttribute('colspan'));
                }
            }
            $maxColumns = max($maxColumns, $columns);
        }

        foreach ($xpath->query('//table') as $table) {
            $rows = iterator_to_array($xpath->query('./tbody/tr', $table));
            if (count($rows) <= 40) {
                continue;
            }

            // Long tables make Dompdf repeatedly copy the remaining rows at page breaks.
            // Keep each table small, repeating its headings without splitting rowspans.
            $chunks = [];
            $chunk = [];
            $spanRemaining = 0;
            foreach ($rows as $row) {
                $chunk[] = $row;
                foreach ($xpath->query('./td | ./th', $row) as $cell) {
                    $spanRemaining = max($spanRemaining, (int) $cell->getAttribute('rowspan'));
                }
                $spanRemaining = max(0, $spanRemaining - 1);
                if (count($chunk) >= 40 && $spanRemaining === 0) {
                    $chunks[] = $chunk;
                    $chunk = [];
                }
            }
            if ($chunk) {
                $chunks[] = $chunk;
            }

            foreach ($chunks as $index => $chunk) {
                $part = $table->cloneNode(false);
                foreach ($xpath->query('./caption | ./colgroup | ./thead', $table) as $header) {
                    $part->appendChild($header->cloneNode(true));
                }
                $body = $document->createElement('tbody');
                foreach ($chunk as $row) {
                    $body->appendChild($row);
                }
                $part->appendChild($body);
                if ($index === array_key_last($chunks)) {
                    foreach ($xpath->query('./tfoot', $table) as $footer) {
                        $part->appendChild($footer->cloneNode(true));
                    }
                }
                $table->parentNode->insertBefore($part, $table);
            }
            $table->parentNode->removeChild($table);
        }

        $orientation = $maxColumns > 6 ? 'landscape' : 'portrait';
        $style = $document->createElement('style', '@page { size: A4 '.$orientation.'; }');
        $document->getElementsByTagName('head')->item(0)->appendChild($style);

        return ['html' => $document->saveHTML(), 'orientation' => $orientation];
    }
}
