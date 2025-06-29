<?php
error_reporting(0);
session_start();

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    echo "<script type='text/javascript'>
        alert('$message');
    </script>";
    unset($_SESSION['message']);
}

$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = mysqli_connect($host, $user, $password, $db);

// Get website content from database
$content_sql = "SELECT * FROM website_content WHERE id = 1";
$content_result = mysqli_query($data, $content_sql);

if (mysqli_num_rows($content_result) > 0) {
    $content = mysqli_fetch_assoc($content_result);
} else {
    // Default content if no record exists
    $content = [
        'hero_title' => 'Excellence in Education',
        'hero_subtitle' => 'Empowering students with knowledge, skills, and values for a brighter future. Join our community of learners and discover your potential.',
        'about_title' => 'Welcome to Miles e-School Academy',
        'about_content' => 'At Miles e-School Academy, we believe that learning is a journey — and sometimes, it takes a few extra miles! Our students don\'t just stop at the basics; they zoom past the ordinary with curiosity, creativity, and a healthy dose of determination.',
        'contact_address' => '123 Education Street, Academic District, City, Country',
        'contact_phone' => '+1 (555) 123-4567',
        'contact_email' => 'info@milesacademy.edu',
        'contact_hours' => 'Monday - Friday: 8:00 AM - 6:00 PM\nSaturday: 9:00 AM - 2:00 PM'
    ];
}

$sql = "SELECT * FROM teacher";
$result = mysqli_query($data, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Miles e-School Academy - Excellence in Education</title>

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
			--primary-color: #2563eb;
			--secondary-color: #1e40af;
			--accent-color: #f59e0b;
			--text-dark: #1f2937;
			--text-light: #6b7280;
			--bg-light: #f8fafc;
			--white: #ffffff;
		}
		
		* {
			font-family: 'Poppins', sans-serif;
		}
		
		.navbar {
			background: rgba(37, 99, 235, 0.95) !important;
			backdrop-filter: blur(10px);
			transition: all 0.3s ease;
		}
		
		.navbar-brand {
			font-weight: 700;
			font-size: 1.5rem;
		}
		
		.nav-link {
			font-weight: 500;
			transition: all 0.3s ease;
		}
		
		.nav-link:hover {
			color: var(--accent-color) !important;
		}
		
		.hero-section {
			background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
			color: white;
			padding: 120px 0 80px;
			position: relative;
			overflow: hidden;
		}
		
		.hero-section::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: url('index.jpeg') center/cover;
			opacity: 0.1;
			z-index: 1;
		}
		
		.hero-content {
			position: relative;
			z-index: 2;
		}
		
		.hero-title {
			font-size: 3.5rem;
			font-weight: 700;
			margin-bottom: 1.5rem;
		}
		
		.hero-subtitle {
			font-size: 1.25rem;
			margin-bottom: 2rem;
			opacity: 0.9;
		}
		
		.btn-hero {
			padding: 12px 30px;
			font-weight: 600;
			border-radius: 50px;
			transition: all 0.3s ease;
		}
		
		.section-title {
			font-size: 2.5rem;
			font-weight: 700;
			text-align: center;
			margin-bottom: 3rem;
			color: var(--text-dark);
		}
		
		.section-subtitle {
			text-align: center;
			color: var(--text-light);
			font-size: 1.1rem;
			margin-bottom: 4rem;
		}
		
		.feature-card {
			background: var(--white);
			border-radius: 15px;
			padding: 2rem;
			text-align: center;
			box-shadow: 0 10px 30px rgba(0,0,0,0.1);
			transition: all 0.3s ease;
			height: 100%;
		}
		
		.feature-card:hover {
			transform: translateY(-10px);
			box-shadow: 0 20px 40px rgba(0,0,0,0.15);
		}
		
		.feature-icon {
			width: 80px;
			height: 80px;
			background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0 auto 1.5rem;
			color: white;
			font-size: 2rem;
		}
		
		.course-card {
			background: var(--white);
			border-radius: 15px;
			overflow: hidden;
			box-shadow: 0 10px 30px rgba(0,0,0,0.1);
			transition: all 0.3s ease;
			height: 100%;
		}
		
		.course-card:hover {
			transform: translateY(-5px);
			box-shadow: 0 20px 40px rgba(0,0,0,0.15);
		}
		
		.course-image {
			height: 200px;
			object-fit: cover;
			width: 100%;
		}
		
		.teacher-card {
			background: var(--white);
			border-radius: 15px;
			overflow: hidden;
			box-shadow: 0 10px 30px rgba(0,0,0,0.1);
			transition: all 0.3s ease;
			height: 100%;
		}
		
		.teacher-card:hover {
			transform: translateY(-5px);
			box-shadow: 0 20px 40px rgba(0,0,0,0.15);
		}
		
		.teacher-image {
			height: 250px;
			object-fit: cover;
			width: 100%;
		}
		
		.stats-section {
			background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
			color: white;
			padding: 80px 0;
		}
		
		.stat-item {
			text-align: center;
		}
		
		.stat-number {
			font-size: 3rem;
			font-weight: 700;
			margin-bottom: 0.5rem;
		}
		
		.stat-label {
			font-size: 1.1rem;
			opacity: 0.9;
		}
		
		.testimonial-card {
			background: var(--white);
			border-radius: 15px;
			padding: 2rem;
			box-shadow: 0 10px 30px rgba(0,0,0,0.1);
			text-align: center;
			height: 100%;
		}
		
		.testimonial-avatar {
			width: 80px;
			height: 80px;
			border-radius: 50%;
			object-fit: cover;
			margin: 0 auto 1rem;
		}
		
		.contact-info {
			background: var(--bg-light);
			border-radius: 15px;
			padding: 2rem;
			height: 100%;
		}
		
		.contact-item {
			display: flex;
			align-items: center;
			margin-bottom: 1.5rem;
		}
		
		.contact-icon {
			width: 50px;
			height: 50px;
			background: var(--primary-color);
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			color: white;
			margin-right: 1rem;
		}
		
		.footer {
			background: var(--text-dark);
			color: white;
			padding: 60px 0 20px;
		}
		
		.footer-section h5 {
			color: var(--accent-color);
			margin-bottom: 1.5rem;
		}
		
		.footer-section ul {
			list-style: none;
			padding: 0;
		}
		
		.footer-section ul li {
			margin-bottom: 0.5rem;
		}
		
		.footer-section ul li a {
			color: #9ca3af;
			text-decoration: none;
			transition: color 0.3s ease;
		}
		
		.footer-section ul li a:hover {
			color: var(--accent-color);
		}
		
		.social-links a {
			display: inline-block;
			width: 40px;
			height: 40px;
			background: var(--primary-color);
			color: white;
			border-radius: 50%;
			text-align: center;
			line-height: 40px;
			margin-right: 10px;
			transition: all 0.3s ease;
		}
		
		.social-links a:hover {
			background: var(--accent-color);
			transform: translateY(-3px);
		}
		
		.admission-form {
			background: var(--white);
			border-radius: 15px;
			box-shadow: 0 10px 30px rgba(0,0,0,0.1);
			overflow: hidden;
		}
		
		.form-header {
			background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
			color: white;
			padding: 2rem;
			text-align: center;
		}
		
		.form-body {
			padding: 2rem;
		}
		
		.form-control {
			border-radius: 10px;
			border: 2px solid #e5e7eb;
			padding: 12px 15px;
			transition: all 0.3s ease;
		}
		
		.form-control:focus {
			border-color: var(--primary-color);
			box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
		}
		
		.btn-primary {
			background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
			border: none;
			border-radius: 50px;
			padding: 12px 30px;
			font-weight: 600;
			transition: all 0.3s ease;
		}
		
		.btn-primary:hover {
			transform: translateY(-2px);
			box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
		}
		
		@media (max-width: 768px) {
			.hero-title {
				font-size: 2.5rem;
			}
			
			.section-title {
				font-size: 2rem;
			}
		}
	</style>
</head>
<body>
	<!-- Navigation -->
	<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
		<div class="container">
			<a class="navbar-brand" href="#">
				<i class="fas fa-graduation-cap me-2"></i>
				Miles e-School Academy
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav ms-auto">
					<li class="nav-item">
						<a class="nav-link" href="#home">Home</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#about">About</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#courses">Courses</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#teachers">Teachers</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#contact">Contact</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
							<i class="fas fa-sign-in-alt me-1"></i>Login
						</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>

	<!-- Login Modal -->
	<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="loginModalLabel">
						<i class="fas fa-graduation-cap me-2"></i>Welcome to Miles Academy
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<!-- Login Type Tabs -->
					<ul class="nav nav-pills nav-fill mb-4" id="loginTabs" role="tablist">
						<li class="nav-item" role="presentation">
							<button class="nav-link active" id="student-tab" data-bs-toggle="pill" data-bs-target="#student-login" type="button" role="tab">
								<i class="fas fa-user-graduate me-2"></i>Student
							</button>
						</li>
						<li class="nav-item" role="presentation">
							<button class="nav-link" id="teacher-tab" data-bs-toggle="pill" data-bs-target="#teacher-login" type="button" role="tab">
								<i class="fas fa-chalkboard-teacher me-2"></i>Teacher
							</button>
						</li>
						<li class="nav-item" role="presentation">
							<button class="nav-link" id="admin-tab" data-bs-toggle="pill" data-bs-target="#admin-login" type="button" role="tab">
								<i class="fas fa-user-shield me-2"></i>Admin
							</button>
						</li>
					</ul>

					<!-- Tab Content -->
					<div class="tab-content" id="loginTabContent">
						<!-- Student Login -->
						<div class="tab-pane fade show active" id="student-login" role="tabpanel">
							<form action="login.php" method="POST">
								<input type="hidden" name="usertype" value="student">
								<div class="mb-3">
									<label for="student-username" class="form-label">Username</label>
									<div class="input-group">
										<span class="input-group-text"><i class="fas fa-user"></i></span>
										<input type="text" class="form-control" id="student-username" name="username" required>
									</div>
								</div>
								<div class="mb-3">
									<label for="student-password" class="form-label">Password</label>
									<div class="input-group">
										<span class="input-group-text"><i class="fas fa-lock"></i></span>
										<input type="password" class="form-control" id="student-password" name="password" required>
									</div>
								</div>
								<div class="mb-3 form-check">
									<input type="checkbox" class="form-check-input" id="student-remember">
									<label class="form-check-label" for="student-remember">Remember me</label>
								</div>
								<button type="submit" class="btn btn-primary w-100">
									<i class="fas fa-sign-in-alt me-2"></i>Student Login
								</button>
							</form>
						</div>

						<!-- Teacher Login -->
						<div class="tab-pane fade" id="teacher-login" role="tabpanel">
							<form action="login.php" method="POST">
								<input type="hidden" name="usertype" value="teacher">
								<div class="mb-3">
									<label for="teacher-username" class="form-label">Username</label>
									<div class="input-group">
										<span class="input-group-text"><i class="fas fa-user"></i></span>
										<input type="text" class="form-control" id="teacher-username" name="username" required>
									</div>
								</div>
								<div class="mb-3">
									<label for="teacher-password" class="form-label">Password</label>
									<div class="input-group">
										<span class="input-group-text"><i class="fas fa-lock"></i></span>
										<input type="password" class="form-control" id="teacher-password" name="password" required>
									</div>
								</div>
								<div class="mb-3 form-check">
									<input type="checkbox" class="form-check-input" id="teacher-remember">
									<label class="form-check-label" for="teacher-remember">Remember me</label>
								</div>
								<button type="submit" class="btn btn-success w-100">
									<i class="fas fa-chalkboard-teacher me-2"></i>Teacher Login
								</button>
							</form>
						</div>

						<!-- Admin Login -->
						<div class="tab-pane fade" id="admin-login" role="tabpanel">
							<form action="login.php" method="POST">
								<input type="hidden" name="usertype" value="admin">
								<div class="mb-3">
									<label for="admin-username" class="form-label">Username</label>
									<div class="input-group">
										<span class="input-group-text"><i class="fas fa-user"></i></span>
										<input type="text" class="form-control" id="admin-username" name="username" required>
									</div>
								</div>
								<div class="mb-3">
									<label for="admin-password" class="form-label">Password</label>
									<div class="input-group">
										<span class="input-group-text"><i class="fas fa-lock"></i></span>
										<input type="password" class="form-control" id="admin-password" name="password" required>
									</div>
								</div>
								<div class="mb-3 form-check">
									<input type="checkbox" class="form-check-input" id="admin-remember">
									<label class="form-check-label" for="admin-remember">Remember me</label>
								</div>
								<button type="submit" class="btn btn-danger w-100">
									<i class="fas fa-user-shield me-2"></i>Admin Login
								</button>
							</form>
						</div>
					</div>

					<!-- Forgot Password Link -->
					<div class="text-center mt-3">
						<a href="forgot_password.php" class="text-decoration-none">
							<i class="fas fa-key me-1"></i>Forgot Password?
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Hero Section -->
	<section id="home" class="hero-section">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-lg-6 hero-content">
					<h1 class="hero-title"><?php echo $content['hero_title']; ?></h1>
					<p class="hero-subtitle"><?php echo $content['hero_subtitle']; ?></p>
					<div class="d-flex gap-3">
						<a href="#admission" class="btn btn-light btn-hero">
							<i class="fas fa-user-plus me-2"></i>Apply Now
						</a>
						<a href="#courses" class="btn btn-outline-light btn-hero">
							<i class="fas fa-book me-2"></i>Our Courses
						</a>
					</div>
				</div>
				<div class="col-lg-6">
					<img src="index.jpeg" alt="Students Learning" class="img-fluid rounded-3 shadow-lg">
				</div>
			</div>
	</div>
	</section>

	<!-- Features Section -->
	<section class="py-5 bg-light">
		<div class="container">
		<div class="row">
				<div class="col-md-4 mb-4">
					<div class="feature-card">
						<div class="feature-icon">
							<i class="fas fa-chalkboard-teacher"></i>
						</div>
						<h4>Expert Teachers</h4>
						<p>Learn from experienced educators who are passionate about teaching and committed to your success.</p>
					</div>
				</div>
				<div class="col-md-4 mb-4">
					<div class="feature-card">
						<div class="feature-icon">
							<i class="fas fa-laptop"></i>
						</div>
						<h4>Modern Technology</h4>
						<p>State-of-the-art facilities and digital learning tools to enhance your educational experience.</p>
					</div>
				</div>
				<div class="col-md-4 mb-4">
					<div class="feature-card">
						<div class="feature-icon">
							<i class="fas fa-users"></i>
						</div>
						<h4>Small Class Sizes</h4>
						<p>Personalized attention with small class sizes ensuring every student gets the support they need.</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- About Section -->
	<section id="about" class="py-5">
		<div class="container">
			<h2 class="section-title">About Miles e-School Academy</h2>
			<p class="section-subtitle">Building futures through quality education and innovative learning</p>
			<div class="row align-items-center">
				<div class="col-lg-6 mb-4">
					<img src="row.jpg" alt="School Campus" class="img-fluid rounded-3 shadow-lg">
				</div>
				<div class="col-lg-6">
					<h3 class="mb-4"><?php echo $content['about_title']; ?></h3>
					<p class="lead mb-4"><?php echo $content['about_content']; ?></p>
					<div class="row">
						<div class="col-6 mb-3">
							<div class="d-flex align-items-center">
								<i class="fas fa-check-circle text-success me-2"></i>
								<span>Experienced Faculty</span>
							</div>
						</div>
						<div class="col-6 mb-3">
							<div class="d-flex align-items-center">
								<i class="fas fa-check-circle text-success me-2"></i>
								<span>Modern Curriculum</span>
							</div>
						</div>
						<div class="col-6 mb-3">
							<div class="d-flex align-items-center">
								<i class="fas fa-check-circle text-success me-2"></i>
								<span>Individual Attention</span>
							</div>
						</div>
						<div class="col-6 mb-3">
							<div class="d-flex align-items-center">
								<i class="fas fa-check-circle text-success me-2"></i>
								<span>Career Guidance</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Stats Section -->
	<section class="stats-section">
		<div class="container">
			<div class="row">
				<div class="col-md-3 col-6 mb-4">
					<div class="stat-item">
						<div class="stat-number">500+</div>
						<div class="stat-label">Students</div>
					</div>
				</div>
				<div class="col-md-3 col-6 mb-4">
					<div class="stat-item">
						<div class="stat-number">25+</div>
						<div class="stat-label">Expert Teachers</div>
					</div>
				</div>
				<div class="col-md-3 col-6 mb-4">
					<div class="stat-item">
						<div class="stat-number">15+</div>
						<div class="stat-label">Years Experience</div>
					</div>
				</div>
				<div class="col-md-3 col-6 mb-4">
					<div class="stat-item">
						<div class="stat-number">95%</div>
						<div class="stat-label">Success Rate</div>
					</div>
			</div>
		</div>
	</div>
	</section>

	<!-- Courses Section -->
	<section id="courses" class="py-5">
		<div class="container">
			<h2 class="section-title">Our Courses</h2>
			<p class="section-subtitle">Comprehensive curriculum designed for academic excellence</p>
		<div class="row">
			<?php
			$courses = [
					[
						"title" => "IGCSE Cambridge Mathematics (0580)",
						"description" => "Comprehensive mathematics course covering algebra, geometry, and statistics.",
						"image" => "picture1.jpg"
					],
					[
						"title" => "AS - Probability & Statistics (9709)",
						"description" => "Advanced study of probability theory and statistical analysis.",
						"image" => "picture2.jpg"
					],
					[
						"title" => "AS - Mechanics (9709)",
						"description" => "Physics-based mathematics focusing on motion and forces.",
						"image" => "picture3.jpg"
					],
					[
						"title" => "AS - Pure Mathematics 1",
						"description" => "Foundation course in pure mathematics and mathematical reasoning.",
						"image" => "picture1.jpg"
					],
					[
						"title" => "A Level - Pure Mathematics 3 (9709)",
						"description" => "Advanced pure mathematics for university preparation.",
						"image" => "picture2.jpg"
					],
					[
						"title" => "Computer Science Fundamentals",
						"description" => "Introduction to programming, algorithms, and computer systems.",
						"image" => "picture3.jpg"
					]
				];

				foreach ($courses as $course) {
				echo '
					<div class="col-lg-4 col-md-6 mb-4">
						<div class="course-card">
							<img src="' . $course['image'] . '" class="course-image" alt="' . htmlspecialchars($course['title']) . '">
							<div class="card-body p-4">
								<h5 class="card-title">' . htmlspecialchars($course['title']) . '</h5>
								<p class="card-text text-muted">' . htmlspecialchars($course['description']) . '</p>
								<a href="#admission" class="btn btn-outline-primary">Learn More</a>
							</div>
						</div>
				</div>';
			}
			?>
		</div>
	</div>
	</section>

	<!-- Teachers Section -->
	<section id="teachers" class="py-5 bg-light">
		<div class="container">
			<h2 class="section-title">Our Expert Teachers</h2>
			<p class="section-subtitle">Meet our dedicated team of experienced educators</p>
			<div class="row">
				<?php 
				if ($result && mysqli_num_rows($result) > 0) {
					while ($teacher = mysqli_fetch_assoc($result)) { ?>
						<div class="col-lg-4 col-md-6 mb-4">
							<div class="teacher-card">
								<img src="<?php echo $teacher['image']; ?>" class="teacher-image" alt="<?php echo htmlspecialchars($teacher['name']); ?>">
								<div class="card-body p-4">
									<h5 class="card-title"><?php echo htmlspecialchars($teacher['name']); ?></h5>
									<p class="card-text text-muted"><?php echo htmlspecialchars($teacher['description']); ?></p>
									<div class="d-flex justify-content-center">
										<a href="#" class="btn btn-sm btn-outline-primary me-2"><i class="fab fa-linkedin"></i></a>
										<a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-envelope"></i></a>
									</div>
								</div>
							</div>
						</div>
					<?php }
				} else {
					// Fallback teachers if database is empty
					$fallbackTeachers = [
						["name" => "Dr. Sarah Johnson", "description" => "Mathematics Specialist with 15+ years experience", "image" => "picture1.jpg"],
						["name" => "Prof. Michael Chen", "description" => "Physics and Mechanics expert", "image" => "picture2.jpg"],
						["name" => "Ms. Emily Rodriguez", "description" => "Statistics and Probability instructor", "image" => "picture3.jpg"]
					];
					
					foreach ($fallbackTeachers as $teacher) {
						echo '
						<div class="col-lg-4 col-md-6 mb-4">
							<div class="teacher-card">
								<img src="' . $teacher['image'] . '" class="teacher-image" alt="' . htmlspecialchars($teacher['name']) . '">
								<div class="card-body p-4">
									<h5 class="card-title">' . htmlspecialchars($teacher['name']) . '</h5>
									<p class="card-text text-muted">' . htmlspecialchars($teacher['description']) . '</p>
									<div class="d-flex justify-content-center">
										<a href="#" class="btn btn-sm btn-outline-primary me-2"><i class="fab fa-linkedin"></i></a>
										<a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-envelope"></i></a>
									</div>
								</div>
							</div>
						</div>';
					}
				}
				?>
			</div>
		</div>
	</section>

	<!-- Testimonials Section -->
	<section class="py-5">
		<div class="container">
			<h2 class="section-title">What Our Students Say</h2>
			<p class="section-subtitle">Hear from our successful graduates</p>
			<div class="row">
				<div class="col-lg-4 mb-4">
					<div class="testimonial-card">
						<img src="picture1.jpg" alt="Student" class="testimonial-avatar">
						<h5>Alex Thompson</h5>
						<p class="text-muted">Graduated 2023</p>
						<p>"The teachers at Miles Academy are exceptional. They helped me achieve my dream of studying engineering at university."</p>
						<div class="text-warning">
							<i class="fas fa-star"></i>
							<i class="fas fa-star"></i>
							<i class="fas fa-star"></i>
							<i class="fas fa-star"></i>
							<i class="fas fa-star"></i>
						</div>
					</div>
				</div>
				<div class="col-lg-4 mb-4">
					<div class="testimonial-card">
						<img src="picture2.jpg" alt="Student" class="testimonial-avatar">
						<h5>Maria Garcia</h5>
						<p class="text-muted">Graduated 2023</p>
						<p>"The small class sizes and personalized attention made all the difference in my academic success."</p>
						<div class="text-warning">
							<i class="fas fa-star"></i>
							<i class="fas fa-star"></i>
							<i class="fas fa-star"></i>
							<i class="fas fa-star"></i>
							<i class="fas fa-star"></i>
						</div>
					</div>
				</div>
				<div class="col-lg-4 mb-4">
					<div class="testimonial-card">
						<img src="picture3.jpg" alt="Student" class="testimonial-avatar">
						<h5>David Kim</h5>
						<p class="text-muted">Graduated 2023</p>
						<p>"The modern facilities and technology-enhanced learning environment prepared me perfectly for university."</p>
						<div class="text-warning">
							<i class="fas fa-star"></i>
							<i class="fas fa-star"></i>
							<i class="fas fa-star"></i>
							<i class="fas fa-star"></i>
							<i class="fas fa-star"></i>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Admission Form Section -->
	<section id="admission" class="py-5 bg-light">
		<div class="container">
    <div class="row justify-content-center">
				<div class="col-lg-8">
					<div class="admission-form">
						<div class="form-header">
							<h3><i class="fas fa-user-plus me-2"></i>Apply for Admission</h3>
							<p class="mb-0">Take the first step towards your academic success</p>
                </div>
						<div class="form-body">
                    <form method="POST" action="data_check.php">
								<div class="row">
									<div class="col-md-6 mb-3">
										<label for="name" class="form-label">Full Name *</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
									<div class="col-md-6 mb-3">
										<label for="email" class="form-label">Email Address *</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
								</div>
								<div class="row">
									<div class="col-md-6 mb-3">
										<label for="phone" class="form-label">Phone Number *</label>
										<input type="tel" name="phone" id="phone" class="form-control" required>
                        </div>
									<div class="col-md-6 mb-3">
										<label for="grade" class="form-label">Select Grade *</label>
                            <select name="grade" id="grade" class="form-control" required>
                                <option value="">-- Select Grade --</option>
											<option value="Grade 8">Grade 8</option>
											<option value="Grade 9">Grade 9</option>
											<option value="Grade 10">Grade 10</option>
											<option value="Grade 11">Grade 11</option>
											<option value="Grade 12">Grade 12</option>
											<option value="GCE">GCE</option>
                            </select>
									</div>
                        </div>
                        <div class="row">
									<div class="col-md-6 mb-3">
										<label for="section" class="form-label">Select Section *</label>
                            <select name="section" id="section" class="form-control" required>
                                <option value="">-- Select Section --</option>
											<option value="A">Section A</option>
											<option value="B">Section B</option>
											<option value="C">Section C</option>
											<option value="D">Section D</option>
                            </select>
                        </div>
									<div class="col-md-6 mb-3">
										<label for="message" class="form-label">Why Should We Accept You? *</label>
										<textarea name="message" id="message" class="form-control" rows="4" placeholder="Tell us about your academic goals, achievements, and why you want to join our academy..." required></textarea>
                        </div>
                        </div>
                        <div class="text-center">
									<button type="submit" name="apply" class="btn btn-primary btn-lg">
										<i class="fas fa-paper-plane me-2"></i>Submit Application
									</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
	</section>

	<!-- Contact Section -->
	<section id="contact" class="py-5">
		<div class="container">
			<h2 class="section-title">Contact Us</h2>
			<p class="section-subtitle">Get in touch with us for any inquiries</p>
		<div class="row">
				<div class="col-lg-8 mb-4">
					<div class="contact-info">
						<div class="contact-item">
							<div class="contact-icon">
								<i class="fas fa-map-marker-alt"></i>
							</div>
							<div>
								<h6>Address</h6>
								<p class="mb-0"><?php echo $content['contact_address']; ?></p>
							</div>
						</div>
						<div class="contact-item">
							<div class="contact-icon">
								<i class="fas fa-phone"></i>
							</div>
							<div>
								<h6>Phone</h6>
								<p class="mb-0"><?php echo $content['contact_phone']; ?></p>
							</div>
						</div>
						<div class="contact-item">
							<div class="contact-icon">
								<i class="fas fa-envelope"></i>
							</div>
							<div>
								<h6>Email</h6>
								<p class="mb-0"><?php echo $content['contact_email']; ?></p>
							</div>
						</div>
						<div class="contact-item">
							<div class="contact-icon">
								<i class="fas fa-clock"></i>
							</div>
							<div>
								<h6>Office Hours</h6>
								<p class="mb-0"><?php echo $content['contact_hours']; ?></p>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-4 mb-4">
					<div class="contact-info">
						<h5>Quick Links</h5>
						<ul class="list-unstyled">
							<li><a href="#courses" class="text-decoration-none">Course Catalog</a></li>
							<li><a href="#admission" class="text-decoration-none">Admission Process</a></li>
							<li><a href="#teachers" class="text-decoration-none">Faculty Directory</a></li>
							<li><a href="#" class="text-decoration-none">Student Portal</a></li>
							<li><a href="#" class="text-decoration-none">Parent Resources</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Footer -->
	<footer class="footer">
		<div class="container">
			<div class="row">
				<div class="col-lg-4 mb-4">
					<h5><i class="fas fa-graduation-cap me-2"></i>Miles e-School Academy</h5>
					<p class="text-muted">Empowering students with knowledge, skills, and values for a brighter future. Join our community of learners and discover your potential.</p>
					<div class="social-links">
						<a href="#"><i class="fab fa-facebook-f"></i></a>
						<a href="#"><i class="fab fa-twitter"></i></a>
						<a href="#"><i class="fab fa-linkedin-in"></i></a>
						<a href="#"><i class="fab fa-instagram"></i></a>
					</div>
				</div>
				<div class="col-lg-2 col-md-6 mb-4">
					<h5>Academics</h5>
					<ul>
						<li><a href="#courses">Courses</a></li>
						<li><a href="#teachers">Faculty</a></li>
						<li><a href="#">Library</a></li>
						<li><a href="#">Research</a></li>
					</ul>
				</div>
				<div class="col-lg-2 col-md-6 mb-4">
					<h5>Admissions</h5>
					<ul>
						<li><a href="#admission">Apply Now</a></li>
						<li><a href="#">Requirements</a></li>
						<li><a href="#">Tuition</a></li>
						<li><a href="#">Scholarships</a></li>
					</ul>
				</div>
				<div class="col-lg-2 col-md-6 mb-4">
					<h5>Student Life</h5>
					<ul>
						<li><a href="#">Campus</a></li>
						<li><a href="#">Activities</a></li>
						<li><a href="#">Support</a></li>
						<li><a href="#">Resources</a></li>
					</ul>
				</div>
				<div class="col-lg-2 col-md-6 mb-4">
					<h5>Contact</h5>
					<ul>
						<li><a href="#contact">Contact Us</a></li>
						<li><a href="#">Directory</a></li>
						<li><a href="#">Emergency</a></li>
						<li><a href="#">Feedback</a></li>
					</ul>
				</div>
			</div>
			<hr class="my-4">
			<div class="row align-items-center">
				<div class="col-md-6">
					<p class="mb-0 text-muted">&copy; 2024 Miles e-School Academy. All rights reserved.</p>
				</div>
				<div class="col-md-6 text-md-end">
					<a href="#" class="text-muted me-3">Privacy Policy</a>
					<a href="#" class="text-muted me-3">Terms of Service</a>
					<a href="#" class="text-muted">Cookie Policy</a>
				</div>
		</div>
	</div>
	</footer>

	<!-- Bootstrap JS -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
	
	<!-- Smooth Scrolling -->
	<script>
		// Smooth scrolling for navigation links
		document.querySelectorAll('a[href^="#"]').forEach(anchor => {
			anchor.addEventListener('click', function (e) {
				e.preventDefault();
				const target = document.querySelector(this.getAttribute('href'));
				if (target) {
					target.scrollIntoView({
						behavior: 'smooth',
						block: 'start'
					});
				}
			});
		});

		// Navbar background change on scroll
		window.addEventListener('scroll', function() {
			const navbar = document.querySelector('.navbar');
			if (window.scrollY > 50) {
				navbar.style.background = 'rgba(37, 99, 235, 0.98) !important';
			} else {
				navbar.style.background = 'rgba(37, 99, 235, 0.95) !important';
			}
		});

		// Animate stats on scroll
		const observerOptions = {
			threshold: 0.5
		};

		const observer = new IntersectionObserver(function(entries) {
			entries.forEach(entry => {
				if (entry.isIntersecting) {
					entry.target.style.opacity = '1';
					entry.target.style.transform = 'translateY(0)';
				}
			});
		}, observerOptions);

		document.querySelectorAll('.stat-item').forEach(item => {
			item.style.opacity = '0';
			item.style.transform = 'translateY(20px)';
			item.style.transition = 'all 0.6s ease';
			observer.observe(item);
		});
	</script>
</body>
</html>
