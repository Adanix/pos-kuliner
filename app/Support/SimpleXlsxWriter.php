<?php

namespace App\Support;

use Exception;
use ZipArchive;

class SimpleXlsxWriter
{
    /**
     * Build an XLSX file and return the temporary file path.
     *
     * @param array $headers List of header strings.
     * @param array $rows 2D array of data rows.
     * @param array $numericColumns Array of 0-based column indices that should be formatted as numeric with thousand separator.
     * @return string Path to the created temporary .xlsx file.
     */
    public static function create(array $headers, array $rows, array $numericColumns = []): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_export_');
        $zip = new ZipArchive();

        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Tidak dapat membuat file temporary XLSX.");
        }

        // 1. [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
            '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // 2. _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // 3. xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
            '</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // 4. xl/workbook.xml
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<bookViews><workbookView xWindow="0" yWindow="0" windowWidth="20000" windowHeight="12000"/></bookViews>' .
            '<sheets>' .
            '<sheet name="Laporan Transaksi" sheetId="1" r:id="rId1"/>' .
            '</sheets>' .
            '</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbook);

        // 5. xl/styles.xml
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<fonts count="2">' .
            '<font><sz val="10"/><name val="Arial"/></font>' . // font 0: normal
            '<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Arial"/></font>' . // font 1: bold white
            '</fonts>' .
            '<fills count="3">' .
            '<fill><patternFill patternType="none"/></fill>' . // fill 0
            '<fill><patternFill patternType="gray125"/></fill>' . // fill 1
            '<fill><patternFill patternType="solid"><fgColor rgb="FF14110F"/></patternFill></fill>' . // fill 2: dark header
            '</fills>' .
            '<borders count="1"><border><left/><right/><top/><bottom/></border></borders>' .
            '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>' .
            '<cellXfs count="3">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' . // style 0: regular
            '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"><alignment horizontal="center" vertical="center"/></xf>' . // style 1: header
            '<xf numFmtId="3" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"><alignment horizontal="right"/></xf>' . // style 2: currency #,##0
            '</cellXfs>' .
            '</styleSheet>';
        $zip->addFromString('xl/styles.xml', $styles);

        // 6. xl/worksheets/sheet1.xml
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<sheetViews><sheetView tabSelected="1" workbookViewId="0"/></sheetViews>' .
            '<sheetFormatPr defaultRowHeight="18"/>' .
            '<cols>';

        // Set column widths
        foreach ($headers as $colIdx => $hdr) {
            $colNum = $colIdx + 1;
            $width = in_array($colIdx, $numericColumns, true) ? 18 : 22;
            if ($colIdx === 0) $width = 24; // Transaction Number
            $sheetXml .= '<col min="' . $colNum . '" max="' . $colNum . '" width="' . $width . '" customWidth="1"/>';
        }
        $sheetXml .= '</cols><sheetData>';

        // Write Header Row (Row 1)
        $sheetXml .= '<row r="1" ht="26" customHeight="1">';
        foreach ($headers as $colIdx => $hdr) {
            $cellRef = self::columnLetter($colIdx) . '1';
            $escaped = htmlspecialchars($hdr, ENT_XML1, 'UTF-8');
            $sheetXml .= '<c r="' . $cellRef . '" t="inlineStr" s="1"><is><t>' . $escaped . '</t></is></c>';
        }
        $sheetXml .= '</row>';

        // Write Data Rows
        $rowNum = 2;
        foreach ($rows as $row) {
            $sheetXml .= '<row r="' . $rowNum . '">';
            foreach ($row as $colIdx => $val) {
                $cellRef = self::columnLetter($colIdx) . $rowNum;
                $isNum = in_array($colIdx, $numericColumns, true) && is_numeric($val);

                if ($isNum) {
                    $sheetXml .= '<c r="' . $cellRef . '" t="n" s="2"><v>' . (float)$val . '</v></c>';
                } else {
                    $escaped = htmlspecialchars((string)$val, ENT_XML1, 'UTF-8');
                    $sheetXml .= '<c r="' . $cellRef . '" t="inlineStr" s="0"><is><t>' . $escaped . '</t></is></c>';
                }
            }
            $sheetXml .= '</row>';
            $rowNum++;
        }

        $sheetXml .= '</sheetData></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();

        return $tempFile;
    }

    /**
     * Convert zero-based column index to Excel column letter (0 => A, 27 => AB).
     */
    protected static function columnLetter(int $colIndex): string
    {
        $letter = '';
        $colIndex++;
        while ($colIndex > 0) {
            $modulo = ($colIndex - 1) % 26;
            $letter = chr(65 + $modulo) . $letter;
            $colIndex = (int)(($colIndex - $modulo) / 26);
        }
        return $letter;
    }
}
