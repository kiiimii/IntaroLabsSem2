#!/bin/sh

TEST_DIR="../data/A"
PHP_SCRIPT="task1.php"

echo "--- Запуск тестов для $PHP_SCRIPT ---"

count=0
passed=0

for dat_file in "$TEST_DIR"/*.dat; do
    [ -e "$dat_file" ] || continue

    count=$((count + 1))
    test_name=$(basename "$dat_file" .dat)
    ans_file="$TEST_DIR/$test_name.ans"

    echo -e "\e[34m[Тест $test_name]\e[0m"

    if [ ! -f "$ans_file" ]; then
        echo -e "   \e[33mПропуск: Файл ответа $test_name.ans не найден\e[0m"
        continue
    fi

    result=$(php "$PHP_SCRIPT" < "$dat_file" | xargs)
    
    expected=$(cat "$ans_file" | xargs)

    if [ "$result" = "$expected" ]; then
        echo -e "   \e[32mРезультат: [OK]\e[0m"
        echo -e "   Ожидалось: $expected"
        echo -e "   Получено:  $result"
        passed=$((passed + 1))
    else
        echo -e "   \e[31mРезультат: [FAIL]\e[0m"
        echo -e "   \e[31mОжидалось: $expected\e[0m"
        echo -e "   \e[31mПолучено:  $result\e[0m"
    fi
    echo "---------------------------"
done

echo -e "\nИтог: \e[32mПройдено: $passed\e[0m / \e[31mВсего: $count\e[0m"