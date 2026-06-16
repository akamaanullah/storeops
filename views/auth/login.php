<?php
$title = "Sign In - " . APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/../partials/layout-head-icons.php'; ?>
    <title><?= $title ?></title>
    <!-- Google Fonts Inter, Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/style.css">
</head>
<body class="min-h-screen bg-natural-bg flex items-center justify-center p-4">

    <div class="max-w-md w-full space-y-6">
        <div id="login-form-card" class="bg-white rounded-3xl shadow-sm border border-natural-border p-8 space-y-6">
        <div class="text-center space-y-2">
            <div class="flex justify-center">
                <?php $brandLogoSize = 'login'; include __DIR__ . '/../partials/app-brand-logo.php'; ?>
            </div>
            <h1 class="text-2xl font-serif italic text-natural-heading tracking-tight"><?= htmlspecialchars(APP_NAME) ?></h1>
            <p class="text-xs text-natural-darkmute">Store operations management platform</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center space-x-2.5">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/auth/login" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
            <div class="space-y-1.5">
                <label for="username" class="text-[10px] font-bold text-natural-darkmute uppercase tracking-wider block">Login Name</label>
                <input 
                    id="username"
                    type="text" 
                    name="username"
                    required 
                    autocomplete="username"
                    placeholder="Your login name" 
                    class="w-full px-4 py-3 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 focus:border-natural-primary rounded-xl text-xs text-natural-text bg-natural-bg"
                >
            </div>

            <div class="space-y-1.5">
                <?php
                $pfId = 'password';
                $pfName = 'password';
                $pfLabel = 'Password';
                $pfLabelClass = 'text-[10px] font-bold text-natural-darkmute uppercase tracking-wider block';
                $pfRequired = true;
                $pfPlaceholder = '••••••••';
                $pfExtraClass = 'w-full px-4 py-3 pr-12 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 focus:border-natural-primary rounded-xl text-xs text-natural-text bg-natural-bg';
                include __DIR__ . '/../partials/password-field.php';
                ?>
            </div>

            <button type="submit" class="w-full py-3 bg-natural-primary hover:bg-natural-primary-hover text-white font-semibold text-xs uppercase tracking-wider rounded-xl transition-colors shadow-sm">Sign In</button>
        </form>
        </div>

        <p class="text-center text-[10px] text-natural-muted leading-relaxed">
            &copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?>. All rights reserved.<br>
            A <?= htmlspecialchars(COMPANY_NAME) ?> solution.
        </p>
    </div>
    <?php include __DIR__ . '/../partials/password-toggle-scripts.php'; ?>
</body>
</html>
