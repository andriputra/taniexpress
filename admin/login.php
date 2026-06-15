<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (currentUser() && currentUser()['role'] === 'admin') {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login($_POST['email'] ?? '', $_POST['password'] ?? '') && currentUser()['role'] === 'admin') {
        flash('success', 'Selamat datang, Admin!');
        redirect('index.php');
    }
    $error = 'Kredensial admin tidak valid.';
}

if ($error) pushToast('error', $error);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login Admin - <?= e(APP_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" rel="stylesheet"/>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#0f5238','primary-container':'#2d6a4f','leaf-green-light':'#D8F3DC','text-main':'#1B4332','text-muted':'#52796F','error-red':'#BC4749','outline-variant':'#bfc9c1','surface-container-low':'#f3f4f3'}}}}</script>
    <style>
        body{font-family:'Inter',sans-serif}
        .tonal-shadow{box-shadow:0 4px 20px -2px rgba(45,106,79,.08)}
        .input-field,input.input-field,select.input-field,textarea.input-field{width:100%;background:#fff;border:1px solid #bfc9c1;padding:.875rem 1.25rem;font-size:.875rem;outline:none;transition:all .2s;border-radius:9999px!important}
        textarea.input-field,select.input-field{border-radius:1.25rem!important}
        .input-field:focus{border-color:#0f5238;box-shadow:0 0 0 3px rgba(15,82,56,.15)}
        .btn-primary{display:inline-flex;align-items:center;justify-content:center;width:100%;padding:.875rem 1.5rem;background:#0f5238;color:#fff;border-radius:9999px;font-weight:600;transition:all .2s}
        .btn-primary:hover{background:#2d6a4f}
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-[#f9f9f8] px-4">
    <div class="w-full max-w-md bg-white rounded-3xl tonal-shadow p-8 border border-[#bfc9c1]/30">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-[#0f5238]"><?= e(APP_NAME) ?></h1>
            <p class="text-[#52796F] text-sm mt-1">Panel Administrator</p>
        </div>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-text-muted mb-1.5">Email Admin</label>
                <input type="email" name="email" required class="input-field"/>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-muted mb-1.5">Password</label>
                <input type="password" name="password" required class="input-field"/>
            </div>
            <button type="submit" class="btn-primary">Masuk Admin</button>
        </form>
        <p class="text-center text-xs text-gray-400 mt-6"><a href="../index.php" class="text-[#0f5238]">← Kembali ke Toko</a></p>
    </div>
    <?php renderUiShell(); renderUiScripts(); ?>
</body>
</html>
