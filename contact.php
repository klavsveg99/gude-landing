<?php
session_start();

$config = [
    'smtp_host' => 'smtp.hostinger.com',
    'smtp_port' => 587,
    'smtp_username' => '',
    'smtp_password' => '',
    'from_email' => '',
    'from_name' => 'GUDE',
    'to_email' => 'info@gude.lv'
];

function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $containerType = sanitize($_POST['containerType'] ?? '');
    $containerAmount = sanitize($_POST['containerAmount'] ?? '');
    $location = sanitize($_POST['location'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $lang = sanitize($_POST['lang'] ?? 'lv');

    $fields = [
        'lv' => ['name' => 'Vārds', 'email' => 'E-pasts', 'phone' => 'Tālrunis', 'containerType' => 'Konteinera tips', 'containerAmount' => 'Konteinieru daudzums', 'location' => 'Atrašanās vieta', 'message' => 'Ziņa', 'error_required' => 'Lūdzu, aizpildiet nepieciešamos laukus.', 'error_email' => 'Nepareiza e-pasta adrese.', 'success' => 'Paldies par Jūsu pieteikumu! Sazināsimies ar Jums drīzumā.', 'error_send' => 'Kļūda nosūtot ziņojumu. Lūdzu, mēģiniet vēlreiz.'],
        'en' => ['name' => 'Name', 'email' => 'Email', 'phone' => 'Phone', 'containerType' => 'Container type', 'containerAmount' => 'Container amount', 'location' => 'Location', 'message' => 'Message', 'error_required' => 'Please fill in all required fields.', 'error_email' => 'Invalid email address.', 'success' => 'Thank you for your request! We will contact you soon.', 'error_send' => 'Error sending message. Please try again.'],
        'ru' => ['name' => 'Имя', 'email' => 'Эл. почта', 'phone' => 'Телефон', 'containerType' => 'Тип контейнера', 'containerAmount' => 'Количество контейнеров', 'location' => 'Местоположение', 'message' => 'Сообщение', 'error_required' => 'Пожалуйста, заполните все обязательные поля.', 'error_email' => 'Неверный адрес электронной почты.', 'success' => 'Спасибо за вашу заявку! Мы свяжемся с вами в ближайшее время.', 'error_send' => 'Ошибка отправки сообщения. Пожалуйста, попробуйте еще раз.'],
        'de' => ['name' => 'Name', 'email' => 'E-Mail', 'phone' => 'Telefon', 'containerType' => 'Behältertyp', 'containerAmount' => 'Behältermenge', 'location' => 'Standort', 'message' => 'Nachricht', 'error_required' => 'Bitte füllen Sie alle erforderlichen Felder aus.', 'error_email' => 'Ungültige E-Mail-Adresse.', 'success' => 'Vielen Dank für Ihre Anfrage! Wir werden uns in Kürze bei Ihnen melden.', 'error_send' => 'Fehler beim Senden der Nachricht. Bitte versuchen Sie es erneut.'],
        'nl' => ['name' => 'Naam', 'email' => 'E-mail', 'phone' => 'Telefoon', 'containerType' => 'Containertype', 'containerAmount' => 'Container aantal', 'location' => 'Locatie', 'message' => 'Bericht', 'error_required' => 'Vul alle verplichte velden in.', 'error_email' => 'Ongeldig e-mailadres.', 'success' => 'Bedankt voor uw aanvraag! Wij nemen binnenkort contact met u op.', 'error_send' => 'Fout bij verzenden van bericht. Probeer het opnieuw.'],
        'da' => ['name' => 'Navn', 'email' => 'E-mail', 'phone' => 'Telefon', 'containerType' => 'Containertype', 'containerAmount' => 'Container antal', 'location' => 'Lokation', 'message' => 'Besked', 'error_required' => 'Udfyld venligst alle påkrævede felter.', 'error_email' => 'Ugyldig e-mailadresse.', 'success' => 'Tak for din forespørgsel! Vi kontakter dig snart.', 'error_send' => 'Fejl ved afsendelse af besked. Prøv igen.']
    ];

    $t = $fields[$lang] ?? $fields['en'];

    if (empty($name) || empty($email) || empty($phone) || empty($containerType) || empty($containerAmount) || empty($location)) {
        $response['message'] = $t['error_required'];
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = $t['error_email'];
        echo json_encode($response);
        exit;
    }

    $subject = "Jauns ziņojums no gude.lv - $name";
    if ($lang !== 'lv') {
        $subject = "New message from gude.lv - $name";
    }
    
    $body = "Jauns ziņojums no kontaktformas:\n\n";
    $body .= "{$t['name']}: $name\n";
    $body .= "{$t['email']}: $email\n";
    $body .= "{$t['phone']}: $phone\n";
    $body .= "{$t['containerType']}: $containerType\n";
    $body .= "{$t['containerAmount']}: $containerAmount\n";
    $body .= "{$t['location']}: $location\n";
    if (!empty($message)) {
        $body .= "{$t['message']}:\n$message\n";
    }

    $headers = "From: {$config['from_name']} <{$config['from_email']}>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    if (!empty($config['smtp_username']) && !empty($config['smtp_password'])) {
        require_once 'PHPMailer/PHPMailer.php';
        require_once 'PHPMailer/SMTP.php';
        require_once 'PHPMailer/Exception.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_username'];
            $mail->Password = $config['smtp_password'];
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $config['smtp_port'];
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($config['to_email']);
            $mail->addReplyTo($email, $name);

            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            $response['success'] = true;
            $response['message'] = $t['success'];
        } catch (Exception $e) {
            $response['message'] = $t['error_send'];
        }
    } else {
        if (mail($config['to_email'], $subject, $body, $headers)) {
            $response['success'] = true;
            $response['message'] = $t['success'];
        } else {
            $response['message'] = $t['error_send'];
        }
    }
}

echo json_encode($response);
