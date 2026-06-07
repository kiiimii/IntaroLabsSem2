<?php
// task_C.php

function solve(string $input): string {
    // Гарантируем использование точки в качестве десятичного разделителя во всех локалях
    setlocale(LC_NUMERIC, 'C');

    $lines = preg_split('/\r\n|\r|\n/', trim($input));
    if (empty($lines) || (count($lines) === 1 && trim($lines[0]) === '')) {
        return '';
    }

    $banners = [];
    $totalWeight = 0;
    $order = []; // Массив для сохранения исходного порядка ввода

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $parts = preg_split('/\s+/', $line);
        if (count($parts) < 2) {
            continue;
        }

        $id = $parts[0];
        $weight = (int)$parts[1];

        $banners[$id] = $weight;
        $totalWeight += $weight;
        $order[] = $id;
    }

    if ($totalWeight === 0) {
        return '';
    }

    // Построение массива накопленных весов (CDF)
    $cdf = [];
    $keys = [];
    $currentSum = 0;
    foreach ($banners as $id => $weight) {
        $currentSum += $weight;
        $cdf[] = $currentSum;
        $keys[] = $id;
    }

    // Инициализируем массив счетчиков для каждого баннера нулями
    $counts = array_fill_keys($order, 0);
    $numTrials = 1000000; // 10^6 симуляций
    $numKeys = count($cdf);

    // Моделирование 10^6 показов с бинарным поиском (встроен прямо в цикл для максимального ускорения)
    for ($i = 0; $i < $numTrials; $i++) {
        $r = mt_rand(1, $totalWeight);
        
        $low = 0;
        $high = $numKeys - 1;
        while ($low < $high) {
            $mid = ($low + $high) >> 1;
            if ($cdf[$mid] < $r) {
                $low = $mid + 1;
            } else {
                $high = $mid;
            }
        }
        $counts[$keys[$low]]++;
    }

    // Формируем результат строго в порядке ввода баннеров
    $output = [];
    foreach ($order as $id) {
        $share = $counts[$id] / $numTrials;
        // Выводим с точностью до 6 знаков после запятой
        $formattedShare = sprintf("%.6f", $share);
        $output[] = "{$id} {$formattedShare}";
    }

    return implode("\n", $output);
}

$inputData = file_get_contents('php://stdin');
$result = solve($inputData);
echo $result . "\n";