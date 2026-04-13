<?php
header('Content-Type: application/json');

require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$containerType = isset($_POST['containerType']) ? trim($_POST['containerType']) : '';
$containerAmount = isset($_POST['containerAmount']) ? trim($_POST['containerAmount']) : '';
$location = isset($_POST['location']) ? trim($_POST['location']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

$referer = $_SERVER['HTTP_REFERER'] ?? '';
$lang = (strpos($referer, '/en') !== false || strpos($referer, '/en?') !== false) ? 'en' : 
       ((strpos($referer, '/ru') !== false || strpos($referer, '/ru?') !== false) ? 'ru' : 
       ((strpos($referer, '/de') !== false || strpos($referer, '/de?') !== false) ? 'de' : 
       ((strpos($referer, '/nl') !== false || strpos($referer, '/nl?') !== false) ? 'nl' : 
       ((strpos($referer, '/da') !== false || strpos($referer, '/da?') !== false) ? 'da' : 'lv'))));

$messages = [
    'lv' => ['required' => 'Lūdzu, aizpildiet visus obligātos laukus.', 'invalid_email' => 'Lūdzu, ievadiet derīgu e-pasta adresi.', 'success' => 'Paldies! Jūsu ziņa ir nosūtīta. Mēs sazināsimies ar jums drīzumā.', 'error' => 'Kļūda nosūtot ziņojumu. Lūdzu, mēģiniet vēlreiz.'],
    'en' => ['required' => 'Please fill in all required fields.', 'invalid_email' => 'Please enter a valid email address.', 'success' => 'Thank you! Your message has been sent. We will contact you soon.', 'error' => 'Error sending message. Please try again.'],
    'ru' => ['required' => 'Пожалуйста, заполните все обязательные поля.', 'invalid_email' => 'Пожалуйста, введите корректный адрес эл. почты.', 'success' => 'Спасибо! Ваше сообщение отправлено. Мы свяжемся с вами скоро.', 'error' => 'Ошибка отправки. Попробуйте еще раз.'],
    'de' => ['required' => 'Bitte füllen Sie alle erforderlichen Felder aus.', 'invalid_email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.', 'success' => 'Vielen Dank! Ihre Nachricht wurde gesendet. Wir werden uns bald bei Ihnen melden.', 'error' => 'Fehler beim Senden. Bitte versuchen Sie es erneut.'],
    'nl' => ['required' => 'Vul alle verplichte velden in.', 'invalid_email' => 'Voer een geldig e-mailadres in.', 'success' => 'Bedankt! Uw bericht is verzonden. Wij nemen binnenkort contact met u op.', 'error' => 'Fout bij verzenden. Probeer het opnieuw.'],
    'da' => ['required' => 'Udfyld venligst alle påkrævede felter.', 'invalid_email' => 'Indtast en gyldig e-mailadresse.', 'success' => 'Tak! Din besked er sendt. Vi kontakter dig snart.', 'error' => 'Fejl ved afsendelse. Prøv igen.']
];

$t = $messages[$lang] ?? $messages['lv'];

if (empty($name) || empty($email) || empty($phone) || empty($containerType) || empty($containerAmount) || empty($location)) {
    echo json_encode(['success' => false, 'message' => $t['required']]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => $t['invalid_email']]);
    exit;
}

$to = 'info@gude.lv';
$subject = ($lang === 'lv' ? 'Jauna ziņa no GUDE.LV - ' : ($lang === 'ru' ? 'Новое сообщение с GUDE.LV - ' : 'New message from GUDE.LV - ')) . $name;

$email_content = ($lang === 'lv' ? "Jauna ziņa no kontaktformas:\n\n" : ($lang === 'ru' ? "Новое сообщение из контактной формы:\n\n" : "New message from contact form:\n\n"));
$email_content .= ($lang === 'lv' ? "Vārds: " : ($lang === 'ru' ? "Имя: " : "Name: ")) . $name . "\n";
$email_content .= ($lang === 'lv' ? "E-pasts: " : ($lang === 'ru' ? "Эл. почта: " : "Email: ")) . $email . "\n";
$email_content .= ($lang === 'lv' ? "Tālrunis: " : ($lang === 'ru' ? "Телефон: " : "Phone: ")) . $phone . "\n";
$email_content .= ($lang === 'lv' ? "Iepakojuma veids: " : ($lang === 'ru' ? "Тип упаковки: " : "Packaging type: ")) . $containerType . "\n";
$email_content .= ($lang === 'lv' ? "Iepakojuma daudzums: " : ($lang === 'ru' ? "Количество упаковки: " : "Packaging amount: ")) . $containerAmount . "\n";
$email_content .= ($lang === 'lv' ? "Atrašanās vieta: " : ($lang === 'ru' ? "Местоположение: " : "Location: ")) . $location . "\n";
if (!empty($message)) {
    $email_content .= ($lang === 'lv' ? "Ziņa: " : ($lang === 'ru' ? "Сообщение: " : "Message: ")) . $message . "\n";
}
$email_content .= "\n---\n";
$email_content .= ($lang === 'lv' ? "Nosūtīts no: " : ($lang === 'ru' ? "Отправлено с: " : "Sent from: ")) . ($referer ?: 'Unknown') . "\n";
$email_content .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'info@gude.lv';
    $mail->Password = 'SiaGude2026!';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;
    
    $mail->setFrom('info@gude.lv', 'SIA GUDE');
    $mail->addAddress($to);
    $mail->addReplyTo($email, $name);
    
    $mail->Subject = $subject;
    $mail->Body = $email_content;
    $mail->CharSet = 'UTF-8';
    
    $mail->send();
    echo json_encode(['success' => true, 'message' => $t['success']]);
} catch (Exception $e) {
    error_log('PHPMailer Error: ' . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'message' => $t['error']]);
}