<?php
include 'includes/header.php';
include 'includes/db_connect_utem.php';

$pdo = getDB();

// Real counts from database
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalFiles    = $pdo->query("SELECT COUNT(*) FROM files")->fetchColumn();
$pdfCount      = $pdo->query("SELECT COUNT(*) FROM files WHERE fileType='PDF'")->fetchColumn();
$audioCount    = $pdo->query("SELECT COUNT(*) FROM files WHERE fileType='MP3'")->fetchColumn();
$videoCount    = $pdo->query("SELECT COUNT(*) FROM files WHERE fileType='MP4'")->fetchColumn();

// Recent 5 files
$recentFiles = $pdo->query("SELECT * FROM files ORDER BY uploadDate DESC LIMIT 5")->fetchAll();
?>

<div class="max-w-7xl mx-auto p-6 space-y-6">

    <!-- Hero -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-8 text-white">
        <div class="text-blue-200 text-sm font-semibold uppercase tracking-widest mb-2">BITP3353 · Group GW04 · UTeM 2026</div>
        <h1 class="text-4xl font-bold mb-2">MediaFind System</h1>
        <p class="text-blue-100 text-lg mb-6">
            Multimedia database retrieval supporting
            <span class="font-semibold text-white">Attribute-Based (ABR)</span>,
            <span class="font-semibold text-white">Text-Based (TBR)</span>, and
            <span class="font-semibold text-white">Content-Based (CBR)</span> search.
        </p>
        <div class="flex gap-3 flex-wrap">
            <a href="search.php" class="flex items-center gap-2 bg-white text-blue-700 font-semibold px-5 py-2.5 rounded-lg hover:bg-blue-50 transition">
                <i data-feather="search" class="w-4 h-4"></i> Search Files
            </a>
            <a href="student.php" class="flex items-center gap-2 border border-blue-300 text-white font-semibold px-5 py-2.5 rounded-lg hover:bg-blue-700 transition">
                <i data-feather="users" class="w-4 h-4"></i> View Students
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="p-5 rounded-xl border border-slate-200 shadow-sm bg-white">
            <div class="text-3xl font-bold text-slate-900 mb-1"><?= $totalFiles ?></div>
            <div class="text-sm text-slate-500">Total Files</div>
        </div>
        <div class="p-5 rounded-xl border border-slate-200 shadow-sm bg-white">
            <div class="text-3xl font-bold text-slate-900 mb-1"><?= $totalStudents ?></div>
            <div class="text-sm text-slate-500">Students</div>
        </div>
        <div class="p-5 rounded-xl border border-red-100 shadow-sm bg-white">
            <div class="text-3xl font-bold text-red-600 mb-1"><?= $pdfCount ?></div>
            <div class="text-sm text-slate-500">📄 PDF Docs</div>
        </div>
        <div class="p-5 rounded-xl border border-orange-100 shadow-sm bg-white">
            <div class="text-3xl font-bold text-orange-600 mb-1"><?= $audioCount ?></div>
            <div class="text-sm text-slate-500">🎵 Audio (MP3)</div>
        </div>
        <div class="p-5 rounded-xl border border-blue-100 shadow-sm bg-white">
            <div class="text-3xl font-bold text-blue-600 mb-1"><?= $videoCount ?></div>
            <div class="text-sm text-slate-500">🎬 Video (MP4)</div>
        </div>
    </div>

    <!-- Retrieval Methods -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="search.php?mode=ABR" class="block p-5 rounded-xl border-2 border-purple-200 bg-purple-50 hover:border-purple-400 transition">
            <div class="flex items-center gap-2 mb-2">
                <span class="bg-purple-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">ABR</span>
                <span class="font-semibold text-purple-800">Attribute-Based Retrieval</span>
            </div>
            <p class="text-xs text-purple-600">Filter by file type, size, group, or matric number</p>
        </a>
        <a href="search.php?mode=TBR" class="block p-5 rounded-xl border-2 border-green-200 bg-green-50 hover:border-green-400 transition">
            <div class="flex items-center gap-2 mb-2">
                <span class="bg-green-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">TBR</span>
                <span class="font-semibold text-green-800">Text-Based Retrieval</span>
            </div>
            <p class="text-xs text-green-600">Keyword search in names, life mottos and file names</p>
        </a>
        <a href="search.php?mode=CBR" class="block p-5 rounded-xl border-2 border-orange-200 bg-orange-50 hover:border-orange-400 transition">
            <div class="flex items-center gap-2 mb-2">
                <span class="bg-orange-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">CBR</span>
                <span class="font-semibold text-orange-800">Content-Based Retrieval</span>
            </div>
            <p class="text-xs text-orange-600">Find by mood label, video resolution or audio features</p>
        </a>
    </div>

    <!-- Recent Files -->
    <div class="rounded-xl border border-slate-200 shadow-sm bg-white">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-900">Recent Uploads</h2>
            <a href="student.php" class="text-sm text-blue-600 hover:underline">View all students →</a>
        </div>
        <table class="w-full text-left">
            <thead>
                <tr class="border-b bg-slate-50">
                    <th class="p-4 text-sm font-medium text-slate-600">File Name</th>
                    <th class="p-4 text-sm font-medium text-slate-600">Type</th>
                    <th class="p-4 text-sm font-medium text-slate-600">Student</th>
                    <th class="p-4 text-sm font-medium text-slate-600">Group</th>
                    <th class="p-4 text-sm font-medium text-slate-600">Size</th>
                    <th class="p-4 text-sm font-medium text-slate-600">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentFiles)): ?>
                <tr><td colspan="6" class="p-8 text-center text-slate-400">No files yet. Import GW04.sql first.</td></tr>
                <?php else: ?>
                <?php foreach ($recentFiles as $f):
                    $typeBadge = match(strtoupper($f['fileType'])) {
                        'PDF' => 'bg-red-100 text-red-700',
                        'MP3' => 'bg-orange-100 text-orange-700',
                        'MP4' => 'bg-blue-100 text-blue-700',
                        default => 'bg-slate-100 text-slate-600'
                    };
                ?>
                <tr class="border-b hover:bg-slate-50">
                    <td class="p-4 font-medium text-slate-900"><?= htmlspecialchars($f['fileName']) ?></td>
                    <td class="p-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $typeBadge ?>">
                            <?= htmlspecialchars($f['fileType']) ?>
                        </span>
                    </td>
                    <td class="p-4 text-slate-600 text-sm"><?= htmlspecialchars($f['studentName']) ?></td>
                    <td class="p-4">
                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-xs font-medium">
                            <?= htmlspecialchars($f['groupName']) ?>
                        </span>
                    </td>
                    <td class="p-4 text-slate-500 text-sm"><?= number_format($f['fileSizeMb'], 1) ?> MB</td>
                    <td class="p-4 text-slate-400 text-sm"><?= date('d M Y', strtotime($f['uploadDate'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Group Members -->
    <div class="rounded-xl border border-slate-200 shadow-sm bg-white p-5">
        <h2 class="font-semibold text-slate-900 mb-4">👥 Group GW04 Members</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php
            $members = [
                ['name'=>'Khairul Wajihah Binti Khairuddin','matric'=>'B032410184','role'=>'Database Designer'],
                ['name'=>'Miya Aoyon',                       'matric'=>'B032220052','role'=>'System Analyst'],
                ['name'=>'Miza Binti Mohamad Radzi',         'matric'=>'B032310641','role'=>'UI Developer'],
                ['name'=>'Muhammad Arifuddin Bin Azman',      'matric'=>'B032310638','role'=>'Backend Developer'],
            ];
            foreach ($members as $m):
            ?>
            <div class="flex items-center gap-3 p-4 rounded-lg bg-slate-50 border border-slate-100">
                <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    <?= strtoupper(substr($m['name'],0,1)) ?>
                </div>
                <div>
                    <div class="font-semibold text-slate-900 text-sm"><?= $m['name'] ?></div>
                    <div class="text-xs text-slate-500"><?= $m['matric'] ?> · <?= $m['role'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
