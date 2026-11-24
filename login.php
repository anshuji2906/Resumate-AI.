
<?php
session_start();
require_once "includes/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $pass = $_POST["password"] ?? "";

    if ($email === "" || $pass === "") {
        $error = "Please enter email and password.";
    } else {

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user["password"])) {

            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["full_name"];
            $_SESSION["user_email"] = $user["email"];

            header("Location: dashboard.php");
            exit;

        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login | Resumate AI</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 25px;
    overflow-x: hidden;
    font-family: 'Inter', sans-serif;

    background: linear-gradient(
        135deg,
        #f8efff,
        #ffeef6,
        #eaf6ff,
        #f2edff
    );
    background-size: 300% 300%;
    animation: backgroundMove 12s ease infinite;
}

/* BACKGROUND ORBS */

.orb {
    position: fixed;
    border-radius: 50%;
    filter: blur(5px);
    pointer-events: none;
    z-index: 0;
    animation: floatOrb 8s ease-in-out infinite;
}

.orb-one {
    width: 280px;
    height: 280px;
    background: rgba(190,140,255,0.28);
    top: 5%;
    left: 8%;
}

.orb-two {
    width: 240px;
    height: 240px;
    background: rgba(255,155,205,0.30);
    bottom: 5%;
    right: 10%;
    animation-delay: 2s;
}

.orb-three {
    width: 170px;
    height: 170px;
    background: rgba(130,195,255,0.28);
    top: 55%;
    left: 12%;
    animation-delay: 4s;
}

/* MAIN CARD */

.auth-card {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 440px;
    padding: 42px 38px;

    border-radius: 30px;

    background: linear-gradient(
        135deg,
        rgba(255,255,255,0.78),
        rgba(255,255,255,0.48)
    );

    border: 1px solid rgba(255,255,255,0.95);

    backdrop-filter: blur(28px);
    -webkit-backdrop-filter: blur(28px);

    box-shadow:
        0 20px 60px rgba(123,80,165,0.15),
        inset 0 1px 0 rgba(255,255,255,0.95);

    animation: cardAppear 0.9s ease both;

    transition: all 0.4s ease;
}

.auth-card:hover {
    transform: translateY(-6px);
    box-shadow:
        0 28px 70px rgba(123,80,165,0.20),
        inset 0 1px 0 rgba(255,255,255,0.95);
}

/* LOGO */

.logo {
    text-align: center;
    margin-bottom: 28px;
}

.logo-icon {
    width: 58px;
    height: 58px;
    margin: 0 auto 13px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 19px;
    font-size: 29px;
    color: white;

    background: linear-gradient(
        135deg,
        #9562D6,
        #D77DBD,
        #83A8EF
    );

    box-shadow: 0 9px 25px rgba(151,98,213,0.28);

    animation: logoFloat 3s ease-in-out infinite;
}

.logo h2 {
    color: #563B70;
    font-size: 23px;
    font-weight: 900;
}

.logo h2 span {
    color: #A36ACF;
}

/* HEADING */

h1 {
    color: #49305F;
    text-align: center;
    font-size: 30px;
    font-weight: 900;
    margin-bottom: 10px;
}

.subtitle {
    color: #897795;
    text-align: center;
    font-size: 13px;
    line-height: 1.7;
    margin-bottom: 30px;
}

/* ERROR */

.error {
    color: #C03959;
    background: rgba(255,225,233,0.75);
    border: 1px solid rgba(220,100,130,0.2);
    padding: 12px;
    border-radius: 12px;
    text-align: center;
    font-size: 13px;
    margin-bottom: 20px;
}

/* INPUTS */

.form-group {
    margin-bottom: 19px;
}

label {
    display: block;
    color: #563B70;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 9px;
}

.input-wrap {
    position: relative;
}

input {
    width: 100%;
    padding: 15px 17px;

    border: 1px solid rgba(179,145,207,0.35);
    border-radius: 14px;

    outline: none;
    color: #49305F;
    font-family: inherit;
    font-size: 13px;

    background: rgba(255,255,255,0.65);

    transition: all 0.3s ease;
}

input::placeholder {
    color: #AA9BB5;
}

input:hover {
    border-color: #C39AE8;
}

input:focus {
    background: rgba(255,255,255,0.95);
    border-color: #A675DA;
    box-shadow: 0 0 0 4px rgba(166,107,232,0.12);
}

/* FORGOT PASSWORD */

.forgot-row {
    display: flex;
    justify-content: flex-end;
    margin-top: -7px;
    margin-bottom: 23px;
}

.forgot-row a {
    color: #9560C8;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    transition: color 0.3s ease;
}

.forgot-row a:hover {
    color: #D16DBB;
    text-decoration: underline;
}

/* LOGIN BUTTON */

.login-btn {
    position: relative;
    overflow: hidden;

    width: 100%;
    padding: 16px;

    border: none;
    border-radius: 15px;

    color: white;
    font-family: inherit;
    font-size: 15px;
    font-weight: 800;

    cursor: pointer;

    background: linear-gradient(
        110deg,
        #9560D4,
        #D278BD,
        #819BEB
    );

    background-size: 200% 200%;

    box-shadow: 0 10px 25px rgba(145,96,213,0.28);

    transition: all 0.4s ease;

    animation: gradientMove 5s ease infinite;
}

.login-btn:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 32px rgba(145,96,213,0.38);
}

.login-btn:active {
    transform: scale(0.98);
}

/* REGISTER */

.register-text {
    color: #897795;
    font-size: 13px;
    text-align: center;
    margin-top: 25px;
}

.register-text a {
    color: #9560C8;
    font-weight: 800;
    text-decoration: none;
    transition: 0.3s;
}

.register-text a:hover {
    color: #D16DBB;
    text-decoration: underline;
}

/* BACK HOME */

.back-home {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #9886A6;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    transition: 0.3s;
}

.back-home:hover {
    color: #9560C8;
}

/* ANIMATIONS */

@keyframes backgroundMove {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

@keyframes floatOrb {
    0%, 100% { transform: translateY(0) translateX(0); }
    50% { transform: translateY(-35px) translateX(20px); }
}

@keyframes cardAppear {
    from {
        opacity: 0;
        transform: translateY(35px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes logoFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-7px); }
}

@keyframes gradientMove {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

/* MOBILE */

@media (max-width: 500px) {
    .auth-card {
        padding: 32px 23px;
        border-radius: 24px;
    }

    h1 {
        font-size: 26px;
    }

    .orb-one {
        width: 170px;
        height: 170px;
    }

    .orb-two {
        width: 150px;
        height: 150px;
    }
}

@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation: none !important;
        transition: none !important;
    }
}

</style>
</head>

<body>

<!-- Animated Background -->
<div class="orb orb-one"></div>
<div class="orb orb-two"></div>
<div class="orb orb-three"></div>

<!-- Login Card -->
<div class="auth-card">

    <div class="logo">
        <div class="logo-icon">✦</div>
        <h2>Resumate <span>AI</span></h2>
    </div>

    <h1>Welcome Back!</h1>

    <p class="subtitle">
        Login to your Resumate AI account.
        Your journey to a stronger resume continues here.
    </p>

    <?php if ($error): ?>
        <p class="error">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label for="email">Email Address</label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email address"
                autocomplete="email"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                autocomplete="current-password"
                required
            >
        </div>

        <div class="forgot-row">
            <a href="forgot-password.php">Forgot Password?</a>
        </div>

        <button type="submit" class="login-btn">
            Login to Account →
        </button>

    </form>

    <p class="register-text">
        Don't have an account?
        <a href="register.php">Create Account</a>
    </p>

    <a href="index.php" class="back-home">
        ← Back to Home
    </a>

</div>

</body>
</html>

