<?php
/**
 * Reusable Header Component
 * 
 * Required variables before including:
 * $pageTitle - string - Page title for topbar
 * $currentPage - string - Active nav item identifier
 */

require_once __DIR__ . '/auth.php';

$user = getCurrentUser();
$isAdminUser = isAdmin();
$initials = $user ? strtoupper(substr($user['name'], 0, 1)) : '?';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StockVerse - Master the stock market with interactive modules, quizzes, and progress tracking.">
    <title><?= e($pageTitle ?? 'Dashboard') ?> — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= ASSETS_PATH ?>/css/style.css">
    <link rel="stylesheet" href="<?= ASSETS_PATH ?>/css/components.css">
    <link rel="stylesheet" href="<?= ASSETS_PATH ?>/css/pages.css">
    <script>
        (function(){var t=localStorage.getItem('theme');if(t)document.documentElement.setAttribute('data-theme',t)})();
    </script>
</head>
<body>
<div class="app-layout">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">📈</div>
            <div>
                <h1><?= APP_NAME ?></h1>
                <span><?= APP_TAGLINE ?></span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <?php if ($isAdminUser): ?>
            <div class="nav-section">
                <div class="nav-label">Admin</div>
                <a href="<?= BASE_URL ?>/admin/dashboard.php" class="nav-item <?= ($currentPage ?? '') === 'admin-dashboard' ? 'active' : '' ?>">
                    <span class="nav-icon">📊</span> Dashboard
                </a>
                <a href="<?= BASE_URL ?>/admin/modules.php" class="nav-item <?= ($currentPage ?? '') === 'admin-modules' ? 'active' : '' ?>">
                    <span class="nav-icon">📚</span> Modules
                </a>
                <a href="<?= BASE_URL ?>/admin/chapters.php" class="nav-item <?= ($currentPage ?? '') === 'admin-chapters' ? 'active' : '' ?>">
                    <span class="nav-icon">📄</span> Chapters
                </a>
                <a href="<?= BASE_URL ?>/admin/quizzes.php" class="nav-item <?= ($currentPage ?? '') === 'admin-quizzes' ? 'active' : '' ?>">
                    <span class="nav-icon">❓</span> Quizzes
                </a>
                <a href="<?= BASE_URL ?>/admin/users.php" class="nav-item <?= ($currentPage ?? '') === 'admin-users' ? 'active' : '' ?>">
                    <span class="nav-icon">👥</span> Users
                </a>
            </div>
            <?php endif; ?>

            <div class="nav-section">
                <div class="nav-label">Learning</div>
                <a href="<?= BASE_URL ?>/user/dashboard.php" class="nav-item <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <span class="nav-icon">🏠</span> Dashboard
                </a>
                <a href="<?= BASE_URL ?>/user/modules.php" class="nav-item <?= ($currentPage ?? '') === 'modules' ? 'active' : '' ?>">
                    <span class="nav-icon">📚</span> Modules
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-label">Account</div>
                <a href="<?= BASE_URL ?>/auth/logout.php" class="nav-item">
                    <span class="nav-icon">🚪</span> Logout
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar"><?= $initials ?></div>
                <div>
                    <div class="user-name"><?= e($user['name'] ?? 'Guest') ?></div>
                    <div class="user-role"><?= e(ucfirst($user['role'] ?? 'user')) ?></div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle">☰</button>
                <h2 class="page-title"><?= e($pageTitle ?? 'Dashboard') ?></h2>
            </div>
            <div class="topbar-right">
                <div class="search-bar">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchInput" placeholder="Search modules...">
                </div>
                <button class="theme-toggle" id="themeToggle" title="Toggle theme">🌙</button>
            </div>
        </header>

        <div class="page-content">
            <?php
            $flash = getFlash();
            if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>
