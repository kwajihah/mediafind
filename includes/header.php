<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediaFind System — GW04</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="bg-zinc-50 min-h-screen font-sans">

<!-- Navigation Bar -->
<nav class="bg-white border-b border-slate-200 shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-3 flex items-center justify-between">
        <a href="index.php" class="flex items-center gap-2 font-bold text-xl text-slate-800">
            <i data-feather="database" class="w-5 h-5 text-blue-600"></i>
            Media<span class="text-blue-600">Find</span>
        </a>
        <div class="flex items-center gap-1">
            <?php $current = basename($_SERVER['PHP_SELF']); ?>
            <a href="index.php"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                      <?= $current === 'index.php' ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100' ?>">
                Dashboard
            </a>
            <a href="student.php"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                      <?= $current === 'student.php' ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100' ?>">
                Students
            </a>
            <a href="search.php"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                      <?= $current === 'search.php' ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100' ?>">
                Search (ABR/TBR/CBR)
            </a>
        </div>
        <span class="text-xs text-slate-400 font-medium">BITP3353 · GW04 · UTeM</span>
    </div>
</nav>
