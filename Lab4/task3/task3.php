<?php
// solution.php

$input_file = __DIR__ . '/input.txt';
$output_file = __DIR__ . '/output.txt';

if (!file_exists($input_file)) {
    exit("Файл входных данных не найден.\n");
}

$input = file_get_contents($input_file);
if (trim($input) === '') {
    file_put_contents($output_file, '');
    exit;
}

$query = json_decode($input, true);
if ($query === null) {
    file_put_contents($output_file, '');
    exit;
}

/**
 * Переводит одно атомарное условие (ключ-значение) в SQL-представление.
 */
function translate_condition($key, $val) {
    $op = '';
    $field = '';
    
    // Выделяем оператор и имя поля
    if (strpos($key, '<=') === 0) {
        $op = '<=';
        $field = substr($key, 2);
    } elseif (strpos($key, '>=') === 0) {
        $op = '>=';
        $field = substr($key, 2);
    } elseif (strpos($key, '<') === 0) {
        $op = '<';
        $field = substr($key, 1);
    } elseif (strpos($key, '>') === 0) {
        $op = '>';
        $field = substr($key, 1);
    } elseif (strpos($key, '=') === 0) {
        $op = '=';
        $field = substr($key, 1);
    } elseif (strpos($key, '!') === 0) {
        $op = '!';
        $field = substr($key, 1);
    } else {
        $op = '';
        $field = $key;
    }

    // Определяем строковое представление значения и его тип
    $val_repr = '';
    $type = '';
    if (is_string($val)) {
        $val_repr = "'" . $val . "'";
        $type = 'string';
    } elseif (is_int($val) || is_float($val)) {
        $val_repr = $val;
        $type = 'number';
    } elseif (is_bool($val)) {
        $val_repr = $val ? 'true' : 'false';
        $type = 'bool';
    } elseif (is_null($val)) {
        $val_repr = 'null';
        $type = 'null';
    }

    // Трансляция согласно правилу 4.d
    if ($op === '<' || $op === '<=' || $op === '>' || $op === '>=') {
        return "{$field} {$op} {$val_repr}";
    } elseif ($op === '') {
        if ($type === 'string') {
            return "{$field} like {$val_repr}";
        } elseif ($type === 'number') {
            return "{$field} = {$val_repr}";
        } elseif ($type === 'bool') {
            return "{$field} is {$val_repr}";
        } elseif ($type === 'null') {
            return "{$field} is null";
        }
    } elseif ($op === '=') {
        if ($type === 'string' || $type === 'number') {
            return "{$field} = {$val_repr}";
        } elseif ($type === 'bool') {
            return "{$field} is {$val_repr}";
        } elseif ($type === 'null') {
            return "{$field} is null";
        }
    } elseif ($op === '!') {
        if ($type === 'string' || $type === 'number') {
            return "{$field} != {$val_repr}";
        } elseif ($type === 'bool') {
            return "{$field} is not {$val_repr}";
        } elseif ($type === 'null') {
            return "{$field} is not null";
        }
    }

    return '';
}

/**
 * Рекурсивно строит дерево условий block WHERE.
 */
function build_where($obj, $connector = 'and') {
    $parts = [];
    foreach ($obj as $key => $val) {
        // Проверяем, является ли ключ вложенным блоком and_S / or_S или and / or
        if (strpos($key, 'and') === 0) {
            $sub = build_where($val, 'and');
            if ($sub !== '') {
                $parts[] = "({$sub})";
            }
        } elseif (strpos($key, 'or') === 0) {
            $sub = build_where($val, 'or');
            if ($sub !== '') {
                $parts[] = "({$sub})";
            }
        } else {
            // Обычное атомарное условие
            $cond = translate_condition($key, $val);
            if ($cond !== '') {
                $parts[] = $cond;
            }
        }
    }
    return implode(" {$connector} ", $parts);
}

$sql = [];

// 1. SELECT (необязательное, по умолчанию *)
$select_fields = '*';
if (isset($query['select']) && is_array($query['select']) && !empty($query['select'])) {
    $select_fields = implode(', ', $query['select']);
}
$sql[] = "select " . $select_fields;

// 2. FROM (обязательное)
if (isset($query['from']) && trim($query['from']) !== '') {
    $sql[] = "from " . trim($query['from']);
} else {
    file_put_contents($output_file, '');
    exit;
}

// 3. WHERE (необязательное)
if (isset($query['where']) && is_array($query['where']) && !empty($query['where'])) {
    $where_clause = build_where($query['where'], 'and');
    if ($where_clause !== '') {
        $sql[] = "where " . $where_clause;
    }
}

// 4. ORDER (необязательное)
if (isset($query['order']) && is_array($query['order']) && !empty($query['order'])) {
    $field = key($query['order']);
    $direction = current($query['order']);
    $sql[] = "order by " . $field . " " . $direction;
}

// 5. LIMIT (необязательное)
if (isset($query['limit']) && $query['limit'] !== '') {
    $sql[] = "limit " . (int)$query['limit'];
}

// Сборка запроса и добавление точки с запятой в самом конце
$sql_query = implode("\n", $sql) . ';';

file_put_contents($output_file, $sql_query);