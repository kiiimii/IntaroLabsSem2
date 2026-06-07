<?php
// submit.php

// Подключаем автозагрузчик Composer
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Некорректный метод запроса.']);
    exit;
}

$fio = trim($_POST['fio'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$comment = trim($_POST['comment'] ?? '');

// 1. Серверная валидация
if (empty($fio) || empty($email) || empty($phone) || empty($comment)) {
    echo json_encode(['status' => 'error', 'message' => 'Все поля обязательны для заполнения.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Некорректный формат E-mail.']);
    exit;
}

// 2. Подключение к MySQL
$host = getenv('DB_HOST') ?: 'db';
$dbName = getenv('DB_NAME') ?: 'feedback_db';
$user = getenv('DB_USER') ?: 'user';
$password = getenv('DB_PASSWORD') ?: 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE TABLE IF NOT EXISTS requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fio VARCHAR(255),
        email VARCHAR(255),
        phone VARCHAR(50),
        comment TEXT,
        created_at DATETIME
    )");
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка подключения к БД: ' . $e->getMessage()]);
    exit;
}

// 3. Проверка на дубликат в течение часа
try {
    $stmt = $pdo->prepare("SELECT created_at FROM requests WHERE email = :email ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([':email' => $email]);
    $lastRequest = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($lastRequest) {
        $lastTime = strtotime($lastRequest['created_at']);
        $timePassed = time() - $lastTime;

        if ($timePassed < 3600) {
            $timeLeft = 3600 - $timePassed;
            $minutes = floor($timeLeft / 60);
            $seconds = $timeLeft % 60;

            echo json_encode([
                'status' => 'error',
                'message' => "Повторная заявка невозможна. Пожалуйста, подождите еще {$minutes} мин. {$seconds} сек."
            ]);
            exit;
        }
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка проверки лимитов времени.']);
    exit;
}

// 4. Парсинг ФИО
$fioParts = preg_split('/\s+/', $fio);
$lastName = $fioParts[0] ?? '';
$firstName = $fioParts[1] ?? '';
$middleName = $fioParts[2] ?? '';

// 5. Запись в MySQL
$now = date('Y-m-d H:i:s');
try {
    $insertStmt = $pdo->prepare("INSERT INTO requests (fio, email, phone, comment, created_at) VALUES (:fio, :email, :phone, :comment, :created_at)");
    $insertStmt->execute([
        ':fio' => $fio,
        ':email' => $email,
        ':phone' => $phone,
        ':comment' => $comment,
        ':created_at' => $now
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка записи в базу данных.']);
    exit;
}

// 6. Отправка письма менеджеру через PHPMailer
$mail = new PHPMailer(true);

try {
    // Настройки подключения к SMTP-серверу
    $mail->isSMTP();
    $mail->Host       = 'mailhog';
    $mail->Port       = (int)(getenv('SMTP_PORT') ?: 1025);
    $mail->SMTPAuth   = false; 
    $mail->CharSet    = 'UTF-8';

    // Получатели
    $mail->setFrom('no-reply@company.com', 'Feedback System');
    $mail->addAddress('manager@company.com', 'Manager');

    // Содержимое письма
    $mail->isHTML(false);
    $mail->Subject = 'Новая заявка с лендинга';
    $mail->Body    = "Поступила новая заявка:\n\n" .
                     "ФИО: $fio\n" .
                     "Email: $email\n" .
                     "Телефон: $phone\n" .
                     "Комментарий: $comment\n" .
                     "Время создания: $now\n";

    $mail->send();
} catch (Exception $e) {
    file_put_contents(__DIR__ . '/mail_errors.log', "Mailer Error: " . $mail->ErrorInfo . "\n", FILE_APPEND);
}

// 7. Расчет времени связи
$contactTimestamp = time() + (90 * 60);
$contactTimeFormatted = date('H:i:s d.m.Y', $contactTimestamp);

echo json_encode([
    'status' => 'success',
    'lastName' => $lastName,
    'firstName' => $firstName,
    'middleName' => $middleName,
    'email' => $email,
    'phone' => $phone,
    'contactTime' => $contactTimeFormatted
]);