<?php

class HexViewer
{
    public function getFormatted($binary)
    {
        $chunkSize = 16;
        $eolString = PHP_EOL;
        $visualSplit = 8;


        $innerIterator = $maxRowLen = 0;
        $rawView = '';
        if ($visualSplit >= $chunkSize) { // Disable when it doesn't make sense.
            $visualSplit = false;
        }
        if ($chunkSize < 1) {
            $chunkSize = 16;
        }
        // If we're not on the command line, emit header to prevent broken formatting and file execution.
        if (PHP_SAPI != 'cli') {
            header("Content-Type: text/plain");
        }

        $sourceFileSize = strlen($binary);
        $outputFileHandle = '';
        // Auto Left Pad, prepends a zero for elegance.
        $leftPad = strlen(dechex((int)($sourceFileSize / $chunkSize))) + 1;
        // Draw Header table with ruler
        $xHeader = $xHeaderRule = str_repeat(' ', $leftPad) . '   ';
        for ($i = 0; $i < $chunkSize; $i++) {
            $xHeader .= str_pad(strtoupper(dechex($i)), 2, '0', STR_PAD_LEFT) . ' ';
            $xHeaderRule .= "-- ";

            if ($visualSplit != false) {
                if (($i + 1) % $visualSplit == 0) {
                    $xHeader .= '| ';
                    $xHeaderRule .= '| ';
                }
            }
        }
        $outputFileHandle .= "$xHeader$eolString$xHeaderRule$eolString";
        for ($i = 0; $i < $sourceFileSize; $i += $chunkSize) {
            $chunk = substr($binary, $i, $chunkSize);
            $row = str_pad(dechex($i), $leftPad, '0', STR_PAD_LEFT) . ' | ';

            $thisChunk = str_split($chunk, 1);
            foreach ($thisChunk as $v) {
                $row .= str_pad(strtoupper(dechex(ord($v))), 2, '0', STR_PAD_LEFT) . ' ';

                if (ctype_print($v)) {
                    $rawView .= $v;
                } else {
                    $rawView .= '.';
                }

                if ($visualSplit != false) {
                    $innerIterator++;
                    if (($innerIterator) % $visualSplit == 0) {
                        $row .= "| ";
                    }
                }
            }

            if (strlen($row) > $maxRowLen) {
                $maxRowLen = strlen($row);
            }
            $outputFileHandle .= str_pad($row, $maxRowLen, ' ') . $rawView . $eolString;

            $rawView = '';
        }
        return $outputFileHandle;
    }
}