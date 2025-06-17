<?php
error_reporting(0);
session_start();
session_destroy();

if ($_SESSION['message']) {
    $message = $_SESSION['message'];
    echo "<script type='text/javascript'>
        alert('$message');
    </script>";
}

$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = mysqli_connect($host, $user, $password, $db);

$sql = "SELECT * FROM teachers";
$result = mysqli_query($data, $sql);
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Student Management System</title>
	<link rel="stylesheet" type="text/css" href="style.css">

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>

	<style>
		.teacher {
			width: 100%;
			height: 250px;
			object-fit: cover;
			border-radius: 8px;
			margin-bottom: 10px;
		}
		.course-description {
			font-size: 16px;
			color: #333;
			min-height: 60px;
			margin-bottom: 20px;
		}
		.form_deg {
			background-color: #f8f9fa;
			padding: 20px;
			margin: 30px auto;
			border-radius: 10px;
			width: 80%;
		}
		.title_deg {
			font-size: 24px;
			font-weight: bold;
			margin-bottom: 20px;
		}
		.label_text {
			display: block;
			margin-bottom: 5px;
			font-weight: bold;
		}
		.input_deg {
			width: 100%;
			padding: 8px;
			margin-bottom: 15px;
			border: 1px solid #ccc;
			border-radius: 4px;
		}
		.submit-btn {
			background-color: #28a745;
			color: white;
			padding: 10px 20px;
			border: none;
			border-radius: 4px;
			cursor: pointer;
		}
		nav ul {
			list-style-type: none;
			display: flex;
			gap: 15px;
		}
	</style>
</head>
<body>
	<nav>
		<label class="logo">LIMATA e LEANING SCHOOL</label>
		<ul>
			<li><a href="">Home</a></li>
			<li><a href="">Admission</a></li>
			<li><a href="">Contact</a></li>
			<li><a href="login.php" class="btn btn-success">Login</a></li>
		</ul> 
	</nav>

	<div class="section1">
		<label class="img_text">We Teach Students With Care</label>
		<img class="main_img" src="index.jpeg" alt="School Banner">
	</div>

	<div class="container my-4">
		<div class="limz">
			<div class="col-md-4">
				<img class="welcome_img" src=".jpg" alt="Welcome">
			</div>
			<div class="col-md-8">
				<h1>Welcome To Limata1 e-School Academy</h1>
				<p>At Extra Limata Elementary, we believe that learning is a journey — and sometimes, it takes a few extra miles! Our students don’t just stop at the basics; they zoom past the ordinary with curiosity, creativity, and a healthy dose of laughter...</p>
			</div>
		</div>
	</div>

	<center><h1>Our Courses</h1></center>

	<div class="container mb-5">
		<div class="row">
			<?php
			$courses = [
				"IGCSE Cambridge Mathematics (0580)...",
				"AS - Probability & Statistics (9709)...",
				"AS - Mechanics (9709)...",
				"AS - Pure Mathematics 1...",
				"A Level - Pure Mathematics 3 (9709)...",
				"IGCSE Cambridge Mathematics (0580)..."
			];

			foreach ($courses as $index => $course) {
				echo '
				<div class="col-md-4 mb-4">
					<img class="teacher" src="picture' . (($index % 3) + 1) . '.jpg" alt="Course Image">
					<p class="course-description">' . htmlspecialchars($course) . '</p>
				</div>';
			}
			?>
		</div>
	</div>

<!-- Admission Form -->
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h3>Admission Form</h3>
               
                    <form action="data_check.php" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name:</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone:</label>
                            <input type="text" name="phone" id="phone" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="program" class="form-label">Select Program:</label>
                            <select name="program" id="program" class="form-control" required>
                                <option value="">-- Select Program --</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Engineering">Engineering</option>
                                <option value="Mathematics">Mathematics</option>
                                <option value="Business Administration">Business Administration</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Why Should We Accept You?</label>
                            <textarea name="message" id="message" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Apply</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Contact Us Section -->
<div class="contact-us" style="background-color: #f8f9fa; padding: 40px 20px; text-align: center;">
    <h1 style="color: #007bff; margin-bottom: 30px;">Contact Us</h1>
    <div class="contact-icons" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px;">
        <!-- Phone -->
        <div style="text-align: center;">
            <img src="phone-icon.png" alt="Phone Icon" style="width: 50px; height: 50px; margin-bottom: 10px;">
            <p style="font-size: 18px; font-weight: bold;">0970022456</p>
        </div>
        <!-- Email -->
        <div style="text-align: center;">
            <img src="email-icon.png" alt="Email Icon" style="width: 50px; height: 50px; margin-bottom: 10px;">
            <p style="font-size: 18px; font-weight: bold;">comfortlimata@gmail.com</p>
        </div>
        <!-- Instagram -->
        <div style="text-align: center;">
            <img src="instagram-icon.png" alt="Instagram Icon" style="width: 50px; height: 50px; margin-bottom: 10px;">
            <p style="font-size: 18px; font-weight: bold;">@limatacomfort</p>
        </div>
        <!-- Facebook -->
        <div style="text-align: center;">
            <img src="facebook-icon.png" alt="Facebook Icon" style="width: 50px; height: 50px; margin-bottom: 10px;">
            <p style="font-size: 18px; font-weight: bold;">Comfort Limata</p>
        </div>
    </div>
</div>

	<!-- OUR TEACHERS SECTION -->
	<center><h1>Our Teachers</h1></center>

	<div class="container mb-5">
		<div class="row">
			<?php while ($teacher = mysqli_fetch_assoc($result)) { ?>
				<div class="col-md-4 mb-4">
					<div class="card h-100">
						<img src="<?php echo $teacher['image']; ?>" class="card-img-top" alt="Teacher Image" style="height:250px; object-fit:cover;">
						<div class="card-body">
							<h5 class="card-title"><?php echo htmlspecialchars($teacher['name']); ?></h5>
							<p class="card-text"><?php echo htmlspecialchars($teacher['description']); ?></p>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</body>
</html>