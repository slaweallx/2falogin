<?php
// Kullanıcı bilgileri
$kul = [
    ['username' => 'admin', 'password' => '1234'], // Burada kullanıcılar tanımlanır
    ['username' => 'test', 'password' => 'test123']
];

$logFile = "login_log.json"; // Log dosyası

// IP Adresi Alma Fonksiyonu
function getIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// JSON'a log kaydetme fonksiyonu
function logAttempt($username, $status) {
    global $logFile;

    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'username' => $username,
        'ip' => getIP(),
        'status' => $status
    ];

    $logs = [];
    if (file_exists($logFile)) {
        $logs = json_decode(file_get_contents($logFile), true);
    }
    $logs[] = $logEntry;

    file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));
}

// Kimlik doğrulama fonksiyonu
function authenticate() {
    header('WWW-Authenticate: Basic realm="Yetkili Girisi"');
    header('HTTP/1.0 401 Unauthorized');
    echo "Dogrulama başarısız. IP adresiniz loglandı.";
    logAttempt($_SERVER['PHP_AUTH_USER'] ?? 'unknown', 'failed');
    exit;
}

// Kimlik kontrolü
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    authenticate();
} else {
    $auth = false;
    foreach ($kul as $user) {
        if ($_SERVER['PHP_AUTH_USER'] === $user['username'] && $_SERVER['PHP_AUTH_PW'] === $user['password']) {
            $auth = true;
            break;
        }
    }

    if ($auth) {
        echo "Hoşgeldiniz, " . htmlspecialchars($_SERVER['PHP_AUTH_USER']);
        logAttempt($_SERVER['PHP_AUTH_USER'], 'success');
    } else {
        authenticate();
    }
}
?>
