<?php

$input_file = __DIR__ . '/input.txt';
$output_file = __DIR__ . '/output.txt';

if (!file_exists($input_file)) {
    exit("Файл входных данных не найден.\n");
}

$input = trim(file_get_contents($input_file));
if ($input === '') {
    file_put_contents($output_file, '');
    exit;
}

$valid_options = [];

$protocols = ['https', 'http'];

foreach ($protocols as $p) {
    $p_len = strlen($p);
    // Проверяем, начинается ли входная строка с этого протокола
    if (substr($input, 0, $p_len) === $p) {
        $remainder = substr($input, $p_len);
        
        // Возможные доменные зоны
        $zones = ['ru', 'com'];
        foreach ($zones as $z) {
            $z_len = strlen($z);
            $offset = 0;
            
            // Ищем все вхождения зоны в оставшейся части строки
            while (($pos = strpos($remainder, $z, $offset)) !== false) {
                $d = substr($remainder, 0, $pos);
                $c = substr($remainder, $pos + $z_len);
                
                // Валидация домена: непустая строка из строчных латинских букв, цифр и дефиса
                if ($d !== '' && preg_match('/^[a-z0-9-]+$/', $d)) {
                    // Валидация контекста: пустой, либо непустая строка из строчных латинских букв
                    if ($c === '' || preg_match('/^[a-z]+$/', $c)) {
                        $valid_options[] = [
                            'p' => $p,
                            'd' => $d,
                            'z' => $z,
                            'c' => $c
                        ];
                    }
                }
                
                $offset = $pos + 1;
            }
        }
    }
}

if (empty($valid_options)) {
    file_put_contents($output_file, '');
    exit;
}

// Сортировка вариантов по приоритету:
// 1. Наибольшая длина протокола (https предпочтительнее http)
// 2. Наименьшая длина домена (хоста)
usort($valid_options, function($a, $b) {
    $len_a_p = strlen($a['p']);
    $len_b_p = strlen($b['p']);
    if ($len_a_p !== $len_b_p) {
        return $len_b_p - $len_a_p; // по убыванию длины протокола
    }
    
    $len_a_d = strlen($a['d']);
    $len_b_d = strlen($b['d']);
    return $len_a_d - $len_b_d; // по возрастанию длины домена
});

$best = $valid_options[0];

// Сборка итогового URL (не выводим слеш на конце, если контекст отсутствует)
if ($best['c'] === '') {
    $output_url = $best['p'] . '://' . $best['d'] . '.' . $best['z'];
} else {
    $output_url = $best['p'] . '://' . $best['d'] . '.' . $best['z'] . '/' . $best['c'];
}

file_put_contents($output_file, $output_url);