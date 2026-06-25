-- =============================================================
--  MEDIAFIND - RETRIEVAL & SQL DEVELOPER
--  Name    : Khairul Wajihah Binti Khairuddin
--  Matric  : B032410184
--  Role    : Retrieval & SQL Developer
--  Subject : BITP3353 Multimedia Database
--  Group   : GW04
-- =============================================================
-- Database : gw04
-- Tables   :
--   student       (studentID, matric_no, student_name, phone, group_name)
--   media_file    (file_id, student_id, life_motto, pdf_file, audio_file,
--                  video_file, file_type, file_size, upload_file, file_name)
--   pdf_text      (text_id, file_id, extracted_text)
--   audio_features(feature_id, file_id, mfcc1, mfcc2, mfcc3, mfcc4,
--                  mfcc5, tempo, energy)
-- Source data from : mmdb2026.vstu
-- =============================================================

USE gw04;

-- =============================================================
--  SECTION 1 : ATTRIBUTE-BASED RETRIEVAL (ABR)
--  Retrieve files based on file properties / metadata.
--  No file content is opened.
-- =============================================================

-- -----------------------------------------------------------
-- ABR-1 : Show ALL files with student info (overview)
-- -----------------------------------------------------------
SELECT
    s.matric_no,
    s.student_name,
    s.group_name,
    mf.file_name,
    mf.file_type,
    mf.file_size,
    mf.upload_file
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
ORDER BY s.group_name, s.student_name;

-- -----------------------------------------------------------
-- ABR-2 : Filter by file type = audio only
-- -----------------------------------------------------------
SELECT
    s.matric_no,
    s.student_name,
    s.group_name,
    mf.file_name,
    mf.audio_file,
    mf.file_size
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
WHERE mf.file_type = 'audio'
ORDER BY s.group_name;

-- -----------------------------------------------------------
-- ABR-3 : Filter by file type = pdf only
-- -----------------------------------------------------------
SELECT
    s.matric_no,
    s.student_name,
    s.group_name,
    mf.file_name,
    mf.pdf_file,
    mf.file_size
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
WHERE mf.file_type = 'pdf'
ORDER BY s.group_name;

-- -----------------------------------------------------------
-- ABR-4 : Filter by file type = video only
-- -----------------------------------------------------------
SELECT
    s.matric_no,
    s.student_name,
    s.group_name,
    mf.file_name,
    mf.video_file,
    mf.file_size
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
WHERE mf.file_type = 'video'
ORDER BY s.group_name;

-- -----------------------------------------------------------
-- ABR-5 : Filter by student group (GW04 only)
-- -----------------------------------------------------------
SELECT
    s.matric_no,
    s.student_name,
    mf.file_name,
    mf.file_type,
    mf.file_size
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
WHERE s.group_name = 'GW04'
ORDER BY mf.file_type;

-- -----------------------------------------------------------
-- ABR-6 : Filter files smaller than 5 MB
-- -----------------------------------------------------------
SELECT
    s.student_name,
    s.group_name,
    mf.file_name,
    mf.file_type,
    mf.file_size
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
WHERE mf.file_size < 5000000       -- file_size stored in bytes
ORDER BY mf.file_size ASC;

-- -----------------------------------------------------------
-- ABR-7 : Categorise files as Small / Medium / Large
-- -----------------------------------------------------------
SELECT
    s.student_name,
    mf.file_name,
    mf.file_type,
    mf.file_size,
    CASE
        WHEN mf.file_size < 1000000  THEN 'Small'     -- < 1 MB
        WHEN mf.file_size < 5000000  THEN 'Medium'    -- 1–5 MB
        ELSE                              'Large'      -- > 5 MB
    END AS size_category
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
ORDER BY mf.file_size;

-- -----------------------------------------------------------
-- ABR-8 : Filter by specific matric number
-- -----------------------------------------------------------
SELECT
    s.matric_no,
    s.student_name,
    mf.file_name,
    mf.file_type,
    mf.file_size
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
WHERE s.matric_no = 'B032410184';

-- -----------------------------------------------------------
-- ABR-9 : Count total files submitted per group
-- -----------------------------------------------------------
SELECT
    s.group_name,
    COUNT(mf.file_id) AS total_files
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
GROUP BY s.group_name
ORDER BY s.group_name;

-- -----------------------------------------------------------
-- ABR-10 : Count files per type per group (summary table)
-- -----------------------------------------------------------
SELECT
    s.group_name,
    mf.file_type,
    COUNT(*) AS total
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
GROUP BY s.group_name, mf.file_type
ORDER BY s.group_name, mf.file_type;

-- -----------------------------------------------------------
-- ABR-11 : Combined filter — GW04 + audio files only
-- -----------------------------------------------------------
SELECT
    s.matric_no,
    s.student_name,
    mf.file_name,
    mf.audio_file,
    mf.file_size
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
WHERE s.group_name = 'GW04'
  AND mf.file_type = 'audio';

-- -----------------------------------------------------------
-- ABR-12 : Files uploaded on a specific date
-- -----------------------------------------------------------
SELECT
    s.student_name,
    s.group_name,
    mf.file_name,
    mf.file_type,
    mf.upload_file
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
WHERE DATE(mf.upload_file) = '2026-05-20'
ORDER BY mf.upload_file;


-- =============================================================
--  SECTION 2 : TEXT-BASED RETRIEVAL (TBR)
--  Retrieve files using keywords from text fields:
--  student name, life motto, and extracted PDF content.
-- =============================================================

-- -----------------------------------------------------------
-- TBR-1 : Search by student name keyword
-- -----------------------------------------------------------
SELECT
    s.matric_no,
    s.student_name,
    s.group_name,
    mf.file_name,
    mf.file_type
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
WHERE s.student_name LIKE '%Wajihah%'
ORDER BY mf.file_type;

-- -----------------------------------------------------------
-- TBR-2 : Search by life motto keyword
-- -----------------------------------------------------------
-- life_motto is stored in media_file table
SELECT
    s.matric_no,
    s.student_name,
    s.group_name,
    mf.life_motto
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
WHERE mf.life_motto LIKE '%sabar%';

-- -----------------------------------------------------------
-- TBR-3 : Search by keyword inside PDF extracted text
-- -----------------------------------------------------------
SELECT
    s.student_name,
    s.matric_no,
    mf.file_name,
    pt.extracted_text
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
JOIN pdf_text pt    ON mf.file_id  = pt.file_id
WHERE pt.extracted_text LIKE '%teknologi%';

-- -----------------------------------------------------------
-- TBR-4 : Search ALL text fields at once (one search box)
-- -----------------------------------------------------------
-- Searches: student_name, life_motto, extracted PDF text
SELECT DISTINCT
    s.matric_no,
    s.student_name,
    s.group_name,
    mf.file_name,
    mf.file_type
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
LEFT JOIN pdf_text pt ON mf.file_id = pt.file_id
WHERE s.student_name      LIKE '%Johor%'
   OR mf.life_motto        LIKE '%Johor%'
   OR pt.extracted_text    LIKE '%Johor%'
ORDER BY s.group_name;

-- -----------------------------------------------------------
-- TBR-5 : Case-insensitive keyword search using LOWER()
-- -----------------------------------------------------------
SELECT DISTINCT
    s.matric_no,
    s.student_name,
    mf.file_name,
    mf.file_type
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
LEFT JOIN pdf_text pt ON mf.file_id = pt.file_id
WHERE LOWER(s.student_name)    LIKE LOWER('%human%')
   OR LOWER(mf.life_motto)      LIKE LOWER('%human%')
   OR LOWER(pt.extracted_text)  LIKE LOWER('%human%');

-- -----------------------------------------------------------
-- TBR-6 : Search life motto for motivation keywords
-- -----------------------------------------------------------
SELECT
    s.matric_no,
    s.student_name,
    s.group_name,
    mf.life_motto
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
WHERE mf.life_motto LIKE '%syukur%'
   OR mf.life_motto LIKE '%usaha%'
   OR mf.life_motto LIKE '%taqwa%';

-- -----------------------------------------------------------
-- TBR-7 : Search PDF for AI / technology topics
-- -----------------------------------------------------------
SELECT
    s.student_name,
    mf.file_name,
    pt.extracted_text
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
JOIN pdf_text pt    ON mf.file_id  = pt.file_id
WHERE pt.extracted_text LIKE '%artificial intelligence%'
   OR pt.extracted_text LIKE '%machine learning%'
   OR pt.extracted_text LIKE '%kecerdasan buatan%';

-- -----------------------------------------------------------
-- TBR-8 : Count how many results matched a keyword
-- -----------------------------------------------------------
SELECT COUNT(*) AS total_results
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
LEFT JOIN pdf_text pt ON mf.file_id = pt.file_id
WHERE LOWER(s.student_name)   LIKE '%johor%'
   OR LOWER(mf.life_motto)     LIKE '%johor%'
   OR LOWER(pt.extracted_text) LIKE '%johor%';


-- =============================================================
--  SECTION 3 : CONTENT-BASED RETRIEVAL (CBR)
--  Retrieve audio files based on features extracted FROM
--  the audio content itself using Librosa (MFCC, tempo, energy).
--  Similarity is measured using Euclidean Distance / KNN.
-- =============================================================

-- -----------------------------------------------------------
-- CBR-1 : View all stored audio feature vectors
-- -----------------------------------------------------------
SELECT
    af.feature_id,
    s.student_name,
    s.group_name,
    mf.file_name,
    af.mfcc1, af.mfcc2, af.mfcc3, af.mfcc4, af.mfcc5,
    af.tempo,
    af.energy
FROM audio_features af
JOIN media_file mf ON af.file_id   = mf.file_id
JOIN student s     ON mf.student_id = s.studentID
ORDER BY af.feature_id;

-- -----------------------------------------------------------
-- CBR-2 : Find HIGH ENERGY audio (energetic mood)
-- -----------------------------------------------------------
SELECT
    s.student_name,
    s.group_name,
    mf.file_name,
    af.tempo,
    af.energy
FROM audio_features af
JOIN media_file mf ON af.file_id   = mf.file_id
JOIN student s     ON mf.student_id = s.studentID
WHERE af.energy > 0.05
ORDER BY af.energy DESC;

-- -----------------------------------------------------------
-- CBR-3 : Find LOW ENERGY audio (calm / slow mood)
-- -----------------------------------------------------------
SELECT
    s.student_name,
    s.group_name,
    mf.file_name,
    af.tempo,
    af.energy
FROM audio_features af
JOIN media_file mf ON af.file_id   = mf.file_id
JOIN student s     ON mf.student_id = s.studentID
WHERE af.energy <= 0.05
ORDER BY af.energy ASC;

-- -----------------------------------------------------------
-- CBR-4 : Find HIGH TEMPO audio (fast / upbeat, > 120 BPM)
-- -----------------------------------------------------------
SELECT
    s.student_name,
    s.group_name,
    mf.file_name,
    af.tempo,
    af.energy
FROM audio_features af
JOIN media_file mf ON af.file_id   = mf.file_id
JOIN student s     ON mf.student_id = s.studentID
WHERE af.tempo > 120
ORDER BY af.tempo DESC;

-- -----------------------------------------------------------
-- CBR-5 : Filter by tempo range (moderate: 80–120 BPM)
-- -----------------------------------------------------------
SELECT
    s.student_name,
    mf.file_name,
    af.tempo,
    af.energy
FROM audio_features af
JOIN media_file mf ON af.file_id   = mf.file_id
JOIN student s     ON mf.student_id = s.studentID
WHERE af.tempo BETWEEN 80 AND 120
ORDER BY af.tempo;

-- -----------------------------------------------------------
-- CBR-6 : Auto mood classification using CASE
--         (system detects mood from content — not user label)
-- -----------------------------------------------------------
SELECT
    s.student_name,
    mf.file_name,
    af.tempo,
    af.energy,
    CASE
        WHEN af.energy > 0.08 AND af.tempo > 120           THEN 'Energetic'
        WHEN af.energy > 0.05 AND af.tempo BETWEEN 90 AND 120 THEN 'Happy'
        WHEN af.energy <= 0.05 AND af.tempo < 90           THEN 'Calm / Sad'
        ELSE                                                    'Moderate'
    END AS detected_mood
FROM audio_features af
JOIN media_file mf ON af.file_id   = mf.file_id
JOIN student s     ON mf.student_id = s.studentID
ORDER BY af.energy DESC;

-- -----------------------------------------------------------
-- CBR-7 : Euclidean Distance Similarity Search
-- -----------------------------------------------------------
-- Purpose : Given a query audio feature vector, rank all
--           stored audio by similarity — smallest distance
--           means most similar content.
--
-- Formula : distance = SQRT( SUM of (query - stored)^2 )
--
-- Replace @q_* values with features extracted from
-- the user's query audio file using Python/Librosa.
-- -----------------------------------------------------------

-- Set query feature vector
SET @q_mfcc1  = -200.5;
SET @q_mfcc2  =   80.3;
SET @q_mfcc3  =  -12.1;
SET @q_mfcc4  =   15.7;
SET @q_mfcc5  =   -8.4;
SET @q_tempo  =  110.0;
SET @q_energy =    0.07;

-- Run similarity search
SELECT
    s.student_name,
    s.group_name,
    mf.file_name,
    af.tempo,
    af.energy,
    ROUND(
        SQRT(
            POW(af.mfcc1  - @q_mfcc1,  2) +
            POW(af.mfcc2  - @q_mfcc2,  2) +
            POW(af.mfcc3  - @q_mfcc3,  2) +
            POW(af.mfcc4  - @q_mfcc4,  2) +
            POW(af.mfcc5  - @q_mfcc5,  2) +
            POW(af.tempo  - @q_tempo,  2) +
            POW(af.energy - @q_energy, 2)
        ), 4
    ) AS euclidean_distance
FROM audio_features af
JOIN media_file mf ON af.file_id   = mf.file_id
JOIN student s     ON mf.student_id = s.studentID
ORDER BY euclidean_distance ASC;   -- most similar first

-- -----------------------------------------------------------
-- CBR-8 : KNN — Top 3 most similar audio (K = 3)
-- -----------------------------------------------------------
SELECT
    s.student_name,
    s.group_name,
    mf.file_name,
    af.tempo,
    af.energy,
    ROUND(
        SQRT(
            POW(af.mfcc1  - @q_mfcc1,  2) +
            POW(af.mfcc2  - @q_mfcc2,  2) +
            POW(af.mfcc3  - @q_mfcc3,  2) +
            POW(af.mfcc4  - @q_mfcc4,  2) +
            POW(af.mfcc5  - @q_mfcc5,  2) +
            POW(af.tempo  - @q_tempo,  2) +
            POW(af.energy - @q_energy, 2)
        ), 4
    ) AS euclidean_distance
FROM audio_features af
JOIN media_file mf ON af.file_id   = mf.file_id
JOIN student s     ON mf.student_id = s.studentID
ORDER BY euclidean_distance ASC
LIMIT 3;   -- K = 3 nearest neighbours

-- -----------------------------------------------------------
-- CBR-9 : Average audio features per group (analytics)
-- -----------------------------------------------------------
SELECT
    s.group_name,
    ROUND(AVG(af.tempo),  2) AS avg_tempo,
    ROUND(AVG(af.energy), 4) AS avg_energy,
    ROUND(AVG(af.mfcc1),  2) AS avg_mfcc1,
    COUNT(*)                  AS total_audio_files
FROM audio_features af
JOIN media_file mf ON af.file_id   = mf.file_id
JOIN student s     ON mf.student_id = s.studentID
GROUP BY s.group_name
ORDER BY s.group_name;


-- =============================================================
--  SECTION 4 : COMBINED RETRIEVAL
--  Combine ABR + TBR + CBR in realistic search scenarios.
-- =============================================================

-- ABR + TBR : Group GW04 students with 'sabar' in life motto
--             who submitted an audio file
SELECT
    s.matric_no,
    s.student_name,
    mf.life_motto,
    mf.file_name,
    mf.file_type,
    mf.file_size
FROM student s
JOIN media_file mf ON s.studentID = mf.student_id
WHERE s.group_name  = 'GW04'
  AND mf.life_motto  LIKE '%sabar%'
  AND mf.file_type   = 'audio';

-- TBR + CBR : Students whose motto mentions 'music'
--             AND their audio has high energy
SELECT
    s.student_name,
    mf.life_motto,
    mf.file_name,
    af.tempo,
    af.energy
FROM student s
JOIN media_file mf    ON s.studentID  = mf.student_id
JOIN audio_features af ON mf.file_id  = af.file_id
WHERE mf.life_motto LIKE '%music%'
  AND af.energy     > 0.05;


-- =============================================================
--  SECTION 5 : SEARCH LOG
--  Record each search query into SEARCH_LOG table.
--  (SEARCH_LOG created by Miza — PL/SQL Developer)
-- =============================================================

-- Log ABR search
INSERT INTO SEARCH_LOG (keyword_searched, search_type, search_datetime, result_count)
SELECT 'file_type=audio', 'ABR', NOW(), COUNT(*)
FROM media_file WHERE file_type = 'audio';

-- Log TBR search
INSERT INTO SEARCH_LOG (keyword_searched, search_type, search_datetime, result_count)
SELECT 'sabar', 'TBR', NOW(), COUNT(*)
FROM media_file WHERE life_motto LIKE '%sabar%';

-- Log CBR search
INSERT INTO SEARCH_LOG (keyword_searched, search_type, search_datetime, result_count)
SELECT 'energy>0.05', 'CBR', NOW(), COUNT(*)
FROM audio_features WHERE energy > 0.05;

-- View all search history
SELECT * FROM SEARCH_LOG ORDER BY search_datetime DESC;

-- =============================================================
--  END OF FILE
--  Khairul Wajihah Binti Khairuddin (B032410184)
--  GW04 — Retrieval & SQL Developer
-- =============================================================
