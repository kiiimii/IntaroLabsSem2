<?php


function solve(string $input): string {
    // Разделяем входные данные по строкам
    $lines = preg_split('/\r\n|\r|\n/', trim($input));
    if (empty($lines) || trim($lines[0]) === '') {
        return '0';
    }

    $lineIndex = 0;

    $n = (int)trim($lines[$lineIndex++]);

    $bets = [];
    for ($i = 0; $i < $n; $i++) {
        if (!isset($lines[$lineIndex])) {
            break;
        }
        $parts = preg_split('/\s+/', trim($lines[$lineIndex++]));
        if (count($parts) < 3) {
            continue;
        }
        
        $bets[] = [
            'game_id'    => $parts[0],
            'amount'     => (float)$parts[1],
            'prediction' => $parts[2]
        ];
    }

    if (!isset($lines[$lineIndex])) {
        return '0';
    }
    $m = (int)trim($lines[$lineIndex++]);

    $games = [];
    for ($j = 0; $j < $m; $j++) {
        if (!isset($lines[$lineIndex])) {
            break;
        }
        $parts = preg_split('/\s+/', trim($lines[$lineIndex++]));
        if (count($parts) < 5) {
            continue;
        }

        $gameId = $parts[0];
        $games[$gameId] = [
            'L'       => (float)$parts[1],
            'R'       => (float)$parts[2],
            'D'       => (float)$parts[3],
            'outcome' => $parts[4]
        ];
    }

    // Рассчитываем итоговый баланс
    $totalBetsAmount = 0;
    $totalWinnings = 0;

    foreach ($bets as $bet) {
        $gameId = $bet['game_id'];
        $amount = $bet['amount'];
        $prediction = $bet['prediction'];

        $totalBetsAmount += $amount;

        // Если игра с таким ID найдена в списке прошедших игр
        if (isset($games[$gameId])) {
            $game = $games[$gameId];
            
            // Если игрок угадал исход
            if ($prediction === $game['outcome']) {
                $coeff = $game[$prediction];
                $totalWinnings += $amount * $coeff;
            }
        }
    }

    $netBalance = $totalWinnings - $totalBetsAmount;

    return (string)$netBalance;
}

if (php_sapi_name() === 'cli' && realpath($argv[0]) === __FILE__) {
    $inputFile = 'input.txt';
    $outputFile = 'output.txt';

    if (file_exists($inputFile) && is_readable($inputFile)) {
        $inputData = file_get_contents($inputFile);
    } else {
        $inputData = file_get_contents('php://stdin');
    }

    $result = solve($inputData);

    file_put_contents($outputFile, $result);

    echo $result . "\n";
}

$inputData = file_get_contents('php://stdin');
