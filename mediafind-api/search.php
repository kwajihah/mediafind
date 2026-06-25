<?php
// =============================================================
//  MediaFind — search.php
//  Retrieval & SQL Developer : Khairul Wajihah (B032410184)
//  Handles ABR, TBR, CBR retrieval from gw04 database
// =============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../db_connect_utem.php';

$pdo    = getDB();
$mode   = strtoupper(trim($_GET['mode']   ?? 'TBR'));
$kw     = trim($_GET['keyword']           ?? '');
$ftype  = trim($_GET['fileType']          ?? 'All');
$group  = trim($_GET['group']             ?? 'All');
$size   = trim($_GET['size']              ?? 'All');
$mood   = trim($_GET['mood']              ?? 'All');
$minDur = trim($_GET['minDuration']       ?? '');
$maxDur = trim($_GET['maxDuration']       ?? '');

$results = [];

// =============================================================
//  ABR — Attribute-Based Retrieval
//  Filter by: file type, group, file size, upload date
// =============================================================
if ($mode === 'ABR') {
    $sql = "
        SELECT
            mf.file_id        AS id,
            mf.file_name      AS fileName,
            UPPER(mf.file_type) AS fileType,
            s.student_name    AS studentName,
            s.matric_no       AS matricNo,
            s.group_name      AS `group`,
            s.phone           AS phone,
            DATE(mf.upload_file) AS uploadDate,
            ROUND(mf.file_size / 1048576, 2) AS fileSizeMb,
            mf.life_motto     AS lifeMotto
        FROM media_file mf
        JOIN student s ON mf.student_id = s.studentID
        WHERE 1=1
    ";
    $params = [];

    // Filter: file type
    if ($ftype !== 'All') {
        $map = ['PDF' => 'pdf', 'MP3' => 'audio', 'MP4' => 'video'];
        if (isset($map[$ftype])) {
            $sql .= " AND mf.file_type = ?";
            $params[] = $map[$ftype];
        }
    }

    // Filter: student group
    if ($group !== 'All') {
        $sql .= " AND s.group_name = ?";
        $params[] = $group;
    }

    // Filter: file size category
    if ($size === 'Small') {
        $sql .= " AND mf.file_size < 1048576";
    } elseif ($size === 'Medium') {
        $sql .= " AND mf.file_size BETWEEN 1048576 AND 5242880";
    } elseif ($size === 'Large') {
        $sql .= " AND mf.file_size > 5242880";
    }

    // Filter: keyword in file name or matric no
    if ($kw !== '') {
        $sql .= " AND (mf.file_name LIKE ? OR s.matric_no LIKE ? OR s.student_name LIKE ?)";
        $like = "%$kw%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $sql .= " ORDER BY s.group_name, s.student_name, mf.file_type";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
}

// =============================================================
//  TBR — Text-Based Retrieval
//  Search keyword in: student_name, life_motto, pdf extracted text
// =============================================================
elseif ($mode === 'TBR') {
    $sql = "
        SELECT DISTINCT
            mf.file_id        AS id,
            mf.file_name      AS fileName,
            UPPER(mf.file_type) AS fileType,
            s.student_name    AS studentName,
            s.matric_no       AS matricNo,
            s.group_name      AS `group`,
            s.phone           AS phone,
            DATE(mf.upload_file) AS uploadDate,
            ROUND(mf.file_size / 1048576, 2) AS fileSizeMb,
            mf.life_motto     AS lifeMotto
        FROM media_file mf
        JOIN student s ON mf.student_id = s.studentID
        LEFT JOIN pdf_text pt ON mf.file_id = pt.file_id
        WHERE 1=1
    ";
    $params = [];

    // Keyword search across all text fields
    if ($kw !== '') {
        $sql .= " AND (
            LOWER(s.student_name)    LIKE LOWER(?)
            OR LOWER(mf.life_motto)   LIKE LOWER(?)
            OR LOWER(pt.extracted_text) LIKE LOWER(?)
        )";
        $like = "%$kw%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    // Optional file type filter
    if ($ftype !== 'All') {
        $map = ['PDF' => 'pdf', 'MP3' => 'audio', 'MP4' => 'video'];
        if (isset($map[$ftype])) {
            $sql .= " AND mf.file_type = ?";
            $params[] = $map[$ftype];
        }
    }

    // Optional group filter
    if ($group !== 'All') {
        $sql .= " AND s.group_name = ?";
        $params[] = $group;
    }

    $sql .= " ORDER BY s.group_name, s.student_name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
}

// =============================================================
//  CBR — Content-Based Retrieval
//  Search by audio content features: mood (energy+tempo), duration
//  Uses Euclidean Distance similarity on MFCC + tempo + energy
// =============================================================
elseif ($mode === 'CBR') {
    // Mood maps to energy/tempo thresholds (detected from content)
    $sql = "
        SELECT
            mf.file_id        AS id,
            mf.file_name      AS fileName,
            'MP3'             AS fileType,
            s.student_name    AS studentName,
            s.matric_no       AS matricNo,
            s.group_name      AS `group`,
            s.phone           AS phone,
            DATE(mf.upload_file) AS uploadDate,
            ROUND(mf.file_size / 1048576, 2) AS fileSizeMb,
            mf.life_motto     AS lifeMotto,
            ROUND(af.tempo, 2)  AS tempo,
            ROUND(af.energy, 4) AS energy,
            CASE
                WHEN af.energy > 0.08 AND af.tempo > 120           THEN 'Energetic'
                WHEN af.energy > 0.05 AND af.tempo BETWEEN 90 AND 120 THEN 'Happy'
                WHEN af.energy <= 0.05 AND af.tempo < 90           THEN 'Calm'
                ELSE 'Moderate'
            END AS detectedMood
        FROM audio_features af
        JOIN media_file mf ON af.file_id = mf.file_id
        JOIN student s ON mf.student_id = s.studentID
        WHERE 1=1
    ";
    $params = [];

    // Filter by mood (content-detected, not user label)
    if ($mood !== 'All') {
        if (strtolower($mood) === 'energetic') {
            $sql .= " AND af.energy > 0.08 AND af.tempo > 120";
        } elseif (strtolower($mood) === 'calm') {
            $sql .= " AND af.energy <= 0.05 AND af.tempo < 90";
        } elseif (strtolower($mood) === 'happy') {
            $sql .= " AND af.energy > 0.05 AND af.tempo BETWEEN 90 AND 120";
        }
    }

    // Filter by group
    if ($group !== 'All') {
        $sql .= " AND s.group_name = ?";
        $params[] = $group;
    }

    // Keyword in student name or life motto
    if ($kw !== '') {
        $sql .= " AND (LOWER(s.student_name) LIKE LOWER(?) OR LOWER(mf.life_motto) LIKE LOWER(?))";
        $like = "%$kw%";
        $params[] = $like;
        $params[] = $like;
    }

    $sql .= " ORDER BY af.energy DESC, af.tempo DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    // Append detected mood to each result for frontend display
    foreach ($rows as &$row) {
        $row['lifeMotto'] = ($row['lifeMotto'] ?? '') .
            ' | Tempo: ' . $row['tempo'] . ' BPM | Energy: ' . $row['energy'] .
            ' | Mood: ' . $row['detectedMood'];
    }
    $results = $rows;
}

echo json_encode($results);
