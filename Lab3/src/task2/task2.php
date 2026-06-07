<?php

$sectionsFile = '/app/data/B/002_sections.xml';
$productsFile = '/app/data/B/002_products.xml';
$outputFile   = 'output.xml';

if (!file_exists($sectionsFile)) {
    exit("Файл $sectionsFile не найден\n");
}

$sectionsXml = simplexml_load_file($sectionsFile);
$productsXml = file_exists($productsFile) ? simplexml_load_file($productsFile) : null;

$sectionsMap = [];

if ($sectionsXml && $sectionsXml->Раздел) {
    foreach ($sectionsXml->Раздел as $sec) {
        $id = (string)$sec->Ид;
        $sectionsMap[$id] = [
            'Ид' => $id,
            'Наименование' => (string)$sec->Наименование,
            'Товары' => []
        ];
    }
}

if ($productsXml && $productsXml->Товар) {
    foreach ($productsXml->Товар as $prod) {
        $prodId   = (string)$prod->Ид;
        $prodName = (string)$prod->Наименование;
        $prodArt  = (string)$prod->Артикул;

        if ($prod->Разделы && $prod->Разделы->ИдРаздела) {
            foreach ($prod->Разделы->ИдРаздела as $secIdNode) {
                $secId = (string)$secIdNode;
                if (isset($sectionsMap[$secId])) {
                    $sectionsMap[$secId]['Товары'][] = [
                        'Ид'           => $prodId,
                        'Наименование' => $prodName,
                        'Артикул'      => $prodArt
                    ];
                }
            }
        }
    }
}

$doc = new DOMDocument('1.0', 'UTF-8');
$doc->formatOutput = true; 

$root = $doc->createElement('ЭлементыКаталога');
$doc->appendChild($root);

$sectionsNode = $doc->createElement('Разделы');
$root->appendChild($sectionsNode);

foreach ($sectionsMap as $secData) {
    $secNode = $doc->createElement('Раздел');
    $sectionsNode->appendChild($secNode);

    $addNode = function($parent, $name, $value) use ($doc) {
        $node = $doc->createElement($name);
        $node->appendChild($doc->createTextNode($value));
        $parent->appendChild($node);
    };

    $addNode($secNode, 'Ид', $secData['Ид']);
    $addNode($secNode, 'Наименование', $secData['Наименование']);

    $prodsNode = $doc->createElement('Товары');
    $secNode->appendChild($prodsNode);

    foreach ($secData['Товары'] as $pData) {
        $pNode = $doc->createElement('Товар');
        $prodsNode->appendChild($pNode);

        $addNode($pNode, 'Ид', $pData['Ид']);
        $addNode($pNode, 'Наименование', $pData['Наименование']);
        $addNode($pNode, 'Артикул', $pData['Артикул']);
    }
}

$doc->save($outputFile);
echo "Успешно сгенерирован $outputFile\n";