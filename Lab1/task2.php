<?php

$text = "Текст новости. Ознакомьтесь с законопроектом по ссылке: " .
        "http://asozd.duma.gov.ru/main.nsf/(Spravka)?OpenAgent&RN=123456-7&123. " .
        "Также доступен другой документ по защищенному протоколу: " .
        "https://asozd.duma.gov.ru/main.nsf/(Spravka)?OpenAgent&RN=987654-3&999.";

echo "Исходный текст \n" . $text . "\n\n";

/*
 * Регулярное выражение для поиска старых ссылок:
 * #https?:// - протокол http или https
 * asozd\.duma\.gov\.ru/main\.nsf/ - домен и путь, экранируем точки
 * \(Spravka\) - экранируем скобки, так как они являются спецсимволами группировки
 * \?OpenAgent - экранируем вопросительный знак
 * &RN=([0-9-]+) - группа захвата №1: номер законопроекта (цифры и дефисы)
 * &\d+ - знак амперсанда и последующее целое число в конце ссылки
 * Флаг 'i' в конце делает регулярное выражение регистронезависимым.
 */
$pattern = '#https?://asozd\.duma\.gov\.ru/main\.nsf/\(Spravka\)\?OpenAgent&RN=([0-9-]+)&\d+#i';

$replacement = 'http://sozd.parlament.gov.ru/bill/$1';

$result = preg_replace($pattern, $replacement, $text);

echo " Результат после замены \n" . $result . "\n";