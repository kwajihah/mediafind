<?php
include 'includes/header.php';
include 'includes/db_connect_utem.php';

$pdo = getDB();

$searchTerm  = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchParam = "%{$searchTerm}%";

// ── Fetch students (life_motto comes from media_file) ────
// Subquery gets the latest life_motto for each student
$sql = "
    SELECT s.*,
        (SELECT mf.life_motto
         FROM media_file mf
         WHERE mf.student_id = s.studentID
           AND mf.life_motto IS NOT NULL
           AND mf.life_motto != ''
         LIMIT 1) AS life_motto
    FROM student s
    WHERE s.student_name LIKE ?
       OR s.matric_no    LIKE ?
       OR s.group_name   LIKE ?
    ORDER BY s.group_name, s.student_name
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$searchParam, $searchParam, $searchParam]);
$students = $stmt->fetchAll();

// ── Group stats ──────────────────────────────────────────
$statsStmt      = $pdo->query("SELECT group_name, COUNT(*) as count FROM student GROUP BY group_name");
$groupStatsResult = $statsStmt->fetchAll();

$groupStats = [];
foreach ($groupStatsResult as $row) {
    $groupStats[$row['group_name']] = $row['count'];
}
?>

<div class="space-y-6 max-w-7xl mx-auto p-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 mb-1">Students</h1>
            <p class="text-slate-600">Dataset from bitp3353.utem.edu.my — student profiles and multimedia submissions</p>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 rounded-lg border border-blue-100">
            <span class="text-blue-700 font-medium"><?= count($students) ?> Students Found</span>
        </div>
    </div>

    <!-- Group Stats -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <?php
        $groups = ['GW01','GW02','GW03','GW04','GW05'];
        $colors = [
            'GW01'=>'bg-blue-100 text-blue-700',
            'GW02'=>'bg-green-100 text-green-700',
            'GW03'=>'bg-purple-100 text-purple-700',
            'GW04'=>'bg-orange-100 text-orange-700',
            'GW05'=>'bg-pink-100 text-pink-700'
        ];
        foreach ($groups as $group):
            $count = $groupStats[$group] ?? 0;
            $color = $colors[$group];
        ?>
        <div class="p-5 rounded-xl border border-slate-200 shadow-sm bg-white">
            <div class="inline-flex items-center justify-center w-9 h-9 <?= $color ?> rounded-lg mb-3">
                <span class="font-bold text-sm"><?= substr($group, -2) ?></span>
            </div>
            <div class="text-2xl font-bold text-slate-900 mb-0.5"><?= $count ?></div>
            <div class="text-sm text-slate-600"><?= $group ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Search + Table -->
    <div class="rounded-xl border border-slate-200 shadow-sm bg-white">
        <div class="p-5 border-b border-slate-200">
            <form method="GET" action="student.php" class="flex gap-2">
                <input type="search" name="search"
                       value="<?= htmlspecialchars($searchTerm) ?>"
                       placeholder="Search by name, matric no, group…"
                       class="flex-1 pl-4 pr-4 py-2 border rounded-l-md focus:outline-none focus:border-blue-500" />
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded-r-md hover:bg-blue-700 transition">
                    Search
                </button>
                <?php if ($searchTerm): ?>
                <a href="student.php"
                   class="px-4 py-2 border rounded-md text-slate-600 hover:bg-slate-100 transition text-sm flex items-center">
                    Clear
                </a>
                <?php endif; ?>
            </form>
        </div>

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b bg-slate-50">
                    <th class="p-4 font-medium text-slate-700">Student Name</th>
                    <th class="p-4 font-medium text-slate-700">Matric No</th>
                    <th class="p-4 font-medium text-slate-700">Group</th>
                    <th class="p-4 font-medium text-slate-700">Phone</th>
                    <th class="p-4 font-medium text-slate-700">Life Motto</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($students) > 0): ?>
                    <?php foreach ($students as $student): ?>
                    <tr class="border-b hover:bg-slate-50">
                        <td class="p-4 font-medium text-slate-900">
                            <?= htmlspecialchars($student['student_name']) ?>
                        </td>
                        <td class="p-4 text-slate-600 font-mono text-sm">
                            <?= htmlspecialchars($student['matric_no']) ?>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-1 <?= $student['group_name']==='GW04' ? 'bg-orange-50 text-orange-700 border-orange-200' : 'bg-blue-50 text-blue-700 border-blue-200' ?> border rounded-md text-xs font-medium">
                                <?= htmlspecialchars($student['group_name']) ?>
                            </span>
                        </td>
                        <td class="p-4 text-slate-600 text-sm">
                            <?= htmlspecialchars($student['phone']) ?>
                        </td>
                        <td class="p-4 text-slate-500 text-sm italic max-w-xs truncate">
                            <?php if (!empty($student['life_motto'])): ?>
                                "<?= htmlspecialchars($student['life_motto']) ?>"
                            <?php else: ?>
                                <span class="text-slate-300 not-italic">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-600">
                            No students found matching your search.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
