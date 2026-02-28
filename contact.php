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
 sanitize($_POST['    $message =message'] ?? '');

    if (empty($name) || empty($email)) {
        $response['message'] = 'Lūdzu, aizpildiet nepieciešamos laukus.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Nepareiza e-pasta adrese.';
        echo json_encode($response);
        exit;
    }

    $subject = "Jauns ziņojums no gude.lv - $name";
    $body = "Jauns ziņojums no kontaktformas:\n\n";
    $body .= "Vārds: $name\n";
    $body .= "E-pasts: $email\n";
    if (!empty($phone)) {
        $body .= "Tālrunis: $phone\n";
    }
    if (!empty($message)) {
        $body .= "Ziņa:\n$message\n";
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
            $response['message'] = 'Paldies par Jūsu pieteikumu! Sazināsimies ar Jums drīzumā.';
        } catch (Exception $e) {
            $response['message'] = 'Kļūda nosūtot ziņojumu. Lūdzu, mēģiniet vēlreiz.';
        }
    } else {
        if (mail($config['to_email'], $subject, $body, $headers)) {
            $response['success'] = true;
            $response['message'] = 'Paldies par Jūsu pieteikumu! Sazināsimies ar Jums drīzumā.';
        } else {
            $response['message'] = 'Kļūda nosūtot ziņojumu. Lūdzu, mēģiniet vēlreiz.';
        }
    }
}

echo json_encode($response);
