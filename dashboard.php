
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userName = $_SESSION["user_name"] ?? "User";
$userEmail = $_SESSION["user_email"] ?? "";

require_once "includes/db.php";

$statsQuery = $conn->prepare(
    "SELECT
        COUNT(*) AS total,
        COALESCE(ROUND(AVG(ats_score)), 0) AS average_score,
        COALESCE(MAX(ats_score), 0) AS best_score
     FROM analysis_history
     WHERE user_id = ?"
);

$statsQuery->execute([$_SESSION["user_id"]]);
$stats = $statsQuery->fetch();

$historyQuery = $conn->prepare(
    "SELECT ats_score, matched_count, missing_count, created_at
     FROM analysis_history
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 10"
);

$historyQuery->execute([$_SESSION["user_id"]]);
$history = $historyQuery->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Resumate AI</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Arial, sans-serif;
    min-height: 100vh;
    color: #49345e;
    background:
        radial-gradient(ellipse at 10% 10%, rgba(194, 157, 255, .48), transparent 38%),
        radial-gradient(ellipse at 90% 15%, rgba(255, 166, 207, .48), transparent 38%),
        radial-gradient(ellipse at 50% 90%, rgba(139, 207, 255, .48), transparent 42%),
        linear-gradient(135deg, #e9ddff, #ffe3f0, #dceeff);
    background-attachment: fixed;
}

/* NAVBAR */

.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 7%;
    background: rgba(225, 208, 255, .55);
    border-bottom: 1px solid rgba(255,255,255,.8);
    backdrop-filter: blur(25px);
    box-shadow: 0 8px 30px rgba(117, 75, 160, .08);
}

.logo {
    font-size: 26px;
    font-weight: 800;
    color: #8052b5;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 22px;
}

.nav-links a {
    text-decoration: none;
    color: #654b7b;
    font-weight: 600;
    transition: .3s;
}

.nav-links a:hover {
    color: #a04fc7;
    transform: translateY(-2px);
}

.nav-links .logout {
    padding: 11px 22px;
    border-radius: 25px;
    color: white;
    background: linear-gradient(110deg, #8955d5, #d66eae);
    box-shadow: 0 7px 18px rgba(155, 91, 195, .25);
}

/* MAIN CONTAINER */

.container {
    width: 90%;
    max-width: 1150px;
    margin: 45px auto;
}

/* WELCOME SECTION */

.welcome {
    padding: 42px;
    border-radius: 30px;
    background: linear-gradient(
        120deg,
        rgba(183, 144, 255, .48),
        rgba(255, 161, 208, .42),
        rgba(133, 204, 255, .45)
    );
    border: 1px solid rgba(255,255,255,.9);
    backdrop-filter: blur(25px);
    box-shadow: 0 18px 45px rgba(120, 80, 170, .15);
    transition: .4s;
}

.welcome:hover {
    transform: translateY(-4px);
    box-shadow: 0 25px 50px rgba(120, 80, 170, .20);
}

.welcome h1 {
    font-size: 34px;
    margin-bottom: 14px;
    color: #583477;
}

.welcome p {
    color: #705a88;
    line-height: 1.8;
}

/* SECTION HEADINGS */

.section-title {
    margin: 38px 0 22px;
    font-size: 24px;
    color: #654184;
}

/* DASHBOARD CARDS */

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 23px;
}

.dashboard-card {
    padding: 29px;
    border-radius: 25px;
    background: linear-gradient(
        145deg,
        rgba(220, 198, 255, .70),
        rgba(255, 218, 239, .65),
        rgba(213, 237, 255, .70)
    );
    border: 1px solid rgba(255,255,255,.95);
    backdrop-filter: blur(25px);
    box-shadow: 0 12px 35px rgba(116, 76, 160, .13);
    transition: all .4s ease;
}

.dashboard-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 22px 45px rgba(116, 76, 160, .22);
    border-color: white;
}

.card-icon {
    font-size: 36px;
    margin-bottom: 18px;
}

.dashboard-card h3 {
    margin-bottom: 13px;
    color: #63447d;
}

.dashboard-card h2 {
    font-size: 31px;
    color: #8955c5;
    margin-bottom: 10px;
}

.dashboard-card p {
    color: #78648e;
    line-height: 1.7;
    font-size: 14px;
    min-height: 65px;
}

/* BUTTONS */

.card-btn {
    display: inline-block;
    margin-top: 18px;
    padding: 12px 21px;
    border-radius: 25px;
    text-decoration: none;
    color: white;
    font-size: 14px;
    font-weight: bold;
    background: linear-gradient(110deg, #8955d5, #d66eae, #72bce5);
    box-shadow: 0 8px 20px rgba(137, 85, 213, .22);
    transition: .3s;
}

.card-btn:hover {
    transform: translateY(-4px);
    box-shadow: 0 13px 25px rgba(137, 85, 213, .32);
}

/* PROFILE AND HISTORY */

.profile-card {
    margin-top: 30px;
    padding: 28px;
    border-radius: 25px;
    background: linear-gradient(
        135deg,
        rgba(214, 194, 255, .68),
        rgba(255, 211, 235, .62),
        rgba(195, 229, 255, .68)
    );
    border: 1px solid rgba(255,255,255,.95);
    backdrop-filter: blur(25px);
    box-shadow: 0 12px 35px rgba(116, 76, 160, .13);
}

.profile-card h2 {
    color: #63447d;
    margin-bottom: 15px;
}

.profile-card p {
    margin-top: 13px;
    color: #725b89;
}

/* HISTORY TABLE */

.profile-card table {
    border-collapse: separate !important;
    border-spacing: 0 9px !important;
}

.profile-card th {
    color: #62417e;
    padding: 15px !important;
}

.profile-card td {
    padding: 16px !important;
    color: #6d5684;
    background: rgba(255,255,255,.32);
}

.profile-card tbody tr {
    transition: .3s;
}

.profile-card tbody tr:hover {
    transform: scale(1.01);
}

.profile-card tbody tr td:first-child {
    border-radius: 12px 0 0 12px;
}

.profile-card tbody tr td:last-child {
    border-radius: 0 12px 12px 0;
}

.profile-card td strong {
    color: #8955c5;
}

/* FOOTER */

footer {
    text-align: center;
    padding: 30px 15px;
    color: #806c98;
}

/* RESPONSIVE */

@media(max-width:800px) {
    .dashboard-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media(max-width:550px) {
    .navbar {
        padding: 18px 5%;
        flex-wrap: wrap;
        gap: 15px;
    }

    .nav-links {
        gap: 12px;
        font-size: 13px;
    }

    .container {
        margin: 25px auto;
    }

    .welcome {
        padding: 25px;
    }

    .welcome h1 {
        font-size: 25px;
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

</style>
</head>

<body>

<nav class="navbar">
    <div class="logo">✦ Resumate AI</div>

    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="analyzer.php">Analyzer</a>
        <a href="logout.php" class="logout">Logout ↗</a>
    </div>
</nav>

<main class="container">

    <section class="welcome">
        <h1>Welcome Back, <?= htmlspecialchars($userName) ?>! 👋</h1>

        <p>
            Your personal resume improvement workspace is ready.
            Analyze your resume, improve your bullet points and get
            closer to your dream internship.
        </p>
    </section>

<h2 class="section-title">Your Performance</h2>

<section class="dashboard-grid">

    <div class="dashboard-card">
        <div class="card-icon">📊</div>
        <h3>Total Analyses</h3>
        <h2><?= (int)$stats["total"] ?></h2>
        <p>Total resumes analyzed by you.</p>
    </div>

    <div class="dashboard-card">
        <div class="card-icon">📈</div>
        <h3>Average ATS Score</h3>
        <h2><?= (int)$stats["average_score"] ?>%</h2>
        <p>Your average resume matching score.</p>
    </div>

    <div class="dashboard-card">
        <div class="card-icon">🏆</div>
        <h3>Best ATS Score</h3>
        <h2><?= (int)$stats["best_score"] ?>%</h2>
        <p>Your highest resume matching score.</p>
    </div>

</section>

<h2 class="section-title">Recent Analysis History</h2>

<div class="profile-card" style="overflow-x:auto;">

    <?php if (count($history) > 0): ?>

    <table style="width:100%; border-collapse:collapse; text-align:left;">
        <thead>
            <tr>
                <th style="padding:14px;">Date</th>
                <th style="padding:14px;">ATS Score</th>
                <th style="padding:14px;">Matched</th>
                <th style="padding:14px;">Missing</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($history as $item): ?>
            <tr>
                <td style="padding:14px;">
                    <?= htmlspecialchars(date("d M Y, h:i A", strtotime($item["created_at"]))) ?>
                </td>

                <td style="padding:14px;">
                    <strong><?= (int)$item["ats_score"] ?>%</strong>
                </td>

                <td style="padding:14px;">
                    <?= (int)$item["matched_count"] ?>
                </td>

                <td style="padding:14px;">
                    <?= (int)$item["missing_count"] ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php else: ?>

        <p>No analysis history yet. Analyze your first resume to see your statistics here!</p>

    <?php endif; ?>

</div>

    <h2 class="section-title">Your Workspace</h2>

    <section class="dashboard-grid">

        <div class="dashboard-card">
            <div class="card-icon">📄</div>
            <h3>ATS Resume Analyzer</h3>
            <p>
                Check your resume against a job description and
                discover matched and missing keywords.
            </p>
            <a href="analyzer.php" class="card-btn">Analyze Resume →</a>
        </div>

        <div class="dashboard-card">
            <div class="card-icon">✨</div>
            <h3>Bullet Point Improver</h3>
            <p>
                Transform weak resume sentences into stronger,
                professional bullet points.
            </p>
            <a href="analyzer.php#bulletImprover" class="card-btn">
                Improve Bullets →
            </a>
        </div>

        <div class="dashboard-card">
            <div class="card-icon">👤</div>
            <h3>My Account</h3>
            <p>
                View your registered account details and
                manage your Resumate AI session.
            </p>
            <a href="#profile" class="card-btn">View Profile →</a>
        </div>

    </section>

    <section class="profile-card" id="profile">
        <h2>Account Information</h2>

        <p><strong>Name:</strong>
            <?= htmlspecialchars($userName) ?>
        </p>

        <p><strong>Email:</strong>
            <?= htmlspecialchars($userEmail) ?>
        </p>

        <p><strong>Account Status:</strong> Active</p>
    </section>

</main>

<footer>
    © 2026 Resumate AI. Built to make your career journey smarter. ✦
</footer>

</body>
</html>
