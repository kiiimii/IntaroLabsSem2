<?php

$inputFile  = '/app/data/D/002.html';
$outputFile = 'output2.html';

if (!file_exists($inputFile)) {
    exit("Файл $inputFile не найден\n");
}

$html = file_get_contents($inputFile);

$pattern = '/(<!--.*?-->|<script\b[^>]*>.*?<\/script>|<(?:pre|code|textarea|style)\b[^>]*>.*?<\/(?:pre|code|textarea|style)>|<(?:"[^"]*"|\'[^\']*\'|[^\'">])+>)/is';

$tokens = preg_split($pattern, $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

$output = '';
$scriptsToMove = [];

foreach ($tokens as $token) {
    if (preg_match('/^<!--(.*?)-->$/s', $token, $match)) {
        if (strpos(ltrim($match[1]), 'skip-delete') === 0) {
            $output .= $token;
        }
    } 
    elseif (preg_match('/^<script\b/i', $token)) {
        if (preg_match('/\bdata-skip-moving\s*=\s*(["\']?)true\1/i', $token)) {
            $output .= $token;
        } else {
            $scriptsToMove[] = $token;
        }
    } 
    elseif (preg_match('/^<(pre|code|textarea|style)\b/i', $token)) {
        $output .= $token;
    } 
    elseif (preg_match('/^</', $token)) {
        $token = str_replace(["\r", "\n", "\t"], '', $token);
        $token = preg_replace('/ {2,}/', ' ', $token);
        $output .= $token;
    } 
    else {
        if (trim($token) === '') {
            continue;
        } else {
            $token = str_replace(["\r", "\n", "\t"], '', $token);
            $token = preg_replace('/ {2,}/', ' ', $token);
            $output .= $token;
        }
    }
}

if (!empty($scriptsToMove)) {
    $scriptsStr = implode('', $scriptsToMove);
    
    if (stripos($output, '</body>') !== false) {
        $output = preg_replace('/<\/body>/i', $scriptsStr . '</body>', $output);
    } else {
        $output .= $scriptsStr;
    }
}

file_put_contents($outputFile, $output);
echo "Оптимизация завершена. Результат сохранен в $outputFile\n";