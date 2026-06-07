<?php

$str = "2aaa'3'bbb'4'";
echo "Исходная строка: " . $str . "\n";

// Регулярное выражение ищет числа (\d+), находящиеся внутри одинарных кавычек
$pattern = "/'(\d+)'/";

$result = preg_replace_callback($pattern, function ($matches) {
    // $matches[1] содержит числовую подстроку без кавычек
    $doubled = $matches[1] * 2;
    return "'" . $doubled . "'";
}, $str);

echo "Результат:       " . $result . "\n";