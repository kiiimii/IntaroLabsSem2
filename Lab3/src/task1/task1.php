<?php

$line = fgets(STDIN);
if (!$line) exit;
$n = (int)trim($line);

for ($i = 0; $i < $n; $i++) {
    $data = explode(' ', trim(fgets(STDIN)));
    if (count($data) < 4) continue;

    $departureTime = $data[0];
    $departureTZ   = $data[1];
    $arrivalTime   = $data[2];
    $arrivalTZ     = $data[3];

    $departure = DateTimeImmutable::createFromFormat(
        'd.m.Y_H:i:s P', 
        $departureTime . ' ' . sprintf('%+03d00', $departureTZ)
    );

    $arrival = DateTimeImmutable::createFromFormat(
        'd.m.Y_H:i:s P', 
        $arrivalTime . ' ' . sprintf('%+03d00', $arrivalTZ)
    );

    $diff = $arrival->getTimestamp() - $departure->getTimestamp();

    echo $diff . PHP_EOL;
}