<?php
// task_D.php

function solve(string $input): string {
    // 1. Удаляем все комментарии /* ... */ (Правило 1)
    $css = preg_replace('#/\*.*?\*/#s', '', $input);

    // 2. Ищем все селекторы и их блоки объявлений
    preg_match_all('/([^{}]+)\s*\{(.*?)\}/s', $css, $matches, PREG_SET_ORDER);

    $minifiedRules = [];

    foreach ($matches as $match) {
        $selector = trim($match[1]);
        $declarations = trim($match[2]);

        // Очищаем селектор от лишних пробелов, в том числе вокруг запятых и комбинаторов (Правило 3)
        $selector = preg_replace('/\s+/', ' ', $selector);
        $selector = preg_replace('/\s*([,>+~])\s*/', '$1', $selector);

        // Разделяем объявления внутри фигурных скобок по точке с запятой
        $decls = explode(';', $declarations);
        $parsedDecls = [];

        foreach ($decls as $decl) {
            $decl = trim($decl);
            if ($decl === '') {
                continue;
            }

            $parts = explode(':', $decl, 2);
            if (count($parts) < 2) {
                continue;
            }

            $prop = strtolower(trim($parts[0]));
            $val = trim($parts[1]);

            // Очищаем значение от лишних пробелов (Правило 3)
            $val = preg_replace('/\s+/', ' ', $val);

            // Удаляем единицы измерения после нулей (0px, 0em, 0% и т.д. => 0) (Правило 4)
            $val = preg_replace('/\b0(px|em|rem|%|pt|pc|in|cm|mm|ex|ch|vh|vw|vmin|vmax)\b/i', '0', $val);

            // Сокращаем шестнадцатеричные цвета и заменяем на ключевые слова (Правило 5)
            $val = preg_replace_callback('/#([a-fA-F0-9]{3,6})\b/', function ($m) {
                $hex = strtoupper($m[1]);
                
                // Если цвет 6-значный, пробуем сократить до 3-значного (Правило 5а)
                if (strlen($hex) === 6) {
                    if ($hex[0] === $hex[1] && $hex[2] === $hex[3] && $hex[4] === $hex[5]) {
                        $hex = $hex[0] . $hex[2] . $hex[4];
                    }
                }

                // Карта соответствия цветов согласно Правилу 5b
                $colorMap = [
                    'CD853F' => 'peru',
                    'FFC0CB' => 'pink',
                    'DDA0DD' => 'plum',
                    'F00'    => 'red',
                    'FF0000' => 'red',
                    'FFFAFA' => 'snow',
                    'D2B48C' => 'tan'
                ];

                if (isset($colorMap[$hex])) {
                    return $colorMap[$hex];
                }

                return '#' . $hex;
            }, $val);

            $parsedDecls[$prop] = $val;
        }

        // Сокращаем margin (Правило 6)
        if (isset($parsedDecls['margin-top']) && isset($parsedDecls['margin-right']) && 
            isset($parsedDecls['margin-bottom']) && isset($parsedDecls['margin-left'])) {
            
            $t = $parsedDecls['margin-top'];
            $r = $parsedDecls['margin-right'];
            $b = $parsedDecls['margin-bottom'];
            $l = $parsedDecls['margin-left'];

            if ($t === $r && $r === $b && $b === $l) {
                $marginVal = $t;
            } elseif ($r === $l && $t === $b) {
                $marginVal = "$t $r";
            } elseif ($r === $l) {
                $marginVal = "$t $r $b";
            } else {
                $marginVal = "$t $r $b $l";
            }

            $parsedDecls['margin'] = $marginVal;
            unset($parsedDecls['margin-top'], $parsedDecls['margin-right'], $parsedDecls['margin-bottom'], $parsedDecls['margin-left']);
        }

        // Сокращаем padding (Правило 6)
        if (isset($parsedDecls['padding-top']) && isset($parsedDecls['padding-right']) && 
            isset($parsedDecls['padding-bottom']) && isset($parsedDecls['padding-left'])) {
            
            $t = $parsedDecls['padding-top'];
            $r = $parsedDecls['padding-right'];
            $b = $parsedDecls['padding-bottom'];
            $l = $parsedDecls['padding-left'];

            if ($t === $r && $r === $b && $b === $l) {
                $paddingVal = $t;
            } elseif ($r === $l && $t === $b) {
                $paddingVal = "$t $r";
            } elseif ($r === $l) {
                $paddingVal = "$t $r $b";
            } else {
                $paddingVal = "$t $r $b $l";
            }

            $parsedDecls['padding'] = $paddingVal;
            unset($parsedDecls['padding-top'], $parsedDecls['padding-right'], $parsedDecls['padding-bottom'], $parsedDecls['padding-left']);
        }

        // Собираем объявления обратно в строковый вид (Правило 3 — убираем лишние ';' в конце)
        $declStrings = [];
        foreach ($parsedDecls as $prop => $val) {
            $declStrings[] = "{$prop}:{$val}";
        }
        $declBlock = implode(';', $declStrings);

        // Игнорируем пустые стили (Правило 2)
        if ($declBlock !== '') {
            $minifiedRules[] = "{$selector}{{$declBlock}}";
        }
    }

    return implode('', $minifiedRules);
}

// Прямой запуск
$inputData = file_get_contents('php://stdin');
$result = solve($inputData);
if ($result !== '') {
    echo $result . "\n";
}