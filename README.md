# SAFEDOCS – Secure Document Storage Web Application

SAFEDOCS is a **web-based document management system** developed using **PHP and MySQL**.
It allows users to securely upload, store, and manage important personal documents through a web interface.

The system includes **role-based access control** for **Admin and Users**, secure file upload validation, and storage management features.

---

## 🚀 Features

* User registration and authentication
* Admin login and dashboard
* Secure document upload and storage
* Document retrieval and management
* Password reset functionality
* Role-based access control (Admin & User)

### 🔒 Security & Storage Controls

* File type validation (**PDF and TXT files only**)
* Maximum file upload size: **10 MB per file**
* Per-user storage quota: **100 MB total storage**
* System tracks total storage used by each user

---

## 🛠 Technologies Used

* **Backend:** PHP
* **Database:** MySQL
* **Frontend:** HTML, CSS
* **Server:** Apache (XAMPP)

---

## 📂 Project Structure

```
SAFEDOCS
│
├── css
├── js
├── uploads
├── admin
├── user
├── config
├── login.php
├── register.php
└── upload.php
```

---

## ⚙️ Setup Instructions

1. Install **XAMPP**
2. Copy the project folder into the **htdocs** directory

Example:

```
C:\xampp\htdocs\safedocs
```

3. Start **Apache** and **MySQL** in XAMPP

---

## 🗄 Database Setup

1. Open **phpMyAdmin**
2. Create a new database named:

```
safedocs
```

3. Import the database file:

```
dataa.sql
```

---

## ▶️ Run the Application

Open your browser and navigate to:

```
http://localhost/safedocs
```

---

## 📌 Notes

* Uploaded files are stored in the **uploads directory**
* Only **PDF and TXT files** are allowed
* Each file must be **10 MB or smaller**
* Each user has a **maximum storage limit of 100 MB**

---

## 👨‍💻 Author

**Ganapathy S**
GitHub: https://github.com/Ganapathy-s22
