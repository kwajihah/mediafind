-- ============================================================
-- GW04 Database Schema  (MediaFind project)
-- Course: BITP3353 Multimedia Database
-- Use this in XAMPP phpMyAdmin (Import tab or SQL tab)
-- ============================================================

CREATE DATABASE IF NOT EXISTS GW04
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE GW04;

-- Drop in reverse dependency order (safe to re-run this script)
DROP TABLE IF EXISTS video_metadata;
DROP TABLE IF EXISTS audio_metadata;
DROP TABLE IF EXISTS text_tag;
DROP TABLE IF EXISTS media_file;
DROP TABLE IF EXISTS student;

-- ------------------------------------------------------------
-- STUDENT
-- ------------------------------------------------------------
CREATE TABLE student (
    student_id    INT AUTO_INCREMENT PRIMARY KEY,
    student_name  VARCHAR(100) NOT NULL,
    matric_no     VARCHAR(20)  NOT NULL UNIQUE,
    phone         VARCHAR(20),
    group_name    VARCHAR(50),
    life_motto    VARCHAR(255)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- MEDIA_FILE  (many files belong to one student)
-- ------------------------------------------------------------
CREATE TABLE media_file (
    file_id        INT AUTO_INCREMENT PRIMARY KEY,
    student_id     INT NOT NULL,
    file_name      VARCHAR(255) NOT NULL,
    file_type      VARCHAR(50)  NOT NULL,
    file_size      BIGINT,
    file_path      VARCHAR(500) NOT NULL,
    created_date   DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_date  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    accessed_date  DATETIME,
    CONSTRAINT fk_mediafile_student
        FOREIGN KEY (student_id) REFERENCES student(student_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TEXT_TAG  (many tags belong to one media file)
-- ------------------------------------------------------------
CREATE TABLE text_tag (
    tag_id        INT AUTO_INCREMENT PRIMARY KEY,
    file_id       INT NOT NULL,
    source_field  VARCHAR(100),
    keyword       VARCHAR(100) NOT NULL,
    CONSTRAINT fk_texttag_mediafile
        FOREIGN KEY (file_id) REFERENCES media_file(file_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- AUDIO_METADATA  (many audio records can belong to one media file)
-- ------------------------------------------------------------
CREATE TABLE audio_metadata (
    audio_id          INT AUTO_INCREMENT PRIMARY KEY,
    file_id           INT NOT NULL,
    duration_seconds  INT,
    file_size_mb      DECIMAL(10,2),
    bitrate           INT,
    mood_label        VARCHAR(50),
    CONSTRAINT fk_audiometadata_mediafile
        FOREIGN KEY (file_id) REFERENCES media_file(file_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- VIDEO_METADATA  (many video records can belong to one media file)
-- ------------------------------------------------------------
CREATE TABLE video_metadata (
    video_id          INT AUTO_INCREMENT PRIMARY KEY,
    file_id           INT NOT NULL,
    duration_seconds  INT,
    resolution        VARCHAR(20),
    frame_rate        DECIMAL(5,2),
    CONSTRAINT fk_videometadata_mediafile
        FOREIGN KEY (file_id) REFERENCES media_file(file_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- End of schema
-- ============================================================