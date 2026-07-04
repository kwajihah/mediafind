<?php
include 'includes/header.php';
include 'includes/db_connect_utem.php';

$pdo = getDB();

// ── Helpers ──────────────────────────────────────────────
function getFileTypeBadgeColor($ft) {
    return match($ft) {
        'pdf'   => 'bg-red-100 text-red-700 border-red-200',
        'audio' => 'bg-orange-100 text-orange-700 border-orange-200',
        'video' => 'bg-blue-100 text-blue-700 border-blue-200',
        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };
}
function getFileIconData($ft) {
    return match($ft) {
        'pdf'   => ['icon'=>'file-text','color'=>'text-red-500'],
        'audio' => ['icon'=>'music',    'color'=>'text-orange-500'],
        'video' => ['icon'=>'video',    'color'=>'text-blue-500'],
        default => ['icon'=>'file',     'color'=>'text-slate-500'],
    };
}
function fileTypeLabel($ft) {
    return match($ft) { 'pdf'=>'PDF','audio'=>'MP3','video'=>'MP4', default=>strtoupper($ft) };
}

// ── Modes ─────────────────────────────────────────────────
$modes = [
    'ABR' => ['label'=>'Attribute-Based Retrieval',  'desc'=>'Filter by file type, size, upload date and student name', 'color'=>'purple'],
    'TBR' => ['label'=>'Text-Based Retrieval',        'desc'=>'Search keywords in student name, life motto and extracted PDF text', 'color'=>'green'],
    'CBR' => ['label'=>'Content-Based Retrieval',     'desc'=>'Find audio by tempo (BPM) and energy using MFCC features', 'color'=>'orange'],
];

$activeMode  = (isset($_GET['mode']) && array_key_exists($_GET['mode'], $modes)) ? $_GET['mode'] : 'ABR';
$hasSearched = isset($_GET['searched']);
$activeColor = $modes[$activeMode]['color'];

// ── Real sidebar stats ────────────────────────────────────
$dbStats = [
    'totalFiles' => $pdo->query("SELECT COUNT(*) FROM media_file")->fetchColumn(),
    'pdfCount'   => $pdo->query("SELECT COUNT(*) FROM media_file WHERE file_type='pdf'")->fetchColumn(),
    'audioCount' => $pdo->query("SELECT COUNT(*) FROM media_file WHERE file_type='audio'")->fetchColumn(),
    'videoCount' => $pdo->query("SELECT COUNT(*) FROM media_file WHERE file_type='video'")->fetchColumn(),
];

// ── Queries ───────────────────────────────────────────────
$searchResults = [];

if ($hasSearched) {

    // ════════════════════════════════════════════════════
    // ABR — Attribute-Based Retrieval
    // Filters: file_type, student_name, file_size range, upload_file date range
    // ════════════════════════════════════════════════════
    if ($activeMode === 'ABR') {
        $conditions = ["1=1"];
        $params     = [];

        if (!empty($_GET['file_type'])) {
            $conditions[] = "mf.file_type = ?";
            $params[]     = $_GET['file_type'];
        }
        if (!empty($_GET['student_name'])) {
            $conditions[] = "s.student_name LIKE ?";
            $params[]     = '%' . $_GET['student_name'] . '%';
        }
        if (isset($_GET['min_size']) && $_GET['min_size'] !== '') {
            // UI shows MB → convert to bytes for query
            $conditions[] = "mf.file_size >= ?";
            $params[]     = (float)$_GET['min_size'] * 1048576;
        }
        if (isset($_GET['max_size']) && $_GET['max_size'] !== '') {
            $conditions[] = "mf.file_size <= ?";
            $params[]     = (float)$_GET['max_size'] * 1048576;
        }
        if (!empty($_GET['date_from'])) {
            $conditions[] = "mf.upload_file >= ?";
            $params[]     = $_GET['date_from'] . ' 00:00:00';
        }
        if (!empty($_GET['date_to'])) {
            $conditions[] = "mf.upload_file <= ?";
            $params[]     = $_GET['date_to'] . ' 23:59:59';
        }

        $where = implode(' AND ', $conditions);
        $sql = "
            SELECT mf.file_id, mf.file_name, mf.file_type,
                   mf.file_size, mf.upload_file, mf.life_motto,
                   s.student_name, s.matric_no, s.group_name, s.phone
            FROM media_file mf
            JOIN student s ON mf.student_id = s.studentID
            WHERE {$where}
            ORDER BY mf.upload_file DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $searchResults = $stmt->fetchAll();
    }

    // ════════════════════════════════════════════════════
    // TBR — Text-Based Retrieval
    // Single keyword across student_name, life_motto, file_name, extracted_text
    // ════════════════════════════════════════════════════
    elseif ($activeMode === 'TBR') {
        $keyword = trim($_GET['keyword'] ?? '');
        if ($keyword !== '') {
            $p = "%{$keyword}%";
            $sql = "
                SELECT mf.file_id, mf.file_name, mf.file_type,
                       mf.file_size, mf.upload_file, mf.life_motto,
                       s.student_name, s.matric_no, s.group_name, s.phone,
                       pt.extracted_text
                FROM media_file mf
                JOIN student s ON mf.student_id = s.studentID
                LEFT JOIN pdf_text pt ON mf.file_id = pt.file_id
                WHERE s.student_name    LIKE ?
                   OR mf.life_motto     LIKE ?
                   OR mf.file_name      LIKE ?
                   OR pt.extracted_text LIKE ?
                ORDER BY mf.upload_file DESC
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$p, $p, $p, $p]);
            $searchResults = $stmt->fetchAll();
        }
    }

    // ════════════════════════════════════════════════════
    // CBR — Content-Based Retrieval
    // Filters: tempo range (BPM), energy range — joins audio_features
    // ════════════════════════════════════════════════════
    elseif ($activeMode === 'CBR') {
        $conditions = ["1=1"];
        $params     = [];

        if (isset($_GET['tempo_min']) && $_GET['tempo_min'] !== '') {
            $conditions[] = "af.tempo >= ?";
            $params[]     = (float)$_GET['tempo_min'];
        }
        if (isset($_GET['tempo_max']) && $_GET['tempo_max'] !== '') {
            $conditions[] = "af.tempo <= ?";
            $params[]     = (float)$_GET['tempo_max'];
        }
        if (isset($_GET['energy_min']) && $_GET['energy_min'] !== '') {
            $conditions[] = "af.energy >= ?";
            $params[]     = (float)$_GET['energy_min'];
        }
        if (isset($_GET['energy_max']) && $_GET['energy_max'] !== '') {
            $conditions[] = "af.energy <= ?";
            $params[]     = (float)$_GET['energy_max'];
        }

        $where = implode(' AND ', $conditions);
        $sql = "
            SELECT mf.file_id, mf.file_name, mf.file_type,
                   mf.file_size, mf.upload_file, mf.life_motto,
                   s.student_name, s.matric_no, s.group_name, s.phone,
                   af.tempo, af.energy,
                   af.mfcc1, af.mfcc2, af.mfcc3, af.mfcc4, af.mfcc5
            FROM media_file mf
            JOIN student s ON mf.student_id = s.studentID
            JOIN audio_features af ON mf.file_id = af.file_id
            WHERE {$where}
            ORDER BY af.tempo ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $searchResults = $stmt->fetchAll();
    }
}
?>

<div class="space-y-6 max-w-7xl mx-auto p-6">

    <!-- Page Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-8 text-white">
        <h1 class="text-3xl font-bold mb-1">Search Multimedia Submissions</h1>
        <p class="text-blue-100">BITP3353 — Multimedia Database Systems &nbsp;|&nbsp; ABR · TBR · CBR Retrieval</p>
    </div>

    <!-- Mode Selector -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <?php foreach ($modes as $key => $data):
            $isActive  = ($activeMode === $key);
            $c         = $data['color'];
            $borderCls = $isActive ? "border-{$c}-500 bg-{$c}-50" : "border-slate-200 bg-white hover:border-slate-300";
            $badgeCls  = $isActive ? "bg-{$c}-600 text-white" : "bg-slate-200 text-slate-700";
            $textCls   = $isActive ? "text-{$c}-800" : "text-slate-700";
            $descCls   = $isActive ? "text-{$c}-600" : "text-slate-500";
        ?>
        <a href="search.php?mode=<?= $key ?>" class="block p-4 rounded-xl border-2 transition-all <?= $borderCls ?>">
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $badgeCls ?>"><?= $key ?></span>
                <span class="font-semibold text-sm <?= $textCls ?>"><?= $data['label'] ?></span>
            </div>
            <p class="text-xs <?= $descCls ?>"><?= $data['desc'] ?></p>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- ════ ABR FILTER FORM ════ -->
    <?php if ($activeMode === 'ABR'): ?>
    <div class="p-5 rounded-xl border border-slate-200 shadow-sm bg-white">
        <form method="GET" action="search.php">
            <input type="hidden" name="mode" value="ABR">
            <input type="hidden" name="searched" value="1">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">File Type</label>
                    <select name="file_type" class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Any Type</option>
                        <option value="pdf"   <?= ($_GET['file_type']??'')==='pdf'   ?'selected':'' ?>>📄 PDF Document</option>
                        <option value="audio" <?= ($_GET['file_type']??'')==='audio' ?'selected':'' ?>>🎵 Audio (MP3)</option>
                        <option value="video" <?= ($_GET['file_type']??'')==='video' ?'selected':'' ?>>🎬 Video (MP4)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Student Name</label>
                    <input type="text" name="student_name"
                           value="<?= htmlspecialchars($_GET['student_name']??'') ?>"
                           placeholder="e.g. Khairul, Miya..."
                           class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Min Size (MB)</label>
                        <input type="number" step="0.1" name="min_size"
                               value="<?= htmlspecialchars($_GET['min_size']??'') ?>"
                               placeholder="e.g. 1"
                               class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Max Size (MB)</label>
                        <input type="number" step="0.1" name="max_size"
                               value="<?= htmlspecialchars($_GET['max_size']??'') ?>"
                               placeholder="e.g. 100"
                               class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Upload Date From</label>
                    <input type="date" name="date_from"
                           value="<?= htmlspecialchars($_GET['date_from']??'') ?>"
                           class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Upload Date To</label>
                    <input type="date" name="date_to"
                           value="<?= htmlspecialchars($_GET['date_to']??'') ?>"
                           class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

            </div>
            <div class="flex gap-3">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 transition">
                    <i data-feather="filter" class="w-4 h-4"></i> Apply Filters
                </button>
                <a href="search.php?mode=ABR" class="inline-flex items-center gap-2 px-5 py-2 border border-slate-200 rounded-md text-sm text-slate-600 hover:bg-slate-100 transition">
                    <i data-feather="x" class="w-4 h-4"></i> Clear
                </a>
            </div>
        </form>
    </div>

    <!-- ════ TBR FILTER FORM ════ -->
    <?php elseif ($activeMode === 'TBR'): ?>
    <div class="p-5 rounded-xl border border-slate-200 shadow-sm bg-white">
        <form method="GET" action="search.php" class="flex gap-3">
            <input type="hidden" name="mode" value="TBR">
            <input type="hidden" name="searched" value="1">
            <div class="flex-1 relative">
                <i data-feather="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="text" name="keyword"
                       value="<?= htmlspecialchars($_GET['keyword']??'') ?>"
                       placeholder="Enter keyword (e.g. database, Johor, machine learning, human)…"
                       class="w-full border border-slate-200 rounded-md pl-10 pr-4 py-2 h-11 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="inline-flex items-center gap-2 px-7 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 transition h-11">
                <i data-feather="search" class="w-4 h-4"></i> Search
            </button>
            <a href="search.php?mode=TBR" class="inline-flex items-center gap-2 px-5 py-2 border border-slate-200 rounded-md text-sm text-slate-600 hover:bg-slate-100 transition h-11">
                <i data-feather="x" class="w-4 h-4"></i> Clear
            </a>
        </form>
    </div>

    <!-- ════ CBR FILTER FORM ════ -->
    <?php elseif ($activeMode === 'CBR'): ?>
    <div class="p-5 rounded-xl border border-slate-200 shadow-sm bg-white">
        <form method="GET" action="search.php">
            <input type="hidden" name="mode" value="CBR">
            <input type="hidden" name="searched" value="1">
            <p class="text-xs text-slate-500 mb-4">
                Filter audio files by their extracted content features from the <strong>audio_features</strong> table.
                Leave a field empty to ignore that filter.
            </p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Tempo Min (BPM)</label>
                    <input type="number" step="0.01" name="tempo_min"
                           value="<?= htmlspecialchars($_GET['tempo_min']??'') ?>"
                           placeholder="e.g. 60"
                           class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Tempo Max (BPM)</label>
                    <input type="number" step="0.01" name="tempo_max"
                           value="<?= htmlspecialchars($_GET['tempo_max']??'') ?>"
                           placeholder="e.g. 150"
                           class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Energy Min (0–1)</label>
                    <input type="number" step="0.0001" name="energy_min"
                           value="<?= htmlspecialchars($_GET['energy_min']??'') ?>"
                           placeholder="e.g. 0.03"
                           class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Energy Max (0–1)</label>
                    <input type="number" step="0.0001" name="energy_max"
                           value="<?= htmlspecialchars($_GET['energy_max']??'') ?>"
                           placeholder="e.g. 0.10"
                           class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 bg-orange-500 text-white rounded-md text-sm font-medium hover:bg-orange-600 transition">
                    <i data-feather="music" class="w-4 h-4"></i> Retrieve Audio
                </button>
                <a href="search.php?mode=CBR" class="inline-flex items-center gap-2 px-5 py-2 border border-slate-200 rounded-md text-sm text-slate-600 hover:bg-slate-100 transition">
                    <i data-feather="x" class="w-4 h-4"></i> Clear
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- ════ RESULTS + SIDEBAR ════ -->
    <div class="grid lg:grid-cols-3 gap-6">

        <!-- Results -->
        <div class="lg:col-span-2 space-y-4">

            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-slate-900">
                    <?= $hasSearched ? "Results (" . count($searchResults) . ")" : "Set filters above and click Retrieve" ?>
                </h2>
                <?php if ($hasSearched): ?>
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-<?= $activeColor ?>-100 text-<?= $activeColor ?>-700 border border-<?= $activeColor ?>-200">
                        <?= $activeMode ?> — <?= $modes[$activeMode]['label'] ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php if (!$hasSearched): ?>
                <div class="p-12 rounded-xl border border-slate-200 bg-white text-center">
                    <i data-feather="search" class="w-12 h-12 text-slate-300 mx-auto mb-4"></i>
                    <p class="text-slate-600">Use the filters above to retrieve files.</p>
                </div>

            <?php elseif (count($searchResults) === 0): ?>
                <div class="p-12 rounded-xl border border-slate-200 bg-white text-center">
                    <i data-feather="search" class="w-12 h-12 text-slate-300 mx-auto mb-4"></i>
                    <p class="text-slate-600">No results found. Try adjusting your filters.</p>
                </div>

            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($searchResults as $r):
                        $iconData  = getFileIconData($r['file_type'] ?? '');
                        $badgeColor = getFileTypeBadgeColor($r['file_type'] ?? '');
                        $sizeMb     = number_format(($r['file_size'] ?? 0) / 1048576, 2);
                    ?>
                    <div class="p-5 rounded-xl border border-slate-200 shadow-sm bg-white hover:shadow-md transition-shadow">
                        <div class="flex gap-4">
                            <!-- Icon + badge -->
                            <div class="flex-shrink-0 flex flex-col items-center gap-1">
                                <i data-feather="<?= $iconData['icon'] ?>" class="w-10 h-10 <?= $iconData['color'] ?>"></i>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full border <?= $badgeColor ?>">
                                    <?= fileTypeLabel($r['file_type'] ?? '') ?>
                                </span>
                            </div>
                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-slate-900 truncate">
                                    <?= htmlspecialchars($r['file_name'] ?? 'Unnamed File') ?>
                                </h3>
                                <p class="text-sm text-slate-700 mb-2">
                                    <?= htmlspecialchars($r['student_name'] ?? '—') ?>
                                    <span class="mx-1 text-slate-300">·</span>
                                    <span class="text-slate-500"><?= htmlspecialchars($r['matric_no'] ?? '') ?></span>
                                </p>

                                <div class="flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-500">
                                    <span>
                                        <span class="font-medium text-slate-600">Group:</span>
                                        <span class="ml-1 px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-md font-semibold">
                                            <?= htmlspecialchars($r['group_name'] ?? '') ?>
                                        </span>
                                    </span>
                                    <?php if (!empty($r['phone'])): ?>
                                        <span class="flex items-center gap-1">
                                            <i data-feather="phone" class="w-3 h-3"></i>
                                            <?= htmlspecialchars($r['phone']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($r['upload_file'])): ?>
                                        <span class="flex items-center gap-1">
                                            <i data-feather="calendar" class="w-3 h-3"></i>
                                            <?= date('d M Y', strtotime($r['upload_file'])) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($r['file_size'])): ?>
                                        <span><span class="font-medium text-slate-600">Size:</span> <?= $sizeMb ?> MB</span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($r['life_motto'])): ?>
                                    <div class="flex items-start gap-1 mt-2 text-xs text-slate-500 italic">
                                        <i data-feather="quote" class="w-3 h-3 mt-0.5 flex-shrink-0"></i>
                                        <span><?= htmlspecialchars($r['life_motto']) ?></span>
                                    </div>
                                <?php endif; ?>

                                <!-- TBR: extracted text snippet -->
                                <?php if ($activeMode === 'TBR' && !empty($r['extracted_text'])): ?>
                                    <div class="mt-2 p-2 bg-green-50 border border-green-100 rounded text-xs text-green-700">
                                        <span class="font-semibold">Extracted text: </span>
                                        <?= htmlspecialchars(substr($r['extracted_text'], 0, 200)) ?>...
                                    </div>
                                <?php endif; ?>

                                <!-- CBR: audio feature values -->
                                <?php if ($activeMode === 'CBR' && isset($r['tempo'])): ?>
                                    <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                        <span class="px-2 py-0.5 bg-orange-50 text-orange-700 border border-orange-200 rounded font-mono">
                                            Tempo: <?= number_format($r['tempo'], 2) ?> BPM
                                        </span>
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded font-mono">
                                            Energy: <?= number_format($r['energy'], 4) ?>
                                        </span>
                                        <span class="px-2 py-0.5 bg-slate-50 text-slate-600 border border-slate-200 rounded font-mono">
                                            MFCC1: <?= number_format($r['mfcc1'] ?? 0, 2) ?>
                                        </span>
                                        <span class="px-2 py-0.5 bg-slate-50 text-slate-600 border border-slate-200 rounded font-mono">
                                            MFCC2: <?= number_format($r['mfcc2'] ?? 0, 2) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
            <!-- Stats -->
            <div class="p-5 rounded-xl border border-slate-200 shadow-sm bg-white">
                <h3 class="font-semibold text-slate-900 mb-4">System Overview</h3>
                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-sm text-slate-600">Total Files</span>
                        <span class="font-semibold text-slate-900"><?= $dbStats['totalFiles'] ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-sm text-slate-600">PDF Documents</span>
                        <span class="font-semibold text-red-600"><?= $dbStats['pdfCount'] ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-sm text-slate-600">Audio Files (MP3)</span>
                        <span class="font-semibold text-orange-600"><?= $dbStats['audioCount'] ?></span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-slate-600">Video Files (MP4)</span>
                        <span class="font-semibold text-blue-600"><?= $dbStats['videoCount'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Mode hint -->
            <?php if ($activeMode === 'ABR'): ?>
            <div class="p-4 rounded-xl border border-purple-200 bg-purple-50 text-xs text-purple-700">
                <p class="font-semibold mb-1">💡 ABR Tips</p>
                <ul class="space-y-1 list-disc pl-4">
                    <li>Leave all fields empty → shows all files</li>
                    <li>Select <strong>Audio</strong> → all MP3 files</li>
                    <li>Enter a student name to filter their files</li>
                    <li>Use date range to find recent uploads</li>
                </ul>
            </div>
            <?php elseif ($activeMode === 'TBR'): ?>
            <div class="p-4 rounded-xl border border-green-200 bg-green-50 text-xs text-green-700">
                <p class="font-semibold mb-1">💡 TBR Tips</p>
                <ul class="space-y-1 list-disc pl-4">
                    <li>Try: <strong>database</strong>, <strong>Johor</strong></li>
                    <li>Try: <strong>machine learning</strong>, <strong>ABR</strong></li>
                    <li>Searches PDF extracted text, file names and life mottos</li>
                </ul>
            </div>
            <?php elseif ($activeMode === 'CBR'): ?>
            <div class="p-4 rounded-xl border border-orange-200 bg-orange-50 text-xs text-orange-700">
                <p class="font-semibold mb-1">💡 CBR Tips</p>
                <ul class="space-y-1 list-disc pl-4">
                    <li>Tempo range: try <strong>60–100</strong> BPM for slow audio</li>
                    <li>Tempo range: try <strong>120–145</strong> BPM for fast audio</li>
                    <li>Energy range: try <strong>0.03–0.06</strong> for soft tracks</li>
                    <li>Leave all empty → shows all audio with features</li>
                </ul>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>feather.replace();</script>
<?php include 'includes/footer.php'; ?>
