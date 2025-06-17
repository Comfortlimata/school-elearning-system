# 🎓 E-Learning Platform – School Project

This is a web-based **E-Learning Platform** designed as part of a school project for the course *Information Systems and Technology*. 
The platform allows students to register, log in, access course materials, and interact with uploaded resources. 
Admins can upload and manage course documents, announcements, and user roles.

---

## 📚 Features

### 👨‍🏫 For Students

* Register and log in securely
* View available courses and materials
* Download documents (PDFs, DOCs, etc.)
* View announcements or messages from instructors

### 👩‍🏫 For Admin/Instructor

* Admin login panel
* Upload and manage course documents
* Post announcements and updates
* Manage user accounts (students)

---

## 🛠️ Technologies Used

* **Frontend**: HTML5, CSS3, JavaScript
* **Backend**: PHP 8+
* **Database**: MySQL
* **Development Environment**: XAMPP / Laragon / Localhost
* **Editor**: Sublime Text / Visual Studio Code

---

## 📁 Folder Structure

```
e-learning/
├── admin/
│   ├── dashboard.php
│   ├── upload_material.php
│   └── manage_users.php
├── student/
│   ├── login.php
│   ├── register.php
│   ├── courses.php
│   └── downloads.php
├── assets/
│   ├── css/
│   └── js/
├── uploads/
│   └── [PDF and course materials]
├── index.php
├── about.php
├── contact.php
└── README.md
```

---

## 🔐 Login Details (Demo)

### Admin

* **Username**: `admin`
* **Password**: `admin123`

### Student

* **Username**: `student@example.com`
* **Password**: `student123`

> **Note:** These are demo credentials. Please change them in production.

---

## ⚙️ How to Set Up Locally

1. **Clone the repository:**

   ```bash
   git clone https://github.com/comfortlimata/schoolproject.git
   ```

2. **Move the project folder to your server directory:**

   * For XAMPP: `htdocs/`
   * For Laragon: `www/`

3. **Create the database:**

   * Open `phpMyAdmin`
   * Create a new database (e.g., `elearning_db`)
   * Import the provided `elearning.sql` file (if available)

4. **Update the database connection in `db.php`:**

   ```php
   $conn = new mysqli("localhost", "root", "", "elearning_db");
   ```

5. **Run the application:**
   Open your browser and navigate to:

   ```
   http://localhost/schoolproject/
   ```

---

## 🚀 Future Enhancements

* Add video streaming (YouTube or self-hosted)
* Enable student comments or discussion threads
* Add grading and quiz functionality
* Notifications for new materials

---

## 🧑‍💼 Developed By

**Comfort Limata**
*Information Systems and Technology Student – Zambia*
GitHub: [https://github.com/comfortlimata](https://github.com/comfortlimata)

---

## 📄 License

This project is for **educational purposes** and is open-source under the [MIT License](LICENSE).
