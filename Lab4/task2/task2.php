<?php
// solution.php

$input_file = __DIR__ . '/input.txt';
$output_file = __DIR__ . '/output.txt';

if (!file_exists($input_file)) {
    exit("Файл входных данных не найден.\n");
}

$input = file_get_contents($input_file);
$lines = preg_split('/\r\n|\r|\n/', trim($input));

if (empty($lines) || trim($lines[0]) === '') {
    file_put_contents($output_file, '');
    exit;
}

// Читаем значения N (количество IP) и K 
$first_line = preg_split('/\s+/', trim($lines[0]));
if (count($first_line) < 2) {
    file_put_contents($output_file, '');
    exit;
}

$n = (int)$first_line[0];
$k = (int)$first_line[1];

// Преобразуем каждый IP-адрес в 32-символьную двоичную строку
$ip_bins = [];
for ($i = 1; $i <= $n; $i++) {
    if (isset($lines[$i])) {
        $ip = trim($lines[$i]);
        if ($ip !== '') {
            $octets = explode('.', $ip);
            if (count($octets) === 4) {
                $ip_bins[] = sprintf(
                    '%08b%08b%08b%08b',
                    (int)$octets[0],
                    (int)$octets[1],
                    (int)$octets[2],
                    (int)$octets[3]
                );
            }
        }
    }
}

/**
 * Подсчитывает количество уникальных подсетей при заданной длине маски p.
 */
function get_unique_count($ip_bins, $p) {
    $unique = [];
    foreach ($ip_bins as $bin) {
        $subnet = substr($bin, 0, $p);
        $unique[$subnet] = true;
    }
    return count($unique);
}

// Ищем минимальную длину маски p, которая дает ровно K уникальных сетей.
// Поиск идет от 0 до 32, так как чем меньше p, тем крупнее подсети
$ans_p = -1;
for ($p = 0; $p <= 32; $p++) {
    if (get_unique_count($ip_bins, $p) === $k) {
        $ans_p = $p;
        break;
    }
}

if ($ans_p !== -1) {
    // Восстанавливаем строковый вид маски из двоичной строки
    $bin_mask = str_repeat('1', $ans_p) . str_repeat('0', 32 - $ans_p);
    $octets = [];
    for ($i = 0; $i < 4; $i++) {
        $octets[] = bindec(substr($bin_mask, $i * 8, 8));
    }
    $mask_str = implode('.', $octets);
    file_put_contents($output_file, $mask_str);
} else {
    file_put_contents($output_file, '');
}