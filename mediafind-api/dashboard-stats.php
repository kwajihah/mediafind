<?php
// =============================================================
//  MediaFind — dashboard-stats.php
//  Retrieval & SQL Developer : Khairul Wajihah (B032410184)
//  Returns file counts for the System Overview panel
// =============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../db_connect_utem.php';

$pdo = getDB();

$stats = [
    'totalFiles'  => 0,
    'pdfCount'    => 0,
    'audioCount'  => 0,
    'videoCount'  => 0,
    'studentCount'=> 0,
];

// Total files
$stmt = $pdo->query("SELECT COUNT(*) FROM media_file");
$stats['totalFiles'] = (int) $stmt->fetchColumn();

// PDF count
$stmt = $pdo->query("SELECT COUNT(*) FROM media_file WHERE file_type = 'pdf'");
$stats['pdfCount'] = (int) $stmt->fetchColumn();

// Audio count
$stmt = $pdo->query("SELECT COUNT(*) FROM media_file WHERE file_type = 'audio'");
$stats['audioCount'] = (int) $stmt->fetchColumn();

// Video count
$stmt = $pdo->query("SELECT COUNT(*) FROM media_file WHERE file_type = 'video'");
$stats['videoCount'] = (int) $stmt->fetchColumn();

// Student count
$stmt = $pdo->query("SELECT COUNT(*) FROM student");
$stats['studentCount'] = (int) $stmt->fetchColumn();

echo json_encode($stats);
