-- ============================================================
-- GW04 MediaFind Database Schema
-- Matches the PHP code: camelCase columns, tables: students + files
-- Run this in phpMyAdmin -> gw_04 database -> SQL tab (XAMPP)
-- ============================================================

-- Drop old tables if they exist (clean slate)
DROP TABLE IF EXISTS files;
DROP TABLE IF EXISTS students;

-- ------------------------------------------------------------
-- STUDENTS table
-- ------------------------------------------------------------
CREATE TABLE students (
    studentID   INT AUTO_INCREMENT PRIMARY KEY,
    studentName VARCHAR(100) NOT NULL,
    matricNo    VARCHAR(20)  NOT NULL UNIQUE,
    phone       VARCHAR(20),
    groupName   VARCHAR(20),
    lifeMotto   VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- FILES table (denormalized for simple queries)
-- Stores PDF, MP3, MP4 — with CBR/TBR fields
-- ------------------------------------------------------------
CREATE TABLE files (
    fileID          INT AUTO_INCREMENT PRIMARY KEY,
    studentID       INT NOT NULL,
    studentName     VARCHAR(100),
    matricNo        VARCHAR(20),
    groupName       VARCHAR(20),
    phone           VARCHAR(20),
    lifeMotto       VARCHAR(255),
    fileName        VARCHAR(255) NOT NULL,
    fileType        VARCHAR(10)  NOT NULL,   -- PDF | MP3 | MP4
    fileSizeMb      DECIMAL(10,2),
    uploadDate      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- CBR fields
    moodLabel       VARCHAR(50),             -- e.g. Happy, Sad, Calm
    videoResolution VARCHAR(20),             -- e.g. 1920x1080
    fileFeature     VARCHAR(255),            -- general content feature
    CONSTRAINT fk_files_student
        FOREIGN KEY (studentID) REFERENCES students(studentID)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Sample Data for testing (3 GW04 students, 9 files)
-- ============================================================
INSERT INTO students (studentName, matricNo, phone, groupName, lifeMotto) VALUES
('Khairul Wajihah Binti Khairuddin', 'B032410184', '011-12345678', 'GW04', 'Work hard, stay humble'),
('Miya Aoyon',                       'B032220052', '012-98765432', 'GW04', 'Always keep learning'),
('Miza Binti Mohamad Radzi',         'B032310641', '013-55566677', 'GW04', 'Dream big, achieve bigger'),
('Muhammad Arifuddin Bin Azman',      'B032310638', '014-11223344', 'GW04', 'Strive for excellence every day');

INSERT INTO files (studentID, studentName, matricNo, groupName, phone, lifeMotto, fileName, fileType, fileSizeMb, moodLabel, videoResolution, fileFeature) VALUES
(1, 'Khairul Wajihah Binti Khairuddin', 'B032410184', 'GW04', '011-12345678', 'Work hard, stay humble', 'thesis_final.pdf',      'PDF', 2.10,  NULL,    NULL,         'academic research multimedia database'),
(1, 'Khairul Wajihah Binti Khairuddin', 'B032410184', 'GW04', '011-12345678', 'Work hard, stay humble', 'demo_video.mp4',        'MP4', 85.00, NULL,    '1920x1080',  'project demonstration BITP3353'),
(1, 'Khairul Wajihah Binti Khairuddin', 'B032410184', 'GW04', '011-12345678', 'Work hard, stay humble', 'lo_fi_study.mp3',       'MP3', 6.10,  'Calm',  NULL,         'lo-fi ambient chill study music'),
(2, 'Miya Aoyon',                       'B032220052', 'GW04', '012-98765432', 'Always keep learning',   'presentation_slide.pdf','PDF', 1.00,  NULL,    NULL,         'multimedia systems database architecture BITP3353'),
(2, 'Miya Aoyon',                       'B032220052', 'GW04', '012-98765432', 'Always keep learning',   'jazz_sample.mp3',       'MP3', 4.80,  'Happy', NULL,         'jazz upbeat energetic music'),
(2, 'Miya Aoyon',                       'B032220052', 'GW04', '012-98765432', 'Always keep learning',   'lecture_recording.mp4', 'MP4', 120.00,NULL,    '1280x720',   'lecture educational content'),
(3, 'Miza Binti Mohamad Radzi',         'B032310641', 'GW04', '013-55566677', 'Dream big, achieve bigger','report_chapter2.pdf', 'PDF', 0.90,  NULL,    NULL,         'literature review retrieval methods ABR TBR CBR'),
(3, 'Miza Binti Mohamad Radzi',         'B032310641', 'GW04', '013-55566677', 'Dream big, achieve bigger','ambient_track.mp3',   'MP3', 5.20,  'Calm',  NULL,         'ambient peaceful relaxing music'),
(3, 'Miza Binti Mohamad Radzi',         'B032310641', 'GW04', '013-55566677', 'Dream big, achieve bigger','project_demo.mp4',    'MP4', 95.00, NULL,    '1920x1080',  'system demo presentation walkthrough');

-- Files for Muhammad Arifuddin
INSERT INTO files (studentID, studentName, matricNo, groupName, phone, lifeMotto, fileName, fileType, fileSizeMb, moodLabel, videoResolution, fileFeature) VALUES
(4, 'Muhammad Arifuddin Bin Azman', 'B032310638', 'GW04', '014-11223344', 'Strive for excellence every day', 'arifuddin_report.pdf', 'PDF',  1.50, NULL,    NULL,        'database retrieval system multimedia BITP3353'),
(4, 'Muhammad Arifuddin Bin Azman', 'B032310638', 'GW04', '014-11223344', 'Strive for excellence every day', 'arifuddin_song.mp3',   'MP3',  4.20, 'Happy', NULL,        'upbeat cheerful energetic pop music'),
(4, 'Muhammad Arifuddin Bin Azman', 'B032310638', 'GW04', '014-11223344', 'Strive for excellence every day', 'arifuddin_demo.mp4',   'MP4', 70.00, NULL,    '1280x720',  'system walkthrough demo presentation');

-- ============================================================
-- End of schema
-- ============================================================
