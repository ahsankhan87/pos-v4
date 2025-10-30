<?php

namespace App\Libraries;

use CodeIgniter\HTTP\ResponseInterface;

class ReportExporter
{
    /**
     * Output array data as CSV.
     * @param string $filename
     * @param array<int,array<string,mixed>> $rows
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public static function toCsv(string $filename, array $rows, ResponseInterface $response): ResponseInterface
    {
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);
        $response = $response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');

        $fp = fopen('php://temp', 'w+');
        if ($fp === false) {
            return $response->setBody('Failed to open temp stream');
        }
        // headers from first row keys
        $headers = [];
        foreach ($rows as $row) {
            $headers = array_keys($row);
            break;
        }
        if (!empty($headers)) {
            fputcsv($fp, $headers);
        }
        foreach ($rows as $row) {
            // ensure order matches headers
            if (!empty($headers)) {
                $ordered = [];
                foreach ($headers as $h) {
                    $ordered[] = isset($row[$h]) ? (is_scalar($row[$h]) ? (string)$row[$h] : json_encode($row[$h])) : '';
                }
                fputcsv($fp, $ordered);
            } else {
                $mapped = array();
                foreach ($row as $v) {
                    $mapped[] = is_scalar($v) ? (string)$v : json_encode($v);
                }
                fputcsv($fp, $mapped);
            }
        }
        rewind($fp);
        $csv = stream_get_contents($fp) ?: '';
        fclose($fp);
        return $response->setBody($csv);
    }

    /**
     * Output array data as PDF via Dompdf if available; otherwise returns printable HTML.
     * @param string $filename
     * @param string $title
     * @param array<string,string> $meta Key-value meta info
     * @param array<string> $columns Column names/labels
     * @param array<int,array<string,mixed>> $rows
     */
    public static function toPdf(string $filename, string $title, array $meta, array $columns, array $rows, ResponseInterface $response): ResponseInterface
    {
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);
        $html = self::renderHtml($title, $meta, $columns, $rows);

        $dompdfClass = '\\Dompdf\\Dompdf';
        if (class_exists($dompdfClass)) {
            $dompdf = new $dompdfClass();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $output = $dompdf->output();
            return $response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($output);
        }
        // Fallback: return HTML with content-disposition so user can print to PDF
        return $response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '.html"')
            ->setBody($html . '\n<!-- Install dompdf/dompdf via Composer to get true PDF output. -->');
    }

    /**
     * Build a simple printable HTML table for export.
     * @param array<string,string> $meta
     * @param array<string> $columns
     * @param array<int,array<string,mixed>> $rows
     */
    protected static function renderHtml(string $title, array $meta, array $columns, array $rows): string
    {
        $metaRows = '';
        foreach ($meta as $k => $v) {
            $metaRows .= '<tr><th style="text-align:left;padding:4px 8px;background:#f7f7f7">' . htmlspecialchars((string)$k) . '</th><td style="padding:4px 8px">' . htmlspecialchars((string)$v) . '</td></tr>';
        }
        $thead = '';
        foreach ($columns as $c) {
            $thead .= '<th style="padding:6px 8px;border-bottom:1px solid #ddd;text-align:left">' . htmlspecialchars((string)$c) . '</th>';
        }
        $tbody = '';
        foreach ($rows as $r) {
            $tbody .= '<tr>';
            foreach ($columns as $c) {
                $val = $r[$c] ?? '';
                $tbody .= '<td style="padding:6px 8px;border-bottom:1px solid #f1f1f1">' . htmlspecialchars(is_scalar($val) ? (string)$val : json_encode($val)) . '</td>';
            }
            $tbody .= '</tr>';
        }
        return '<!doctype html><html><head><meta charset="utf-8"><title>' . htmlspecialchars($title) . '</title>' .
            '<style>body{font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#111;margin:16px} h1{font-size:18px;margin:0 0 8px} table{border-collapse:collapse;width:100%} .meta{margin:10px 0 16px} .small{color:#666;font-size:11px}</style>' .
            '</head><body>' .
            '<h1>' . htmlspecialchars($title) . '</h1>' .
            '<table class="meta">' . $metaRows . '</table>' .
            '<table><thead><tr>' . $thead . '</tr></thead><tbody>' . $tbody . '</tbody></table>' .
            '<div class="small">Generated at ' . date('Y-m-d H:i:s') . '</div>' .
            '</body></html>';
    }
}
