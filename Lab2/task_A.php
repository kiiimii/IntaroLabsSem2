<?php
// task_A.php

function solve(string $input): string {
    $lines = preg_split('/\r\n|\r|\n/', trim($input));
    if (empty($lines) || (count($lines) === 1 && trim($lines[0]) === '')) {
        return '';
    }

    $banners = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        // Разделяем по любым пробельным символам 
        $parts = preg_split('/\s+/', $line);
        
        if (count($parts) < 3) {
            continue;
        }

        $id = $parts[0];
        // Восстанавливаем строку даты и времени, соединяя 2-ю и 3-ю части через пробел
        $timeStr = $parts[1] . ' ' . $parts[2];

        $dateTime = DateTime::createFromFormat('d.m.Y H:i:s', $timeStr);
        if (!$dateTime) {
            continue; 
        }
        $timestamp = $dateTime->getTimestamp();

        if (!isset($banners[$id])) {
            $banners[$id] = [
                'count' => 1,
                'last_time_str' => $timeStr,
                'timestamp' => $timestamp
            ];
        } else {
            $banners[$id]['count']++;
            if ($timestamp > $banners[$id]['timestamp']) {
                $banners[$id]['last_time_str'] = $timeStr;
                $banners[$id]['timestamp'] = $timestamp;
            }
        }
    }

    $output = [];
    foreach ($banners as $id => $data) {
        $output[] = "{$data['count']} {$id} {$data['last_time_str']}";
    }

    return implode("\n", $output);
}

$inputData = file_get_contents('php://stdin');
$result = solve($inputData);
echo $result . "\n";