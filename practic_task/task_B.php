<?php


function solve(string $input): string {
    $lines = preg_split('/\r\n|\r|\n/', trim($input));
    if (empty($lines) || (count($lines) === 1 && trim($lines[0]) === '')) {
        return '';
    }

    $results = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        // Если в строке есть сокращение "::"
        if (strpos($line, '::') !== false) {
            $parts = explode('::', $line);
            
            // Получаем блоки слева и справа от "::"
            $left = ($parts[0] !== '') ? explode(':', $parts[0]) : [];
            $right = (isset($parts[1]) && $parts[1] !== '') ? explode(':', $parts[1]) : [];

            // Вычисляем, сколько нулевых блоков скрыто за "::" (всего должно быть 8 блоков)
            $missingCount = 8 - (count($left) + count($right));
            $middle = array_fill(0, $missingCount, '0');

            // Объединяем все блоки обратно в один массив
            $blocks = array_merge($left, $middle, $right);
        } else {
            // Если "::" нет, просто делим по двоеточию
            $blocks = explode(':', $line);
        }

        // Дополняем каждый блок ведущими нулями до 4 символов
        $paddedBlocks = array_map(function ($block) {
            return str_pad($block, 4, '0', STR_PAD_LEFT);
        }, $blocks);

        $results[] = implode(':', $paddedBlocks);
    }

    return implode("\n", $results);
}


$inputData = file_get_contents('php://stdin');
$result = solve($inputData);
echo $result . "\n";