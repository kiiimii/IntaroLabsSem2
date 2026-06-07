Сбока контейнера docker build -t php-tasks .
Запуск файла docker run --rm -v "$(pwd)":/app php-tasks php task(Номер задачи).php
task1 и task1 запускаются вручную, task_A,B,C запускаются через test_runner.sh