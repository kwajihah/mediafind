# 📂 MediaFind — Multimedia Database Retrieval System

**Course:** BITP3353 Multimedia Database  
**Group:** GW04 · Universiti Teknikal Malaysia Melaka (UTeM)  
**Lecturer:** Ts. Dr. Hidayah Bt. Rahmalan

---

## 👥 Group Members

| No | Name | Matric No | Role |
|----|------|-----------|------|
| 1 | Khairul Wajihah Binti Khairuddin | B032410184 | Database Designer |
| 2 | Miya Aoyon | B032220052 | System Analyst |
| 3 | Miza Binti Mohamad Radzi | B032310641 | UI Developer |
| 4 | Muhammad Arifuddin Bin Azman |B032310638 | Backend Developer |

---

## 📋 Project Overview

MediaFind is a web-based multimedia database retrieval system built with **PHP + MySQL** that supports three retrieval methods:

| Method | Description |
|--------|-------------|
| **ABR** — Attribute-Based Retrieval | Filter by file type, size, group, matric number |
| **TBR** — Text-Based Retrieval | Keyword search across names, mottos, file names |
| **CBR** — Content-Based Retrieval | Search by mood label, video resolution, audio features |

---

## 🗄 Database Schema

**Tables:** `students`, `files`  
**Database:** `gw_04` (XAMPP local) / `gw04` (UTeM server)

Import `GW04.sql` into phpMyAdmin to set up all tables and sample data.

---

## 🚀 How to Run (XAMPP)

1. Start **Apache** and **MySQL** in XAMPP Control Panel
2. Open `http://localhost/phpmyadmin`
3. Click database `gw_04` → **SQL tab** → paste contents of `GW04.sql` → **Go**
4. Copy this project folder into `C:\xampp\htdocs\grp_project\`
5. Open browser → `http://localhost/grp_project/index.php`

---

## 📁 Project Structure

```
mediafind/
├── .github/workflows/    # CI workflows
├── includes/
│   ├── db_connect_utem.php   # Database connection (PDO)
│   ├── header.php            # Shared HTML header + nav
│   └── footer.php            # Shared HTML footer
├── public/               # Images and static assets
├── GW04.sql              # Database schema + sample data
├── composer.json         # PHP project config
├── index.php             # Dashboard homepage
├── search.php            # ABR / TBR / CBR retrieval
└── student.php           # Student list and search
```

---

## 🔗 Live Demo

[bitp3353.utem.edu.my/2026/all/GroupMDB/GW04/](https://bitp3353.utem.edu.my/2026/all/GroupMDB/GW04/)
