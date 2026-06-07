<?php


function validate(string $value, string $rule, array $params = []): bool {
    switch ($rule) {
        case 'S': 
            $n = isset($params[0]) ? (int)$params[0] : 0;
            $m = isset($params[1]) ? (int)$params[1] : PHP_INT_MAX;
            
            $len = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
            return $len >= $n && $len <= $m;

        case 'N': 
            $n = isset($params[0]) ? (float)$params[0] : -INF;
            $m = isset($params[1]) ? (float)$params[1] : INF;
            
            if (!preg_match('/^[+-]?\d+$/', $value)) {
                return false;
            }
            
            $num = (float)$value;
            return $num >= $n && $num <= $m;

        case 'P': // Номер телефона по маске +7 (999) 999-99-99
            $pattern = '/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/';
            return preg_match($pattern, $value) === 1;

        case 'D': // Дата и время d.m.Y H:i 
            // 1. Проверяем структуру: 1-2 цифры для дней, месяцев, часов, минут; строго 4 для года.
            if (!preg_match('/^\d{1,2}\.\d{1,2}\.\d{4} \d{1,2}:\d{1,2}$/', $value)) {
                return false;
            }
            
            // 2. Распознаем дату с помощью гибких шаблонов PHP
            $d = DateTime::createFromFormat('d.m.Y H:i', $value);
            if (!$d) {
                $d = DateTime::createFromFormat('j.n.Y G:i', $value);
            }
            
            if (!$d) {
                return false;
            }
            
            // 3. Убеждаемся, что не было логического переполнения (например, 30 февраля)
            $errors = DateTime::getLastErrors();
            
            // Если getLastErrors вернул false, значит, ошибок и предупреждений нет вообще
            if ($errors === false) {
                return true;
            }
            
            return $errors['warning_count'] === 0 && $errors['error_count'] === 0;

        case 'E': // Адрес электронной почты
            $pattern = '/^[A-Za-z0-9][A-Za-z0-9_]{3,29}@[A-Za-z]{2,30}\.[a-z]{2,10}$/';
            return preg_match($pattern, $value) === 1;

        default:
            return false;
    }
}


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

        if (preg_match('/^<(.*?)>\s*([A-Z])\s*(.*)$/', $line, $matches)) {
            $value = $matches[1];
            $rule = $matches[2];
            $paramsStr = trim($matches[3]);
            
            $params = [];
            if (!empty($paramsStr)) {
                $params = preg_split('/\s+/', $paramsStr);
                $params = array_map(function($p) {
                    if (is_numeric($p)) return (float)$p;
                    return $p;
                }, $params);
            }

            if (validate($value, $rule, $params)) {
                $results[] = 'OK';
            } else {
                $results[] = 'FAIL';
            }
        } else {
            $results[] = 'FAIL'; 
        }
    }

    return implode("\n", $results);
}


$inputData = file_get_contents('php://stdin');
$result = solve($inputData);
echo $result . "\n";