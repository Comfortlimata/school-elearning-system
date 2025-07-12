<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
} elseif ($_SESSION['usertype'] == 'student') {
    header("location:login.php");
    exit();
}

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = mysqli_connect($host, $user, $password, $db);

if (!$data) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_content'])) {
        $hero_title = mysqli_real_escape_string($data, $_POST['hero_title']);
        $hero_subtitle = mysqli_real_escape_string($data, $_POST['hero_subtitle']);
        $about_title = mysqli_real_escape_string($data, $_POST['about_title']);
        $about_content = mysqli_real_escape_string($data, $_POST['about_content']);
        $contact_address = mysqli_real_escape_string($data, $_POST['contact_address']);
        $contact_phone = mysqli_real_escape_string($data, $_POST['contact_phone']);
        $contact_email = mysqli_real_escape_string($data, $_POST['contact_email']);
        $contact_hours = mysqli_real_escape_string($data, $_POST['contact_hours']);
        
        // Update content in database
        $update_sql = "UPDATE website_content SET 
            hero_title = ?, 
            hero_subtitle = ?, 
            about_title = ?, 
            about_content = ?, 
            contact_address = ?, 
            contact_phone = ?, 
            contact_email = ?, 
            contact_hours = ?,
            updated_at = NOW()
            WHERE id = 1";
            
        $stmt = mysqli_prepare($data, $update_sql);
        mysqli_stmt_bind_param($stmt, "ssssssss", $hero_title, $hero_subtitle, $about_title, $about_content, $contact_address, $contact_phone, $contact_email, $contact_hours);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Content updated successfully!";
        } else {
            $error_message = "Failed to update content. Please try again.";
        }
    }
}

// Get current content
$content_sql = "SELECT * FROM website_content WHERE id = 1";
$content_result = mysqli_query($data, $content_sql);

if (mysqli_num_rows($content_result) > 0) {
    $content = mysqli_fetch_assoc($content_result);
} else {
    // Default content if no record exists
    $content = [
        'hero_title' => 'Excellence in Education',
        'hero_subtitle' => 'Empowering students with knowledge, skills, and values for a brighter future.',
        'about_title' => 'Welcome to Comfort e-School Academy',
        'about_content' => 'At Comfort e-School Academy, we believe that learning is a journey — and sometimes, it takes a few extra miles! Our students don\'t just stop at the basics; they zoom past the ordinary with curiosity, creativity, and a healthy dose of determination.',
        'contact_address' => '123 Education Street, Academic District, City, Country',
        'contact_phone' => '+1 (555) 123-4567',
        'contact_email' => 'info@comfortacademy.edu',
        'contact_hours' => 'Monday - Friday: 8:00 AM - 6:00 PM\nSaturday: 9:00 AM - 2:00 PM'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Content Management - Admin Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="style.css">
    
    <style>
        :root {
            --sidebar-width: 280px;
            --header-height: 70px;
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --border-color: #e5e7eb;
        }
        
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: var(--light-color);
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }
        
        .sidebar-brand:hover {
            color: white;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .sidebar-item {
            margin: 0.5rem 1rem;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .sidebar-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .sidebar-icon {
            width: 20px;
            margin-right: 0.75rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s ease;
        }
        
        /* Header */
        .top-header {
            background: white;
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .btn-logout {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            background: #dc2626;
            color: white;
        }
        
        /* Content Management Styles */
        .content-section {
            padding: 2rem;
        }
        
        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .content-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
        }
        
        .content-body {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }
        
        .preview-section {
            background: var(--light-color);
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        .preview-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 1rem;
        }
        
        .preview-content {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .content-section {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="adminhome.php" class="sidebar-brand">
                <i class="fas fa-graduation-cap me-2"></i>
                Admin Panel
            </a>
        </div>
        
        <div class="sidebar-menu">
            <div class="sidebar-item">
                <a href="adminhome.php" class="sidebar-link">
                    <i class="fas fa-tachometer-alt sidebar-icon"></i>
                    Dashboard
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="content_management.php" class="sidebar-link active">
                    <i class="fas fa-edit sidebar-icon"></i>
                    Content Management
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="admission.php" class="sidebar-link">
                    <i class="fas fa-user-plus sidebar-icon"></i>
                    Admissions
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="add_student.php" class="sidebar-link">
                    <i class="fas fa-user-graduate sidebar-icon"></i>
                    Add Student
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="view_student.php" class="sidebar-link">
                    <i class="fas fa-users sidebar-icon"></i>
                    View Students
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="add_teacher_auth.php" class="sidebar-link">
                    <i class="fas fa-chalkboard-teacher sidebar-icon"></i>
                    Add Teacher
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="add_courses.php" class="sidebar-link">
                    <i class="fas fa-book sidebar-icon"></i>
                    Add Courses
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="view_courses.php" class="sidebar-link">
                    <i class="fas fa-list sidebar-icon"></i>
                    View Courses
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="top-header">
            <div class="header-title">
                <i class="fas fa-edit me-2"></i>
                Content Management
            </div>
            <div class="header-actions">
                <a href="adminhome.php" class="btn btn-outline-primary">
                    <i class="fas fa-home me-1"></i>
                    Dashboard
                </a>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Content Management -->
        <div class="container-fluid p-4">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Content Management Form -->
            <div class="content-section">
                <div class="content-card">
                    <div class="content-header">
                        <h3><i class="fas fa-edit me-2"></i>Website Content Management</h3>
                        <p class="mb-0">Update the content displayed on the homepage</p>
                    </div>
                    <div class="content-body">
                        <form method="POST">
                            <!-- Hero Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="mb-3">Hero Section</h4>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Hero Title</label>
                                        <input type="text" name="hero_title" class="form-control" value="<?php echo htmlspecialchars($content['hero_title']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Hero Subtitle</label>
                                        <textarea name="hero_subtitle" class="form-control" rows="3" required><?php echo htmlspecialchars($content['hero_subtitle']); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- About Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="mb-3">About Section</h4>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">About Title</label>
                                        <input type="text" name="about_title" class="form-control" value="<?php echo htmlspecialchars($content['about_title']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">About Content</label>
                                        <textarea name="about_content" class="form-control" rows="4" required><?php echo htmlspecialchars($content['about_content']); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="mb-3">Contact Information</h4>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Address</label>
                                        <textarea name="contact_address" class="form-control" rows="2" required><?php echo htmlspecialchars($content['contact_address']); ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" name="contact_phone" class="form-control" value="<?php echo htmlspecialchars($content['contact_phone']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($content['contact_email']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Office Hours</label>
                                        <textarea name="contact_hours" class="form-control" rows="2" required><?php echo htmlspecialchars($content['contact_hours']); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview Section -->
                            <div class="preview-section">
                                <div class="preview-title">Content Preview</div>
                                <div class="preview-content">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Hero Section</h5>
                                            <h3><?php echo htmlspecialchars($content['hero_title']); ?></h3>
                                            <p class="text-muted"><?php echo htmlspecialchars($content['hero_subtitle']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>About Section</h5>
                                            <h4><?php echo htmlspecialchars($content['about_title']); ?></h4>
                                            <p><?php echo htmlspecialchars($content['about_content']); ?></p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-12">
                                            <h5>Contact Information</h5>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>Address:</strong><br>
                                                    <small><?php echo nl2br(htmlspecialchars($content['contact_address'])); ?></small>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>Phone:</strong><br>
                                                    <small><?php echo htmlspecialchars($content['contact_phone']); ?></small>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>Email:</strong><br>
                                                    <small><?php echo htmlspecialchars($content['contact_email']); ?></small>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>Hours:</strong><br>
                                                    <small><?php echo nl2br(htmlspecialchars($content['contact_hours'])); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" name="update_content" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update All Content
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
    
    // Real-time preview update
    const formInputs = document.querySelectorAll('input, textarea');
    formInputs.forEach(input => {
        input.addEventListener('input', updatePreview);
    });
    
    function updatePreview() {
        const heroTitle = document.querySelector('input[name="hero_title"]').value;
        const heroSubtitle = document.querySelector('textarea[name="hero_subtitle"]').value;
        const aboutTitle = document.querySelector('input[name="about_title"]').value;
        const aboutContent = document.querySelector('textarea[name="about_content"]').value;
        const contactAddress = document.querySelector('textarea[name="contact_address"]').value;
        const contactPhone = document.querySelector('input[name="contact_phone"]').value;
        const contactEmail = document.querySelector('input[name="contact_email"]').value;
        const contactHours = document.querySelector('textarea[name="contact_hours"]').value;
        
        // Update preview content
        const previewHeroTitle = document.querySelector('.preview-content h3');
        const previewHeroSubtitle = document.querySelector('.preview-content .text-muted');
        const previewAboutTitle = document.querySelector('.preview-content h4');
        const previewAboutContent = document.querySelector('.preview-content p:last-of-type');
        
        if (previewHeroTitle) previewHeroTitle.textContent = heroTitle;
        if (previewHeroSubtitle) previewHeroSubtitle.textContent = heroSubtitle;
        if (previewAboutTitle) previewAboutTitle.textContent = aboutTitle;
        if (previewAboutContent) previewAboutContent.textContent = aboutContent;
    }
</script>
</body>
</html>
      