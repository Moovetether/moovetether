<?php
session_start();
// require 'vendor/autoload.php';

// 🔒 IP de l’utilisateur
function getUserIP() {
    return $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
}

// 🌍 Géolocalisation IP
function getGeolocation($ip) {
    $apiKey = 'your_api_key'; // Remplace par une vraie clé ipinfo.io
    $url = "http://ipinfo.io/{$ip}/json?token={$apiKey}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// 🌐 Traductions
$translations = [
    'en' => [
        'login_success' => 'Login Successful',
        'wrong_information' => 'Wrong information, don\'t try too many attempts',
        'enter_credentials' => 'Please enter username and password',
        'ip_changed' => 'Your login IP address has changed. If this was not you, reset your password now to secure your account , go to forget password.',
    ],
    'fr' => [
        'login_success' => 'Connexion réussie',
        'wrong_information' => 'Mauvaises informations, n\'essayez pas trop de fois',
        'enter_credentials' => 'Veuillez entrer nom d\'utilisateur et mot de passe',
        'ip_changed' => 'Your login IP address has changed. If this was not you, reset your password now to secure your account , go to forget password.',
    ]
];

function getLanguage() {
    $langs = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en';
    return strpos($langs, 'fr') !== false ? 'fr' : 'en';
}

$lang = getLanguage();
$trans = $translations[$lang];
$userIP = getUserIP();

// 📥 Si formulaire envoyé
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!$username || !$password) {
        header("Location: login.php?success=" . urlencode($trans['enter_credentials']));
        exit();
    }

    try {
        $conn = new PDO(
    "mysql:host=shuttle.proxy.rlwy.net;port=47353;dbname=railway",
    "root",
    "hJeBRIvWHXJQabSmozrcpOrqFCacEYmY"
);

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            header("Location: login.php?success=" . urlencode($trans['wrong_information']));
            exit();
        }

        // 🧠 Login valide → session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['loggedin'] = true;

        $lastIp = $user['last_ip'] ?? null;
        $locationData = getGeolocation($userIP);
        $currentLocation = $locationData['country'] ?? 'Unknown';

        // ✅ MAJ IP et localisation
        $stmt = $conn->prepare("UPDATE users SET last_ip = :ip, last_known_location = :loc WHERE user_id = :id");
        $stmt->execute([
            ':ip' => $userIP,
            ':loc' => $currentLocation,
            ':id' => $user['user_id']
        ]);

        // ✅ Message si IP différente
        if ($lastIp && $lastIp !== $userIP) {
            $_SESSION['ip_alert'] = $trans['ip_changed'];
        }

        // ✅ Like automatique
        $stmt = $conn->prepare("SELECT 1 FROM likes WHERE liker_username = :u AND seller_username = 'Moovetether'");
        $stmt->execute([':u' => $username]);
        if (!$stmt->fetchColumn()) {
            $stmt = $conn->prepare("INSERT INTO likes (liker_username, seller_username) VALUES (:u, 'Moovetether')");
            $stmt->execute([':u' => $username]);
        }

        // 👇 Stories désactivées, déplacées vers le CRON (voir script_cron_stories.php)
/*
        $stories = [
            ['image' => 'images/BusinessBook27.jpeg', 'caption' => '📢 - ✨'],
            ['image' => 'images/slide/Business03.jpeg', 'caption' => '📢 - ✨'],
            ['image' => 'images/slide/BusinessBook0003.jpeg', 'caption' => '📢 - ✨'],
            ['image' => 'images/slide/B-logo.jpeg', 'caption' => '📢 -ADS M163 ✨'],
            ['image' => 'images/slide/Business009.jpeg', 'caption' => '📢 -ADS M163 ✨']
        ];

        foreach ($stories as $story) {
            $stmt = $conn->prepare("
                SELECT COUNT(*) FROM stories 
                WHERE user_id = (SELECT user_id FROM users WHERE username = 'Moovetether') 
                AND media_url = :img AND DATE(created_at) = CURDATE()
            ");
            $stmt->execute([':img' => $story['image']]);
            if ($stmt->fetchColumn() == 0) {
                $expiresAt = date("Y-m-d H:i:s", time() + 86400);
                $stmt = $conn->prepare("
                    INSERT INTO stories (user_id, media_url, caption, created_at, expires_at)
                    VALUES ((SELECT user_id FROM users WHERE username = 'Moovetether'), :img, :cap, NOW(), :exp)
                ");
                $stmt->execute([
                    ':img' => $story['image'],
                    ':cap' => $story['caption'],
                    ':exp' => $expiresAt
                ]);
            }
        }
*/

        // ✅ Log activité
        $stmt = $conn->prepare("INSERT INTO activity_log (user_id, timestamp, action, ip_address) VALUES (:id, NOW(), 'User logged in', :ip)");
        $stmt->execute([':id' => $user['user_id'], ':ip' => $userIP]);

        // 🚀 Redirection
        header("Location: home.php");
        exit();

    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

header("Location: login.php");
exit();
