<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
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

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = mysqli_real_escape_string($data, $_POST['name']);
    $email = mysqli_real_escape_string($data, $_POST['email']);
    $phone = mysqli_real_escape_string($data, $_POST['phone']);
    $specialization = mysqli_real_escape_string($data, $_POST['specialization']);
    $qualification = mysqli_real_escape_string($data, $_POST['qualification']);
    $experience_years = (int)$_POST['experience_years'];
    $bio = mysqli_real_escape_string($data, $_POST['bio']);
    $department = mysqli_real_escape_string($data, $_POST['department']);
    $office_location = mysqli_real_escape_string($data, $_POST['office_location']);
    $office_hours = mysqli_real_escape_string($data, $_POST['office_hours']);
    $linkedin_url = mysqli_real_escape_string($data, $_POST['linkedin_url']);
    $twitter_url = mysqli_real_escape_string($data, $_POST['twitter_url']);
    $facebook_url = mysqli_real_escape_string($data, $_POST['facebook_url']);
    $salary = !empty($_POST['salary']) ? (float)$_POST['salary'] : null;
    $hire_date = !empty($_POST['hire_date']) ? $_POST['hire_date'] : null;
    $status = $_POST['status'];

    // Handle image upload
    $image_path = 'default_teacher.jpg'; // Default image
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = "uploads/teachers/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = time() . '_' . uniqid() . '.' . $file_extension;
            $image_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                // File uploaded successfully
            } else {
                $message = "Error uploading image. Using default image.";
                $image_path = 'default_teacher.jpg';
            }
        } else {
            $message = "Invalid image type. Only JPG, PNG, and GIF allowed. Using default image.";
            $image_path = 'default_teacher.jpg';
        }
    }

    // Check if email already exists
    $check_email = mysqli_query($data, "SELECT id FROM teachers WHERE email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $message = "Error: Email address already exists!";
    } else {
        // Insert teacher data - FIXED: Correct parameter count
        $sql = "INSERT INTO teachers (
            name, email, phone, specialization, qualification, experience_years, 
            bio, image, department, office_location, office_hours, linkedin_url, 
            twitter_url, facebook_url, salary, hire_date, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($data, $sql);
        mysqli_stmt_bind_param($stmt, "sssssissssssssds", 
            $name, $email, $phone, $specialization, $qualification, $experience_years,
            $bio, $image_path, $department, $office_location, $office_hours,
            $linkedin_url, $twitter_url, $facebook_url, $salary, $hire_date, $status
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "Teacher added successfully!";
            // Clear form data after successful submission
            $_POST = array();
        } else {
            $message = "Error adding teacher: " . mysqli_error($data);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Teacher - Admin Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --border-color: #e5e7eb;
        }
        
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: var(--light-color);
        }
        
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            padding: 2rem;
        }
        
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }
        
        .form-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem 2rem;
        }
        
        .form-body {
            padding: 2rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid var(--border-color);
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .image-preview {
            width: 150px;
            height: 150px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid var(--border-color);
        }
        
        .required {
            color: var(--danger-color);
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Include Sidebar -->
    <?php include 'adminsidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-chalkboard-teacher me-3"></i>
                Add New Teacher
            </h1>
            <p class="page-subtitle">Register a new teacher to the academy</p>
        </div>

        <!-- Form Card -->
        <div class="form-card">
            <div class="form-header">
                <h3 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>
                    Teacher Information
                </h3>
            </div>
            
            <div class="form-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo strpos($message, 'Error') !== false ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo strpos($message, 'Error') !== false ? 'exclamation-triangle' : 'check-circle'; ?> me-2"></i>
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-lg-8">
                            <h5 class="mb-3 text-primary">
                                <i class="fas fa-user me-2"></i>Basic Information
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="specialization" class="form-label">Specialization <span class="required">*</span></label>
                                    <select class="form-select" id="specialization" name="specialization" required>
                                        <option value="">Select Specialization</option>
                                        <option value="Mathematics" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Mathematics') ? 'selected' : ''; ?>>Mathematics</option>
                                        <option value="Physics" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Physics') ? 'selected' : ''; ?>>Physics</option>
                                        <option value="Computer Science" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                                        <option value="Statistics" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Statistics') ? 'selected' : ''; ?>>Statistics</option>
                                        <option value="Business Administration" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Business Administration') ? 'selected' : ''; ?>>Business Administration</option>
                                        <option value="Engineering" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Engineering') ? 'selected' : ''; ?>>Engineering</option>
                                        <option value="Chemistry" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Chemistry') ? 'selected' : ''; ?>>Chemistry</option>
                                        <option value="Biology" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Biology') ? 'selected' : ''; ?>>Biology</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="qualification" class="form-label">Qualification <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="qualification" name="qualification" 
                                           placeholder="e.g., Ph.D. in Mathematics" 
                                           value="<?php echo isset($_POST['qualification']) ? htmlspecialchars($_POST['qualification']) : ''; ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="experience_years" class="form-label">Years of Experience</label>
                                    <input type="number" class="form-control" id="experience_years" name="experience_years" 
                                           min="0" max="50" 
                                           value="<?php echo isset($_POST['experience_years']) ? htmlspecialchars($_POST['experience_years']) : '0'; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="bio" class="form-label">Biography</label>
                                <textarea class="form-control" id="bio" name="bio" rows="4" 
                                          placeholder="Brief description of the teacher's background, expertise, and achievements..."><?php echo isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : ''; ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Image Upload -->
                        <div class="col-lg-4">
                            <h5 class="mb-3 text-primary">
                                <i class="fas fa-image me-2"></i>Profile Image
                            </h5>
                            
                            <div class="text-center mb-3">
                                <img id="imagePreview" src="default_teacher.jpg" alt="Teacher Image" class="image-preview">
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Upload Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <small class="text-muted">JPG, PNG, GIF (Max 5MB)</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Department Information -->
                    <hr class="my-4">
                    <h5 class="mb-3 text-primary">
                        <i class="fas fa-building me-2"></i>Department Information
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" class="form-control" id="department" name="department" 
                                   placeholder="e.g., Mathematics Department" 
                                   value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''; ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="office_location" class="form-label">Office Location</label>
                            <input type="text" class="form-control" id="office_location" name="office_location" 
                                   placeholder="e.g., Room 201, Science Building" 
                                   value="<?php echo isset($_POST['office_location']) ? htmlspecialchars($_POST['office_location']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="office_hours" class="form-label">Office Hours</label>
                            <textarea class="form-control" id="office_hours" name="office_hours" rows="2" 
                                      placeholder="e.g., Monday - Friday: 9:00 AM - 5:00 PM"><?php echo isset($_POST['office_hours']) ? htmlspecialchars($_POST['office_hours']) : ''; ?></textarea>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="hire_date" class="form-label">Hire Date</label>
                            <input type="date" class="form-control" id="hire_date" name="hire_date" 
                                   value="<?php echo isset($_POST['hire_date']) ? htmlspecialchars($_POST['hire_date']) : ''; ?>">
                        </div>
                    </div>
                    
                    <!-- Social Media & Additional Info -->
                    <hr class="my-4">
                    <h5 class="mb-3 text-primary">
                        <i class="fas fa-share-alt me-2"></i>Social Media & Additional Information
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                            <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" 
                                   placeholder="https://linkedin.com/in/username" 
                                   value="<?php echo isset($_POST['linkedin_url']) ? htmlspecialchars($_POST['linkedin_url']) : ''; ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="twitter_url" class="form-label">Twitter URL</label>
                            <input type="url" class="form-control" id="twitter_url" name="twitter_url" 
                                   placeholder="https://twitter.com/username" 
                                   value="<?php echo isset($_POST['twitter_url']) ? htmlspecialchars($_POST['twitter_url']) : ''; ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="facebook_url" class="form-label">Facebook URL</label>
                            <input type="url" class="form-control" id="facebook_url" name="facebook_url" 
                                   placeholder="https://facebook.com/username" 
                                   value="<?php echo isset($_POST['facebook_url']) ? htmlspecialchars($_POST['facebook_url']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="salary" class="form-label">Salary (Optional)</label>
                            <input type="number" class="form-control" id="salary" name="salary" 
                                   step="0.01" min="0" 
                                   placeholder="Annual salary" 
                                   value="<?php echo isset($_POST['salary']) ? htmlspecialchars($_POST['salary']) : ''; ?>">
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <hr class="my-4">
                    <div class="d-flex justify-content-between">
                        <a href="adminhome.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                        
                        <div>
                            <button type="reset" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-undo me-2"></i>Reset Form
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Add Teacher
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html> 