-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 04, 2026 at 07:11 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gw04`
--

-- --------------------------------------------------------

--
-- Table structure for table `audio_features`
--

CREATE TABLE `audio_features` (
  `feature_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `mfcc1` decimal(10,4) DEFAULT NULL,
  `mfcc2` decimal(10,4) DEFAULT NULL,
  `mfcc3` decimal(10,4) DEFAULT NULL,
  `mfcc4` decimal(10,4) DEFAULT NULL,
  `mfcc5` decimal(10,4) DEFAULT NULL,
  `tempo` decimal(10,4) DEFAULT NULL,
  `energy` decimal(10,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audio_features`
--

INSERT INTO `audio_features` (`feature_id`, `file_id`, `mfcc1`, `mfcc2`, `mfcc3`, `mfcc4`, `mfcc5`, `tempo`, `energy`) VALUES
(1, 5, -198.7432, 82.3156, -14.2891, 16.4523, -9.1234, 128.0000, 0.0823),
(2, 6, -152.4567, 61.8923, -8.3412, 11.2341, -5.6789, 95.0000, 0.0612),
(3, 7, -231.1234, 45.6789, -22.4512, 8.9012, -13.4567, 72.0000, 0.0318),
(4, 8, -178.9012, 73.4561, -11.7823, 14.3456, -7.8901, 142.8571, 0.0945),
(5, 15, -210.3456, 55.1234, -18.6789, 9.7812, -11.2345, 85.7143, 0.0423),
(6, 16, -245.6789, 38.9012, -25.3456, 6.1234, -15.6789, 60.0000, 0.0215),
(7, 17, -165.2345, 78.6789, -9.8901, 18.5678, -6.3456, 136.3636, 0.0756),
(8, 18, -189.4567, 49.3456, -16.7890, 12.8901, -8.9012, 100.0000, 0.0534);

-- --------------------------------------------------------

--
-- Table structure for table `media_file`
--

CREATE TABLE `media_file` (
  `file_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `life_motto` text DEFAULT NULL,
  `pdf_file` varchar(255) DEFAULT NULL,
  `audio_file` varchar(255) DEFAULT NULL,
  `video_file` varchar(255) DEFAULT NULL,
  `file_type` varchar(20) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `upload_file` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media_file`
--

INSERT INTO `media_file` (`file_id`, `student_id`, `life_motto`, `pdf_file`, `audio_file`, `video_file`, `file_type`, `file_size`, `upload_file`, `file_name`) VALUES
(1, 155, NULL, 'uploads/B032410184/report_B032410184.pdf', NULL, NULL, 'pdf', 2457600, '2026-06-25 00:56:05', 'report_B032410184.pdf'),
(2, 201, 'Bismillah in every step, Alhamdulillah in every breath', 'uploads/B032220052/report_B032220052.pdf', NULL, NULL, 'pdf', 3145728, '2026-06-25 00:56:05', 'report_B032220052.pdf'),
(3, 203, 'Just live', 'uploads/B032310641/report_B032310641.pdf', NULL, NULL, 'pdf', 1887436, '2026-06-25 00:56:05', 'report_B032310641.pdf'),
(4, 189, NULL, 'uploads/B032310638/report_B032310638.pdf', NULL, NULL, 'pdf', 2097152, '2026-06-25 00:56:05', 'report_B032310638.pdf'),
(5, 155, NULL, NULL, 'uploads/B032410184/audio_B032410184.mp3', NULL, 'audio', 5242880, '2026-06-25 00:56:05', 'audio_B032410184.mp3'),
(6, 201, 'Bismillah in every step, Alhamdulillah in every breath', NULL, 'uploads/B032220052/audio_B032220052.mp3', NULL, 'audio', 4718592, '2026-06-25 00:56:05', 'audio_B032220052.mp3'),
(7, 203, 'Just live', NULL, 'uploads/B032310641/audio_B032310641.mp3', NULL, 'audio', 3670016, '2026-06-25 00:56:05', 'audio_B032310641.mp3'),
(8, 189, NULL, NULL, 'uploads/B032310638/audio_B032310638.mp3', NULL, 'audio', 6291456, '2026-06-25 00:56:05', 'audio_B032310638.mp3'),
(9, 155, NULL, NULL, NULL, 'uploads/B032410184/video_B032410184.mp4', 'video', 52428800, '2026-06-25 00:56:05', 'video_B032410184.mp4'),
(10, 201, 'Bismillah in every step, Alhamdulillah in every breath', NULL, NULL, 'uploads/B032220052/video_B032220052.mp4', 'video', 78643200, '2026-06-25 00:56:05', 'video_B032220052.mp4'),
(11, 203, 'Just live', NULL, NULL, 'uploads/B032310641/video_B032310641.mp4', 'video', 41943040, '2026-06-25 00:56:05', 'video_B032310641.mp4'),
(12, 189, NULL, NULL, NULL, 'uploads/B032310638/video_B032310638.mp4', 'video', 62914560, '2026-06-25 00:56:05', 'video_B032310638.mp4'),
(13, 131, 'to be positive all the time..', 'uploads/B032310465/report_B032310465.pdf', NULL, NULL, 'pdf', 1572864, '2026-06-25 00:56:05', 'report_B032310465.pdf'),
(14, 126, 'Live without regret', 'uploads/B032420117/report_B032420117.pdf', NULL, NULL, 'pdf', 2621440, '2026-06-25 00:56:05', 'report_B032420117.pdf'),
(15, 167, 'Live everyday like its your last.', NULL, 'uploads/B032410003/audio_B032410003.mp3', NULL, 'audio', 8388608, '2026-06-25 00:56:05', 'audio_B032410003.mp3'),
(16, 150, 'berusaha untuk berjaya', NULL, 'uploads/B032410813/audio_B032410813.mp3', NULL, 'audio', 3145728, '2026-06-25 00:56:05', 'audio_B032410813.mp3'),
(17, 169, 'Hidup Mesti Diteruskan', NULL, 'uploads/B032310571/audio_B032310571.mp3', NULL, 'audio', 4194304, '2026-06-25 00:56:05', 'audio_B032310571.mp3'),
(18, 112, 'sabar , syukur , taqwa', NULL, 'uploads/P02165/audio_P02165.mp3', NULL, 'audio', 5767168, '2026-06-25 00:56:05', 'audio_P02165.mp3');

-- --------------------------------------------------------

--
-- Table structure for table `pdf_text`
--

CREATE TABLE `pdf_text` (
  `text_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `extracted_text` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pdf_text`
--

INSERT INTO `pdf_text` (`text_id`, `file_id`, `extracted_text`) VALUES
(1, 1, 'This report discusses the application of artificial intelligence and machine learning in multimedia database systems. The system was developed at UTeM, Melaka. Keywords: teknologi maklumat, kecerdasan buatan, pangkalan data.'),
(2, 2, 'This document explores content-based retrieval methods for audio and video files. The research was conducted in Johor and focuses on human computer interaction and data mining techniques.'),
(3, 3, 'MediaFind system implements three retrieval approaches: ABR, TBR and CBR. This study covers multimedia database management including audio feature extraction using Librosa library in Python.'),
(4, 4, 'The user interface was designed using React and Next.js framework. The backend uses PHP with MySQL database hosted on a local server. System tested with human users from various groups.'),
(5, 13, 'Kajian ini membincangkan sistem pengurusan data multimedia. Teknologi terkini digunakan untuk memastikan prestasi sistem yang optimum. Projek ini dijalankan di Johor Bahru.'),
(6, 14, 'This report covers human factors in system design and usability testing. Machine learning models were applied to improve retrieval accuracy in the multimedia database.');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `studentID` int(11) NOT NULL,
  `matric_no` varchar(20) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `group_name` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`studentID`, `matric_no`, `student_name`, `phone`, `group_name`) VALUES
(111, 'B0231241', 'Wadi', '0178246', 'GR01'),
(112, 'P02165', 'Norlizam', '065465', 'GR06'),
(113, 'B032420099', 'Muhammad Taufiq Bin Mohd Arifin', '0138742846', 'GS02'),
(114, 'B032420121', 'Nur Sajidah Binti Zanian', '0147480610', 'GS04'),
(115, 'B032510300', 'Nadia Binti Shahrul Azmee', '013-7918004', 'GS05'),
(116, 'B032420087', 'Muhammad Haikal Bin Johari', '0175969369', 'GS05'),
(117, 'B032410815', 'Huda Najihah Binti Suhaimi', '010-8423611', 'GS02'),
(118, 'B032410187', 'Muhammad Nur Azam Bin Mohd Fuad', '0126636032', 'GR01'),
(119, 'B032420153', 'Suhail Amani Binti Mohd Ikbal', '0108404692', 'GS01'),
(120, 'B032510301', 'Muhammad Ammar Harith Bin Jasri', '01123363779', 'GS02'),
(121, 'B032510266', 'Mohamad Faiz Bin Mohd Roshidi', '0193588788', 'GS04'),
(122, 'B032410202', 'Muhammad Afiq Hazim Bin Abd Aziz', '01117964487', 'GS01'),
(123, 'B032510277', 'Izzah Nadhirah Binti Ishak', '0175740124', 'GS03'),
(124, 'B032410195', 'Nur Insyirah Binti Edie Amer', '01161145892', 'GR01'),
(125, 'B032420128', 'Nurhanim Nabila Binti Ab Razak', '01117895604', 'GS05'),
(126, 'B032420117', 'Nur Mahirah Maisarah Binti Mohd Idris', '0176299698', 'GS02'),
(127, 'B032410192', 'Britney Ngieng Fang Yii', '0125140950', 'GS01'),
(128, 'B032420082', 'Muhammad Farhan Bin Mohd Risha', '01110690115', 'GS04'),
(129, 'B032510304', 'Puteri Norshuhada Harris Binti Md Halim Harris', '0196203076', 'GS01'),
(130, 'B032310509', 'Ainnur Athirah Binti Rosli', '0139371920', 'GW01'),
(131, 'B032310465', 'Eilya Filzah Putri Binti Abdullah', '01116804432', 'GW02'),
(132, 'B032310496', 'Pok Wai Yan', '0163700249', 'GW08'),
(133, 'B032310674', 'Nur Izzati Binti Zaidi', '01127717639', 'GW07'),
(134, 'B032310305', 'Ho Sin Ruo', '0194672808', 'GW02'),
(135, 'B032310326', 'Irfan Haziq Bin Rosidi', '0179533403', '3BITDS1G1'),
(136, 'B032310540', 'Nur Batrisyia Balqis Binti Mohd Ferdaus', '0182693710', 'S1G1'),
(137, 'B032310348', 'Nur Aina Binti Fakhruddin', '01110230637', 'GW06'),
(138, 'B032310418', 'Irfah Nadiah Binti Hamdan', '01111676393', 'S1G1'),
(139, 'B032310855', 'Nur Shafiqah Binti Sharip', '01115038510', 'GW02'),
(140, 'B032410181', 'Nur Shazleen Aziem Binti Mat Tan Salleh', '01162345861', 'GW08'),
(141, 'B032220063', 'Jannatul Ferdousi Nahin', '0162070593', 'GW03'),
(142, 'B032310661', 'Muhammad Syameel Amni Bin Mohd Saiful Amri', '0193726014', 'GW06'),
(143, 'B032310655', 'Aniq Afifi Bin Sarli', '0167591873', 'GW01'),
(144, 'B032410818', 'Muhammad Hamdi Bin Hasnim', '0109247175', 'GR01'),
(145, 'B032310715', 'Nur Fasihah Binti Juhari', '01127019571', 'S1G1'),
(146, 'B032410196', 'Nik Nurlyana Syakinah Binti Nik Norazahari', '0183632483', 'GW05'),
(147, 'B032310587', 'Izzatul Wahidah Bt Amir', '01129665274', 'GW02'),
(148, 'B032310833', 'Priyadashwini A/P Yoheswaran', '0164482980', 'GR01'),
(149, 'B032310858', 'Irdina Syafiah Binti Norazman', '0192172812', 'GW09'),
(150, 'B032410813', 'Nurmaisarah Binti Mohd Nor', '0167921669', 'GW08'),
(151, 'B032310742', 'Muhammad Muhaimin Aiman Bin Mohd Rosli', '0103847334', 'GW05'),
(152, 'B032310515', 'Cheng Kah Hooi', '0177032568', 'GW01'),
(153, 'B032410811', 'Kaviarasan A/L Rajeanthiran', '016-7381751', 'GS05'),
(154, 'B032310735', 'Nurul Izzati Nadhirah Binti Iskandar Faidzal', '01110573905', 'GW07'),
(155, 'B032410184', 'Khairul Wajihah Binti Khairuddin', '0163472798', 'GW04'),
(156, 'B032410816', 'Sufiana Adlin Binti Baharom', '01116194436', 'GR09'),
(157, 'B032310838', 'Siti Aisyah Allysa Binti Mohd Nazri', '0173973600', 'GR08'),
(158, 'B032310653', 'Wan Nur Adlin Syauqina Binti Wan Ahmad Fadillah', '01110582101', 'GW07'),
(159, 'B032310080', 'Nur Asyiqin Binti Abdullah', '01111244959', 'GR07'),
(160, 'B032420152', 'Siti Syazlinda Binti Mohmad Zin', '0138531253', 'GR09'),
(161, 'B032510289', 'Aisyah Nur Anieys Najihah Binti Shamsul Anis', '01120794552', 'GR01'),
(162, 'B032310529', 'Wong Zhi Wei', '0149378197', 'GR01'),
(163, 'B032510830', 'Saraneswary A/P Sandran', '0173291160', 'GR06'),
(164, 'B032310246', 'Farah Aqilah Binti Mohd Yani', '0126750238', 'GR03'),
(165, 'B032320103', 'Imran Bin Azlan', '0179796091', 'GR03'),
(166, 'B032310308', 'Tay Fui Poh', '01120589242', 'S1G2'),
(167, 'B032410003', 'Khairul Amri Bin Shamsul Anuar', '0139468231', 'GR04'),
(168, 'B032310345', 'Nadia Amani Binti Zanon', '01112756717', 'GR08'),
(169, 'B032310571', 'Azri Nurul Qaisara Binti Azman', '0194715194', 'GR01'),
(170, 'B032410970', 'Muhammad Asy-Syakur Daniel Bin Suhaimi', '0199577949', 'GR02'),
(171, 'B032310055', 'Ahmad Nazran Bin Shawaluddin', '01123714475', 'GR01'),
(172, 'B032310253', 'Muhammad Halal Bin Achim', '0136822109', 'GR04'),
(173, 'B032310712', 'Nur Aniza Binti Mohd Yusof', '0134078042', 'GR02'),
(174, 'B032310177', 'Ahmad Khurraizy Bin Khuzainol', '01159525357', 'G1S2'),
(175, 'B032510280', 'Muhammad Kamil Bin Mohd Aliashak', '01173775938', 'GR05'),
(176, 'B032410189', 'Azra Natalia Binti Abdullah', '01111697430', 'GR03'),
(177, 'B032420034', 'Farah Damia Binti Mohamad Nizan', '0195121339', 'GR03'),
(178, 'B032420159', 'Tengku Umairah Khadijah Binti Tengku Rithaudden', '0196670664', 'GR07'),
(179, 'B032310592', 'Tan Wei Pin', '0182609939', 'GR07'),
(180, 'B032310499', 'Wardina Safeera Binti Ibrahim', '01160849540', 'GR09'),
(181, 'B032310664', 'Legasheenee Jagathisan', '0195101805', 'GR05'),
(182, 'B032420146', 'Sashvini A/P Shanmugam', '0169207030', 'GR09'),
(183, 'B032310639', 'Amna Najwa Binti Alias', '01136067274', 'GR02'),
(184, 'B032420156', 'Syahindah Binti Azmi', '0179642776', 'GR06'),
(185, 'B032310648', 'Ameerah Maisarah Binti Roszaini', '01121472997', 'GR04'),
(186, 'B032310358', 'Nur Hannah Fatini Binti Mohd Azahar', '0182212448', 'GR07'),
(187, 'B032310479', 'Nurzafirah Anis Binti Mohd Zaini', '01111500627', 'GR06'),
(188, 'B032310390', 'Toh Shuai Ting', '0107730427', 'GW07'),
(189, 'B032310638', 'Muhammad Arifuddin Bin Azman', '01172848164', 'GW04'),
(190, 'B032310424', 'Siti Nuratiqah Binti Abu Bakar', '0137100198', 'GR08'),
(191, 'B032310514', 'Marsya Kamilia Binti Yusrizal', '01169802171', 'GR02'),
(192, 'B032310193', 'Sharifah Yasmin Binti Syd Khalil', '0192924194', 'GK02'),
(193, 'B032420059', 'Mohamad Zaril Aidid Bin Rashid', '0199877163', 'GK02'),
(194, 'B032420127', 'Nureen Amini Binti Fairuz', '0132550227', 'GK02'),
(195, 'B032310211', 'Fatin Nur Faqihah Bt Md Radzi', '0197692562', 'GK02'),
(196, 'B032410817', 'Nur Aina Maisara Binti Asri', '0172911872', 'GR01'),
(197, 'B032410185', 'Muammad Aidil Amani Bin Abdul Rahman', '0189087642', 'GK01'),
(198, 'B032410002', 'Muhammad Haikal Bin Mahadzir', '0132617579', 'GK01'),
(199, 'b032410186', 'Adam Bin Azmi', '0177176957', 'GK01'),
(200, 'B032410200', 'Muhammad Rukaini Aidil', '0174709987', 'GK01'),
(201, 'B032220052', 'Miya Aoyon', '01128766854', 'GW04'),
(202, 'B032420041', 'Heng Huey Jin', '0137121229', 'GR04'),
(203, 'B032310641', 'Miza Binti Mohamad Radzi', '0194569923', 'GW04'),
(204, 'B032410197', 'Siti Maisarah Binti Adzmi', '0165760234', 'GR08'),
(205, 'B032410191', 'Vaanishah A/P Santhyresan', '0167480406', 'GR01'),
(206, 'B032410001', 'Abdul Malik Bin Mustapha', '013-6419607', 'GK01'),
(207, 'B032410182', 'Nur Anis Hazwani Binti Abdul Halim', '0135346421', 'GS03'),
(208, 'B032310381', 'Nur Syarmimi Alia Husna Binti Zaipolbahari', '0175762531', 'GR05'),
(209, 'B032410176', 'Nurul Ain Nasuha Binti Reduan', '0146467663', 'GS04'),
(210, 'B032410183', 'Ain Suriani Binti Zulkefli', '0173382184', 'GR01'),
(211, 'B032310739', 'Nik Arlina Binti Nik Abdul Rahman', '0194603190', 'GS09'),
(212, 'B032420045', 'Izzhilmy Bin Shamsul Bahri', '0197478406', 'GS03'),
(213, 'B032410188', 'Iqma Aqilah Binti Abdul Rahman', '0179696093', 'GS03'),
(214, 'b032310716', 'Adam Dzahir', '0135440733', 'BITD S1G2'),
(215, 'B032420039', 'Hannan Saffiyah Bt Mohd Idris', '01120579682', 'GS05'),
(216, 'B032310374', 'Nur Eliza Binti Anthony', '016-6552805', 'GR01'),
(217, 'B032310134', 'Nur Wahida Binti Noriziady', '0189774015', 'GR06'),
(218, 'B032310153', 'Umar Ashraffi Bin Adnan', '0132447611', 'GW09'),
(219, 'B032420131', 'Nurin Zuhairah Binti Azhar', '0182172476', 'GS04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audio_features`
--
ALTER TABLE `audio_features`
  ADD PRIMARY KEY (`feature_id`),
  ADD KEY `fk_audio_file` (`file_id`);

--
-- Indexes for table `media_file`
--
ALTER TABLE `media_file`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `fk_media_student` (`student_id`);

--
-- Indexes for table `pdf_text`
--
ALTER TABLE `pdf_text`
  ADD PRIMARY KEY (`text_id`),
  ADD KEY `fk_pdf_file` (`file_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`studentID`),
  ADD UNIQUE KEY `matric_no` (`matric_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audio_features`
--
ALTER TABLE `audio_features`
  MODIFY `feature_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `media_file`
--
ALTER TABLE `media_file`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `pdf_text`
--
ALTER TABLE `pdf_text`
  MODIFY `text_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `studentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audio_features`
--
ALTER TABLE `audio_features`
  ADD CONSTRAINT `fk_audio_file` FOREIGN KEY (`file_id`) REFERENCES `media_file` (`file_id`) ON DELETE CASCADE;

--
-- Constraints for table `media_file`
--
ALTER TABLE `media_file`
  ADD CONSTRAINT `fk_media_student` FOREIGN KEY (`student_id`) REFERENCES `student` (`studentID`) ON DELETE CASCADE;

--
-- Constraints for table `pdf_text`
--
ALTER TABLE `pdf_text`
  ADD CONSTRAINT `fk_pdf_file` FOREIGN KEY (`file_id`) REFERENCES `media_file` (`file_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
