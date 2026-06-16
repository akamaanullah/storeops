<?php
/**
 * Global Header View Template (layout/header.php)
 */
use App\Core\RoleLabels;

$currentUser = Auth::user();

// Determine active navigation links dynamically
$currentPath = defined('CURRENT_ROUTE') ? CURRENT_ROUTE : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include dirname(__DIR__, 2) . '/partials/layout-head-icons.php'; ?>
    <title><?= htmlspecialchars($title ?? APP_NAME) ?></title>
    <!-- Google Fonts: Inter & Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/style.css">
</head>
<body class="min-h-screen text-natural-text flex flex-col md:flex-row bg-natural-bg pb-20 md:pb-0">

    <?php if ($currentUser): ?>
        <!-- Left Sidebar - Visible on Desktop only -->
        <aside class="app-sidebar hidden md:flex flex-col w-[17rem] h-screen sticky top-0 border-r border-natural-border justify-between py-5 px-3 shrink-0 z-50">
            <!-- Brand Section -->
            <div class="flex flex-col flex-1 min-h-0 gap-5">
                <a href="<?= BASE_URL ?>/" class="app-sidebar__brand focus:outline-none">
                    <?php $brandLogoSize = 'sidebar'; include dirname(__DIR__, 2) . '/partials/app-brand-logo.php'; ?>
                    <span class="app-sidebar__brand-name"><?= htmlspecialchars(APP_NAME) ?></span>
                </a>
                
                <div class="flex flex-col flex-1 overflow-y-auto min-h-0 app-sidebar__nav-scroll">
                    <?php include dirname(__DIR__, 2) . '/partials/team-lead-sidebar-content.php'; ?>
                </div>
            </div>

            <!-- Sidebar Bottom Section -->
            <div class="app-sidebar__footer space-y-3">
                <a href="<?= BASE_URL ?>/auth/logout" class="sidebar-nav__logout" title="Sign out">
                    <span class="sidebar-nav__icon-wrap sidebar-nav__icon-wrap--muted">
                        <svg class="sidebar-nav__svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </span>
                    <span class="sidebar-nav__label">Sign Out</span>
                </a>
                <?php include dirname(__DIR__, 2) . '/partials/layout-sidebar-branding.php'; ?>
            </div>
        </aside>
    <?php endif; ?>

    <!-- Main Content Frame (Occupies right side on desktop) -->
    <div class="flex-1 flex flex-col min-h-screen overflow-x-hidden">
        
        <?php if ($currentUser): ?>
            <!-- Top Header Bar (Visible on all screens) -->
            <header class="bg-white border-b border-natural-border h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 sticky top-0 z-40 shrink-0">
                <!-- Left Section: Mobile logo only -->
                <div class="flex items-center space-x-4">
                    <!-- Mobile logo (hidden on desktop) -->
                    <a href="<?= BASE_URL ?>/" class="md:hidden flex items-center space-x-2 focus:outline-none">
                        <?php $brandLogoSize = 'mobile'; include dirname(__DIR__, 2) . '/partials/app-brand-logo.php'; ?>
                        <span class="font-serif italic text-natural-heading text-sm tracking-tight"><?= htmlspecialchars(APP_NAME) ?></span>
                    </a>
                </div>
                
                <!-- Right Section: Clock, Notification bell, profile info, and mobile logout -->
                <div class="flex items-center space-x-4">
                    <!-- Notification Bell and Dropdown -->
                    <div class="relative">
                        <button id="notification-bell-btn" class="relative p-2 text-natural-darkmute hover:text-natural-primary transition-colors focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <!-- Alert count indicator -->
                            <?php include dirname(__DIR__, 2) . '/partials/notification-bell-badge.php'; ?>
                        </button>

                        <!-- Notifications Menu Panel -->
                        <div id="notification-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white border border-natural-border rounded-xl shadow-lg z-50 py-2 max-h-96 flex flex-col">
                            <div class="px-4 py-2 border-b border-natural-border flex justify-between items-center bg-natural-pane">
                                <span class="font-semibold text-xs text-natural-heading">Notifications Inbox</span>
                                <button onclick="markAllNotificationsRead()" class="text-[10px] text-natural-primary font-bold hover:underline">Mark read</button>
                            </div>
                            <div id="notification-items-list" class="overflow-y-auto flex-1 divide-y divide-natural-border">
                                <div class="p-4 text-center text-xs text-natural-muted">Loading inbox...</div>
                            </div>
                            <div class="px-4 py-2 border-t border-natural-border text-center bg-natural-pane/30">
                                <a href="<?= BASE_URL ?>/notifications/inbox" class="text-[10px] text-natural-primary font-bold hover:underline block">View all notifications</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Settings Panel -->
                    <div class="flex items-center space-x-2 pl-2 border-l border-natural-border">
                        <div class="w-8 h-8 rounded-full bg-natural-pane border border-natural-border flex items-center justify-center text-natural-primary font-mono font-bold text-xs uppercase">
                            <?= htmlspecialchars(substr(trim($currentUser['name']), 0, 2)) ?>
                        </div>
                        <div class="hidden sm:block text-left">
                            <span class="block font-semibold text-xs text-natural-heading leading-none"><?= htmlspecialchars($currentUser['name']) ?></span>
                            <span class="block text-[9px] text-natural-muted uppercase font-bold mt-1 tracking-wider leading-none"><?= htmlspecialchars(RoleLabels::label($currentUser['role'] ?? null)) ?></span>
                        </div>
                        <!-- Mobile-only logout button -->
                        <a href="<?= BASE_URL ?>/auth/logout" class="md:hidden p-1.5 text-natural-darkmute hover:text-rose-500 rounded-lg focus:outline-none transition-colors" title="Sign out">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </header>
        <?php endif; ?>

        <!-- Main Container Content area -->
        <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8 pb-20 md:pb-8">
            <?php include dirname(__DIR__, 2) . '/partials/flash-messages.php'; ?>
