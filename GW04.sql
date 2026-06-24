-- Create Student Table
CREATE TABLE student (
    studentID INT AUTO_INCREMENT PRIMARY KEY,
    matric_no VARCHAR(20) UNIQUE NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    group_name VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create MEDIA_FILE Table
CREATE TABLE MEDIA_FILE (
    file_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    life_motto TEXT,
    pdf_file VARCHAR(255),
    audio_file VARCHAR(255),
    video_file VARCHAR(255),
    file_type VARCHAR(20),
    file_size BIGINT,
    upload_file TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    file_name VARCHAR(255),
    CONSTRAINT fk_media_student
        FOREIGN KEY (student_id)
        REFERENCES student(studentID)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create PDF_TEXT Table
CREATE TABLE PDF_TEXT (
    text_id INT AUTO_INCREMENT PRIMARY KEY,
    file_id INT NOT NULL,
    extracted_text TEXT,
    CONSTRAINT fk_pdf_file
        FOREIGN KEY (file_id)
        REFERENCES MEDIA_FILE(file_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create audio_features Table
CREATE TABLE audio_features (
    feature_id INT AUTO_INCREMENT PRIMARY KEY,
    file_id INT NOT NULL,
    mfcc1 DECIMAL(10,4),
    mfcc2 DECIMAL(10,4),
    mfcc3 DECIMAL(10,4),
    mfcc4 DECIMAL(10,4),
    mfcc5 DECIMAL(10,4),
    tempo DECIMAL(10,4),
    energy DECIMAL(10,4),
    CONSTRAINT fk_audio_file
        FOREIGN KEY (file_id)
        REFERENCES MEDIA_FILE(file_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
