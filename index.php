<?php
/**
 * StockVerse - Landing Page
 */
require_once __DIR__ . '/config.php';
require_once INCLUDES_PATH . '/auth.php';

// Redirect logged-in users to dashboard
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/user/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StockVerse - Master the stock market with interactive learning modules, quizzes, and real-time progress tracking. Start your investment journey today.">
    <title><?= APP_NAME ?> — <?= APP_TAGLINE ?></title>
    <link rel="stylesheet" href="<?= ASSETS_PATH ?>/css/style.css">
    <link rel="stylesheet" href="<?= ASSETS_PATH ?>/css/components.css">
    <link rel="stylesheet" href="<?= ASSETS_PATH ?>/css/pages.css">
    <script>(function(){var t=localStorage.getItem('theme');if(t)document.documentElement.setAttribute('data-theme',t)})()</script>
</head>
<body>
<div class="landing-page">
    <!-- Navigation -->
    <nav class="landing-nav" id="landingNav">
        <a href="<?= BASE_URL ?>/" class="nav-brand">
            <div class="brand-icon">📈</div>
            <h1><?= APP_NAME ?></h1>
        </a>
        <div class="nav-links">
            <button class="theme-toggle" id="themeToggle">🌙</button>
            <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-ghost">Sign In</a>
            <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary">Get Started</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="hero-badge">🚀 Free & Open Learning Platform</div>
            <h1>
                Learn to Invest in the<br>
                <span class="gradient-text">Stock Market</span>
            </h1>
            <p>
                Master stock market fundamentals, technical analysis, and trading strategies 
                with our structured learning modules, interactive quizzes, and progress tracking.
            </p>
            <div class="hero-buttons">
                <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary btn-lg">
                    Start Learning Free →
                </a>
                <a href="#features" class="btn btn-ghost btn-lg">
                    Explore Features
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="section-title">
            <h2>Everything You Need to Learn</h2>
            <p>A complete learning platform designed for aspiring investors and traders</p>
        </div>

        <div class="features-grid">
            <div class="feature-card stagger-item">
                <div class="feature-icon">📚</div>
                <h3>Structured Modules</h3>
                <p>Learn step-by-step with organized modules covering everything from basics to advanced strategies.</p>
            </div>
            <div class="feature-card stagger-item">
                <div class="feature-icon">❓</div>
                <h3>Interactive Quizzes</h3>
                <p>Test your knowledge after each chapter with multiple-choice quizzes and instant scoring.</p>
            </div>
            <div class="feature-card stagger-item">
                <div class="feature-icon">📊</div>
                <h3>Progress Tracking</h3>
                <p>Track your learning journey with visual progress bars and a personalized dashboard.</p>
            </div>
            <div class="feature-card stagger-item">
                <div class="feature-icon">🌙</div>
                <h3>Dark Mode</h3>
                <p>Easy on the eyes with a beautiful dark mode that you can toggle anytime.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. Built for learners, by learners. All rights reserved.</p>
    </footer>
</div>

<script src="<?= ASSETS_PATH ?>/js/app.js"></script>
</body>
</html>
