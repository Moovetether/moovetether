<?php
session_start(); // Start the session at the very beginning

// Détection de la langue du navigateur
$lang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) 
    ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) 
    : 'en'; // langue par défaut

// Traductions pour les différentes langues
$translations = [
    'en' => [
        'title' => 'Login Page',
        'login_message' => 'Login',
        'username_placeholder' => 'Username',
        'password_placeholder' => 'Password',
        'register_link' => ' ',
        'forgot_password_link' => 'Forgot Account Password? Click Here',
        'welcome_message' => 'Welcome, ',
        'success_message' => 'Welcome back !',
        'logout' => 'Back to your account',
    ],
    'fr' => [
        'title' => 'Page de Connexion',
        'login_message' => 'Connexion',
        'username_placeholder' => 'Nom d\'utilisateur',
        'password_placeholder' => 'Mot de passe',
        'register_link' => ' ',
        'forgot_password_link' => 'Mot de passe oublié ? Cliquez ici',
        'welcome_message' => 'Bienvenue, ',
        'success_message' => 'Connectez-vous a nouveau!',
        'logout' => 'Retour vers le compte',
    ],
];

// Choisir les traductions appropriées
$selected_lang = isset($translations[$lang]) ? $translations[$lang] : $translations['en'];
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-DBi8KJM3uZ5MU6tGzvlKFV0en3ZfGez0n2Jp6w2D8I+4paPbc8n6iVmIpxC4YOhjiNeyQ6chNWiXbhI0KumTQA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
    /* ======= BASE RESET ======= */
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    html, body {
        height: 100%;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow: auto;
    }

    body {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: relative;
        background: #000;
    }

    /* ======= BACKGROUND IMAGE WITH GRADIENT OVERLAY ======= */
    body::before {
        content: "";
        position: fixed;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom right, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)),
                    url('images/Business002.jpeg') no-repeat center center;
        background-size: cover;
        z-index: -1;
    }

    /* ======= CONTAINER ======= */
    .login-container {
        background: rgba(255, 255, 255, 0.06);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 30px 25px;
        width: 100%;
        max-width: 400px;
        color: white;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
    }

    .login-container h2 {
        margin-bottom: 20px;
        font-size: 28px;
        font-weight: bold;
        color: #fff;
    }

    .login-container input {
        width: 100%;
        padding: 12px;
        margin: 15px 0;
        background: transparent;
        border: none;
        border-bottom: 2px solid #00ffcc;
        color: white;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .login-container input:focus {
        outline: none;
        border-bottom: 2px solid #00ff88;
    }

    .login-container ::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .login-container button {
        width: 100%;
        padding: 12px;
        background-color: #00cc99;
        border: none;
        border-radius: 6px;
        color: white;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-top: 15px;
    }

    .login-container button:hover {
        background-color: #00b386;
    }

    .message,
    .notification {
        margin-top: 15px;
        font-weight: bold;
        text-align: center;
    }

    .message {
        color: #FFD700;
    }

    .notification {
        color: #ff4444;
    }

    .links {
        margin-top: 20px;
    }

    .register-link,
    .forgot-password-link {
        color: #00ffff;
        font-size: 14px;
        text-decoration: none;
        margin: 0 10px;
    }

    .register-link:hover,
    .forgot-password-link:hover {
        text-decoration: underline;
    }

    /* ======= RESPONSIVE ======= */
    @media (max-width: 480px) {
        .login-container {
            padding: 20px;
            max-width: 90%;
        }

        .login-container h2 {
            font-size: 22px;
        }

        .login-container input,
        .login-container button {
            font-size: 15px;
        }

        .register-link,
        .forgot-password-link {
            display: block;
            margin-top: 10px;
        }
    }
</style>

    <title><?php echo $selected_lang['title']; ?></title>
</head>
<body>
    <div class="login-container">
        <!-- Display messages -->
        <?php
        // Get the current date
        $currentDate = date('Y-m-d');

        // Display the daily message
        if (!isset($_SESSION['message_seen']) || $_SESSION['message_seen'] != $currentDate) {
            echo '<div class="message">' . $selected_lang['success_message'] . '</div>';
            $_SESSION['message_seen'] = $currentDate; // Set the session variable to today’s date
        }

        // Display the daily welcome message
        if (isset($_SESSION['username'])) {
            if (!isset($_SESSION['welcome_message_seen']) || $_SESSION['welcome_message_seen'] != $currentDate) {
                echo '<p style="color: white;">' . $selected_lang['welcome_message'] . htmlspecialchars($_SESSION['username']) . '!</p>';
                $_SESSION['welcome_message_seen'] = $currentDate; // Set the session variable to today’s date
            }
        }

        if (isset($_GET['success'])) {
            echo '<p class="notification">' . htmlspecialchars($_GET['success']) . '</p>';
        }

        if (isset($_SESSION['username'])) {
            echo '<p><a href="home.php" style="color: #4CAF50;">' . $selected_lang['logout'] . '</a></p>';
        }
        ?>

        <h2><?php echo $selected_lang['login_message']; ?></h2>
        <form action="login_process.php" method="post">
            <input type="text" id="username" name="username" placeholder="<?php echo $selected_lang['username_placeholder']; ?>" required>
            <input type="password" id="password" name="password" placeholder="<?php echo $selected_lang['password_placeholder']; ?>" required>
            <button type="submit"><?php echo $selected_lang['login_message']; ?></button>
        </form>
        <p style="color: white;"><?php echo $selected_lang['register_link']; ?> <a href="register.php" class="register-link"><?php echo $selected_lang['register_link']; ?>Create Account  </a>If You're New</p>
        <p style="color: white;"><a href="forgot_password.php" class="forgot-password-link"><?php echo $selected_lang['forgot_password_link']; ?></a></p>
    </div>

    <!-- Script to clear form fields on successful registration -->
    <script>
        window.onload = function() {
            const successMessage = new URLSearchParams(window.location.search).get('success');
            if (successMessage) {
                document.getElementById("username").value = '';
                document.getElementById("password").value = '';
            }
        };
    </script>
</body>
</html>
