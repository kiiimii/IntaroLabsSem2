#!/bin/sh
# test_runner.sh

for f in tests/*/*.dat; do
    [ -e "$f" ] || continue
    
    task=$(basename "$(dirname "$f")")
    script="task_${task}.php"
    
    if [ ! -f "$script" ]; then
        echo "[-] $f: Пропущен (нет скрипта $script)"
        continue
    fi
    
    ans="${f%.dat}.ans"
    
    out=$(php "$script" < "$f" | tr -d '\r\n')
    
    if [ "$task" = "C" ]; then
        echo "[+] [C] $f: ≈ $out"
    else
        exp=$(cat "$ans" 2>/dev/null | tr -d '\r\n')
        if [ ! -f "$ans" ]; then
            echo "[-] $f: Пропущен (нет файла .ans)"
        elif [ "$out" = "$exp" ]; then
            echo "[+] [$task] $f: OK"
        else
            echo "[X] [$task] $f: ОШИБКА (Ожидалось: '$exp', Получено: '$out')"
        fi
    fi
done