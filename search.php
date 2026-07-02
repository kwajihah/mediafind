<?php
// =============================================================
// search.php — MediaFind ABR / TBR / CBR Search Module
// Group GW04 · BITP3353 Multimedia Database · UTeM 2026
//
// RETRIEVAL MODES:
//   ABR — Attribute-Based Retrieval  : filter by file/student attributes
//   TBR — Text-Based Retrieval       : keyword search across text fields
//   CBR — Content-Based Retrieval    : search by audio/video content features
//
// FILTERS (applied across all modes):
//   - fileType   : PDF | MP3 | MP4 | Any
//   - dateFrom   : uploadDate >=
//   - dateTo     : uploadDate <=
//   - sizeMin    : fileSizeMb >=
//   - sizeMax    : fileSizeMb <=
// =============================================================

include 'includes/header.php';
include 'includes/db_connect_utem.php';

$pdo = getDB();

// =============================================================
// SECTION 1 — PHP UI HELPER FUNCTIONS
// These replace the React component color/icon logic
// =============================================================

/**
 * Returns Tailwind badge classes based on file type.
 * Used to colour-code PDF / MP3 / MP4 badges in results.
 */
function getFileTypeBadgeColor(string $fileType): string {
    switch (strtoupper($fileType)) {
        case 'PDF': return 'bg-red-100 text-red-700 border-red-200';
        case 'MP3': return 'bg-orange-100 text-orange-700 border-orange-200';
        case 'MP4': return 'bg-blue-100 text-blue-700 border-blue-200';
        default:    return 'bg-slate-100 text-slate-700 border-slate-200';
    }
}

/**
 * Returns the Feather icon name and colour class for a given file type.
 * Used to show the correct icon next to each search result card.
 */
function getFileIconData(string $fileType): array {
    switch (strtoupper($fileType)) {
        case 'PDF': return ['icon' => 'file-text', 'color' => 'text-red-500'];
        case 'MP3': return ['icon' => 'music',     'color' => 'text-orange-500'];
        case 'MP4': return ['icon' => 'video',     'color' => 'text-blue-500'];
        default:    return ['icon' => 'file-text', 'color' => 'text-slate-500'];
    }
}

// =============================================================
// SECTION 2 — MODE CONFIGURATION
// Defines the three retrieval modes, their labels and colours.
// =============================================================
$modes = [
    'ABR' => [
        'label' => 'Attribute-Based Retrieval',
        'desc'  => 'Filter by file properties: type, size, date, group',
        'color' => 'purple'
    ],
    'TBR' => [
        'label' => 'Text-Based Retrieval',
        'desc'  => 'Search keywords in names, mottos, and PDF content',
        'color' => 'green'
    ],
    'CBR' => [
        'label' => 'Content-Based Retrieval',
        'desc'  => 'Find files by audio duration, mood, or video resolution',
        'color' => 'orange'
    ]
];

// =============================================================
// SECTION 3 — INPUT CAPTURE
// Collect and sanitise all GET parameters from the search form.
// =============================================================

// Active retrieval mode — default to TBR
$activeMode = (isset($_GET['mode']) && array_key_exists($_GET['mode'], $modes))
    ? $_GET['mode']
    : 'TBR';

// Main keyword
$keyword    = isset($_GET['keyword'])  ? trim($_GET['keyword'])  : '';
$hasSearched = isset($_GET['keyword']); // True even on empty keyword (shows "no results")

// --- Advanced Filters ---
// File type filter: PDF | MP3 | MP4 | '' (any)
$filterType    = isset($_GET['filterType'])  ? trim($_GET['filterType'])  : '';
// Date range filter (YYYY-MM-DD)
$filterDateFrom = isset($_GET['dateFrom'])   ? trim($_GET['dateFrom'])    : '';
$filterDateTo   = isset($_GET['dateTo'])     ? trim($_GET['dateTo'])      : '';
// File size range filter (MB)
$filterSizeMin  = isset($_GET['sizeMin'])    ? trim($_GET['sizeMin'])     : '';
$filterSizeMax  = isset($_GET['sizeMax'])    ? trim($_GET['sizeMax'])     : '';

// =============================================================
// SECTION 4 — DATABASE QUERY BUILDER
// Builds a parameterised PDO query based on active mode + filters.
// All user input goes through prepared statements — no SQL injection.
// =============================================================

$searchResults = [];

if ($hasSearched) {

    // Base query — joins files with nothing extra since files table
    // already stores denormalised student columns for fast lookup.
    $sql    = "SELECT * FROM files WHERE 1=1";
    $params = [];

    // ----------------------------------------------------------
    // 4A — MODE-SPECIFIC WHERE CLAUSES
    // ----------------------------------------------------------

    if ($keyword !== '') {
        if ($activeMode === 'ABR') {
            // Attribute-Based: match exact attributes OR partial file name.
            // matricNo and groupName are exact-match (identifiers).
            // fileName uses LIKE for partial matching convenience.
            $sql .= " AND (
                        fileName  LIKE ?
                     OR matricNo  = ?
                     OR groupName = ?
                     OR fileType  = ?
                    )";
            $likeName = "%{$keyword}%";
            array_push($params, $likeName, $keyword, $keyword, strtoupper($keyword));

        } elseif ($activeMode === 'TBR') {
            // Text-Based: broad LIKE search across all text/content fields.
            // Covers file name, student name, life motto, and file feature text.
            $like = "%{$keyword}%";
            $sql .= " AND (
                        fileName    LIKE ?
                     OR studentName LIKE ?
                     OR lifeMotto   LIKE ?
                     OR fileFeature LIKE ?
                    )";
            array_push($params, $like, $like, $like, $like);

        } elseif ($activeMode === 'CBR') {
            // Content-Based: search audio mood label, video resolution,
            // and the general fileFeature field (e.g. MFCC descriptions).
            $like = "%{$keyword}%";
            $sql .= " AND (
                        moodLabel       LIKE ?
                     OR videoResolution LIKE ?
                     OR fileFeature     LIKE ?
                    )";
            array_push($params, $like, $like, $like);
        }
    }

    // ----------------------------------------------------------
    // 4B — SHARED ADVANCED FILTER CLAUSES
    // These apply on top of any mode — narrowing results further.
    // ----------------------------------------------------------

    // Filter by file type (PDF | MP3 | MP4)
    if ($filterType !== '') {
        $sql .= " AND fileType = ?";
        $params[] = strtoupper($filterType);
    }

    // Filter by upload date range
    if ($filterDateFrom !== '') {
        $sql .= " AND DATE(uploadDate) >= ?";
        $params[] = $filterDateFrom;
    }
    if ($filterDateTo !== '') {
        $sql .= " AND DATE(uploadDate) <= ?";
        $params[] = $filterDateTo;
    }

    // Filter by file size range (MB)
    if ($filterSizeMin !== '' && is_numeric($filterSizeMin)) {
        $sql .= " AND fileSizeMb >= ?";
        $params[] = (float) $filterSizeMin;
    }
    if ($filterSizeMax !== '' && is_numeric($filterSizeMax)) {
        $sql .= " AND fileSizeMb <= ?";
        $params[] = (float) $filterSizeMax;
    }

    // Default sort: newest first
    $sql .= " ORDER BY uploadDate DESC";

    // Execute the prepared statement
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $searchResults = $stmt->fetchAll();
}

// =============================================================
// SECTION 5 — REAL DATABASE STATS
// Replace dummy hardcoded numbers with live COUNT queries.
// =============================================================
$dbStats = [
    'totalFiles'  => (int) $pdo->query("SELECT COUNT(*) FROM files")->fetchColumn(),
    'pdfCount'    => (int) $pdo->query("SELECT COUNT(*) FROM files WHERE fileType = 'PDF'")->fetchColumn(),
    'audioCount'  => (int) $pdo->query("SELECT COUNT(*) FROM files WHERE fileType = 'MP3'")->fetchColumn(),
    'videoCount'  => (int) $pdo->query("SELECT COUNT(*) FROM files WHERE fileType = 'MP4'")->fetchColumn(),
];

// Check whether any advanced filter is currently active
// Used to show/hide the advanced filter panel as expanded by default
$hasActiveFilter = ($filterType !== '' || $filterDateFrom !== '' || $filterDateTo !== '' || $filterSizeMin !== '' || $filterSizeMax !== '');

$activeColor = $modes[$activeMode]['color'];
?>

<!-- =============================================================
     HTML OUTPUT — Structure preserved exactly from original.
     Only additions: Advanced Filters panel inserted after the
     main search bar, and System Overview stats are now live.
     ============================================================= -->

<div class="space-y-6 max-w-7xl mx-auto p-6">

    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-8 text-white">
        <h1 class="text-3xl font-bold mb-1">Search Multimedia Submissions</h1>
        <p class="text-blue-100">
            BITP3353 — Multimedia Database Systems &nbsp;|&nbsp; Dataset: bitp3353.utem.edu.my/2026/all
        </p>
    </div>

    <!-- Retrieval Mode Selector (ABR / TBR / CBR) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <?php foreach ($modes as $key => $data):
            $isActive   = ($activeMode === $key);
            $color      = $data['color'];
            $borderClass = $isActive ? "border-{$color}-500 bg-{$color}-50"     : "border-slate-200 bg-white hover:border-slate-300";
            $badgeClass  = $isActive ? "bg-{$color}-600 text-white border-{$color}-600" : "bg-slate-200 text-slate-700 border-slate-200";
            $textClass   = $isActive ? "text-{$color}-800"  : "text-slate-700";
            $descClass   = $isActive ? "text-{$color}-600"  : "text-slate-500";
        ?>
        <a href="search.php?mode=<?= $key ?>" class="block text-left p-4 rounded-xl border-2 transition-all <?= $borderClass ?>">
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold <?= $badgeClass ?>">
                    <?= $key ?>
                </span>
                <span class="font-semibold text-sm <?= $textClass ?>"><?= $data['label'] ?></span>
            </div>
            <p class="text-xs <?= $descClass ?>"><?= $data['desc'] ?></p>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- ── Main Search Bar ── -->
    <div class="p-5 rounded-xl border border-slate-200 shadow-sm bg-white">
        <form method="GET" action="search.php" id="searchForm">
            <!-- Preserve active mode across submissions -->
            <input type="hidden" name="mode" value="<?= htmlspecialchars($activeMode) ?>" />

            <!-- Keyword row -->
            <div class="flex gap-3 mb-4">
                <div class="flex-1 relative">
                    <i data-feather="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    <?php
                        $placeholder = "Enter keyword...";
                        if ($activeMode === 'TBR') $placeholder = "Enter keyword (e.g. Johor, human, technology)…";
                        if ($activeMode === 'ABR') $placeholder = "Enter file name, matric no, or group…";
                        if ($activeMode === 'CBR') $placeholder = "Enter mood label or content feature…";
                    ?>
                    <input
                        type="text"
                        name="keyword"
                        value="<?= htmlspecialchars($keyword) ?>"
                        placeholder="<?= $placeholder ?>"
                        class="w-full border border-slate-200 rounded-md pl-10 pr-4 py-2 h-11 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium h-11 px-7 bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                    <i data-feather="search" class="w-4 h-4 mr-2"></i> Search
                </button>
                <a href="search.php?mode=<?= htmlspecialchars($activeMode) ?>" class="inline-flex items-center justify-center rounded-md text-sm font-medium h-11 px-5 border border-slate-200 bg-white hover:bg-slate-100 text-slate-900 transition-colors">
                    <i data-feather="x" class="w-4 h-4 mr-2"></i> Clear
                </a>
            </div>

            <!-- ── Advanced Filters Panel ──
                 Collapsed by default; auto-expands when a filter is already active.
                 Preserves all filter values across page loads via GET params. -->
            <div>
                <!-- Toggle button -->
                <button
                    type="button"
                    onclick="toggleFilters()"
                    class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-700 transition mb-3"
                    id="filterToggleBtn"
                >
                    <i data-feather="sliders" class="w-3.5 h-3.5"></i>
                    Advanced Filters
                    <?php if ($hasActiveFilter): ?>
                        <span class="ml-1 px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded text-xs">Active</span>
                    <?php endif; ?>
                    <i data-feather="chevron-down" class="w-3.5 h-3.5" id="filterChevron"></i>
                </button>

                <!-- Filter fields — shown/hidden by JS -->
                <div
                    id="advancedFilters"
                    class="<?= $hasActiveFilter ? '' : 'hidden' ?> grid grid-cols-2 md:grid-cols-4 gap-3 p-4 bg-slate-50 border border-slate-200 rounded-lg"
                >
                    <!-- File Type -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">File Type</label>
                        <select
                            name="filterType"
                            class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                        >
                            <option value=""  <?= $filterType === ''    ? 'selected' : '' ?>>Any</option>
                            <option value="PDF" <?= $filterType === 'PDF' ? 'selected' : '' ?>>PDF Document</option>
                            <option value="MP3" <?= $filterType === 'MP3' ? 'selected' : '' ?>>Audio (MP3)</option>
                            <option value="MP4" <?= $filterType === 'MP4' ? 'selected' : '' ?>>Video (MP4)</option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Date From</label>
                        <input
                            type="date"
                            name="dateFrom"
                            value="<?= htmlspecialchars($filterDateFrom) ?>"
                            class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                        />
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Date To</label>
                        <input
                            type="date"
                            name="dateTo"
                            value="<?= htmlspecialchars($filterDateTo) ?>"
                            class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                        />
                    </div>

                    <!-- Size Range -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Size (MB)</label>
                        <div class="flex gap-1 items-center">
                            <input
                                type="number"
                                name="sizeMin"
                                value="<?= htmlspecialchars($filterSizeMin) ?>"
                                placeholder="Min"
                                min="0"
                                step="0.1"
                                class="w-full border border-slate-200 rounded-md px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                            />
                            <span class="text-slate-400 text-xs flex-shrink-0">–</span>
                            <input
                                type="number"
                                name="sizeMax"
                                value="<?= htmlspecialchars($filterSizeMax) ?>"
                                placeholder="Max"
                                min="0"
                                step="0.1"
                                class="w-full border border-slate-200 rounded-md px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                            />
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <!-- ── Results + Sidebar ── -->
    <div class="grid lg:grid-cols-3 gap-6 mt-6">

        <!-- Results Column (2/3 width) -->
        <div class="lg:col-span-2 space-y-4">

            <!-- Results header -->
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-slate-900">
                    <?= $hasSearched ? "Results (" . count($searchResults) . ")" : "Awaiting Search..." ?>
                </h2>
                <?php if ($hasSearched): ?>
                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold bg-<?= $activeColor ?>-100 text-<?= $activeColor ?>-700 border-<?= $activeColor ?>-200">
                        <?= $activeMode ?> — <?= $modes[$activeMode]['label'] ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- State: not yet searched -->
            <?php if (!$hasSearched): ?>
                <div class="p-12 rounded-xl border border-slate-200 shadow-sm bg-white text-center">
                    <i data-feather="search" class="w-12 h-12 text-slate-300 mx-auto mb-4"></i>
                    <p class="text-slate-600">Enter a keyword and click Search to retrieve files from the database.</p>
                </div>

            <!-- State: searched but no results -->
            <?php elseif (count($searchResults) === 0): ?>
                <div class="p-12 rounded-xl border border-slate-200 shadow-sm bg-white text-center">
                    <i data-feather="search" class="w-12 h-12 text-slate-300 mx-auto mb-4"></i>
                    <p class="text-slate-600">No results found. Try adjusting your filters.</p>
                </div>

            <!-- State: results found -->
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($searchResults as $result):
                        $iconData   = getFileIconData($result['fileType']   ?? '');
                        $badgeColor = getFileTypeBadgeColor($result['fileType'] ?? '');
                    ?>
                    <div class="p-5 rounded-xl border border-slate-200 shadow-sm bg-white hover:shadow-md transition-shadow">
                        <div class="flex gap-4">

                            <!-- File type icon + badge -->
                            <div class="flex-shrink-0 flex flex-col items-center gap-1">
                                <i data-feather="<?= $iconData['icon'] ?>" class="w-10 h-10 <?= $iconData['color'] ?>"></i>
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold <?= $badgeColor ?>">
                                    <?= htmlspecialchars($result['fileType'] ?? 'UNK') ?>
                                </span>
                            </div>

                            <!-- Result details -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <h3 class="font-semibold text-slate-900 truncate">
                                            <?= htmlspecialchars($result['fileName']    ?? 'Unnamed File') ?>
                                        </h3>
                                        <p class="text-sm text-slate-700">
                                            <?= htmlspecialchars($result['studentName'] ?? 'Unknown Student') ?>
                                            <span class="mx-1 text-slate-400">·</span>
                                            <span class="text-slate-500">
                                                <?= htmlspecialchars($result['matricNo'] ?? 'N/A') ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Metadata chips -->
                                <div class="flex flex-wrap gap-x-5 gap-y-1 mb-3 text-xs text-slate-500">
                                    <span class="flex items-center gap-1">
                                        <span class="font-medium text-slate-600">Group:</span>
                                        <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-semibold bg-blue-50 text-blue-700 border-blue-200">
                                            <?= htmlspecialchars($result['groupName'] ?? 'N/A') ?>
                                        </span>
                                    </span>

                                    <?php if (!empty($result['phone'])): ?>
                                        <span class="flex items-center gap-1">
                                            <i data-feather="phone" class="w-3 h-3"></i>
                                            <?= htmlspecialchars($result['phone']) ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($result['uploadDate'])): ?>
                                        <span class="flex items-center gap-1">
                                            <i data-feather="calendar" class="w-3 h-3"></i>
                                            <?= date('d M Y', strtotime($result['uploadDate'])) ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($result['fileSizeMb'])): ?>
                                        <span class="flex items-center gap-1">
                                            <span class="font-medium text-slate-600">Size:</span>
                                            <?= number_format((float) $result['fileSizeMb'], 1) ?> MB
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($result['moodLabel'])): ?>
                                        <span class="flex items-center gap-1">
                                            <i data-feather="music" class="w-3 h-3"></i>
                                            <span class="font-medium text-slate-600">Mood:</span>
                                            <?= htmlspecialchars($result['moodLabel']) ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($result['videoResolution'])): ?>
                                        <span class="flex items-center gap-1">
                                            <i data-feather="monitor" class="w-3 h-3"></i>
                                            <?= htmlspecialchars($result['videoResolution']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Life motto -->
                                <?php if (!empty($result['lifeMotto'])): ?>
                                    <div class="flex items-start gap-1 mb-3 text-xs text-slate-500 italic">
                                        <i data-feather="quote" class="w-3 h-3 mt-0.5 flex-shrink-0"></i>
                                        <span><?= htmlspecialchars($result['lifeMotto']) ?></span>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>

        <!-- Sidebar — Live System Overview -->
        <div class="space-y-4">
            <div class="p-5 rounded-xl border border-slate-200 shadow-sm bg-white">
                <h3 class="font-semibold text-slate-900 mb-4">System Overview</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-slate-100">
                        <span class="text-sm text-slate-600">Total Files Indexed</span>
                        <span class="font-semibold text-slate-900"><?= $dbStats['totalFiles'] ?></span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-slate-100">
                        <span class="text-sm text-slate-600">PDF Documents</span>
                        <span class="font-semibold text-red-600"><?= $dbStats['pdfCount'] ?></span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-slate-100">
                        <span class="text-sm text-slate-600">Audio Files (MP3)</span>
                        <span class="font-semibold text-orange-600"><?= $dbStats['audioCount'] ?></span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-slate-600">Video Files (MP4)</span>
                        <span class="font-semibold text-blue-600"><?= $dbStats['videoCount'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Search Tips Card -->
            <div class="p-5 rounded-xl border border-slate-200 shadow-sm bg-slate-50">
                <h3 class="font-semibold text-slate-700 mb-3 text-sm">💡 Search Tips</h3>
                <ul class="text-xs text-slate-500 space-y-2">
                    <li><span class="font-semibold text-purple-700">ABR:</span> Try a matric no (e.g. B032410184), group (GW04), or file type (PDF)</li>
                    <li><span class="font-semibold text-green-700">TBR:</span> Try keywords like "database", "calm", or a student's name</li>
                    <li><span class="font-semibold text-orange-700">CBR:</span> Try mood labels like "Happy", "Calm", or resolution like "1920x1080"</li>
                    <li><span class="font-semibold text-blue-700">Filters:</span> Use Advanced Filters to narrow by file type, date, or size</li>
                </ul>
            </div>
        </div>

    </div>
</div>

<script>
// =============================================================
// Toggle Advanced Filters panel open/closed
// =============================================================
function toggleFilters() {
    const panel   = document.getElementById('advancedFilters');
    const chevron = document.getElementById('filterChevron');
    const hidden  = panel.classList.toggle('hidden');
    // Rotate chevron to indicate open/closed state
    chevron.style.transform = hidden ? 'rotate(0deg)' : 'rotate(180deg)';
}

// On page load: rotate chevron if filters are already expanded
document.addEventListener('DOMContentLoaded', function () {
    const panel   = document.getElementById('advancedFilters');
    const chevron = document.getElementById('filterChevron');
    if (!panel.classList.contains('hidden')) {
        chevron.style.transform = 'rotate(180deg)';
    }
    feather.replace();
});
</script>

<?php include 'includes/footer.php'; ?>
