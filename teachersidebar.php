<?php
if (!isset($conn)) {
    $conn = mysqli_connect("localhost", "root", "", "schoolproject");
    if (!$conn) { die("Database connection failed: " . mysqli_connect_error()); }
}
if (!isset($teacher_id)) {
    session_start();
    $teacher_id = $_SESSION['teacher_id'] ?? 0;
}
if (!isset($teacher)) {
    $teacher = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM teacher WHERE id = $teacher_id"));
}
$sidebar_classes = mysqli_query($conn, "SELECT DISTINCT g.name as grade_name, gsa.section FROM teacher_grade_subjects tgs JOIN grades g ON tgs.grade_id = g.id JOIN grade_subject_assignments gsa ON gsa.grade_id = tgs.grade_id AND gsa.subject_id = tgs.subject_id WHERE tgs.teacher_id = $teacher_id ORDER BY g.name, gsa.section");
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="#" class="sidebar-brand">Teacher Portal</a>
    </div>
    <div class="sidebar-menu">
        <a href="teacherhome.php" class="sidebar-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherhome.php') echo ' active';?>"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
        <a href="teacherclasses.php" class="sidebar-link with-classes<?php if(basename($_SERVER['PHP_SELF'])=='teacherclasses.php') echo ' active';?>"><i class="fas fa-users-class"></i>My Classes</a>
        <?php if ($sidebar_classes && mysqli_num_rows($sidebar_classes) > 0): ?>
            <ul class="sidebar-classes-list">
                <?php mysqli_data_seek($sidebar_classes, 0); while ($row = mysqli_fetch_assoc($sidebar_classes)): ?>
                    <li><?php echo htmlspecialchars($row['grade_name'] . $row['section']); ?></li>
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>
        <a href="teachersubjects.php" class="sidebar-link<?php if(basename($_SERVER['PHP_SELF'])=='teachersubjects.php') echo ' active';?>"><i class="fas fa-book"></i>My Subjects</a>
        <a href="teacherassignments.php" class="sidebar-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherassignments.php') echo ' active';?>"><i class="fas fa-tasks"></i>Assignments & Materials</a>
        <a href="teacherperformance.php" class="sidebar-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherperformance.php') echo ' active';?>"><i class="fas fa-chart-bar"></i>Student Performance</a>
        <a href="teacherschedule.php" class="sidebar-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherschedule.php') echo ' active';?>"><i class="fas fa-calendar-alt"></i>Schedule</a>
        <a href="teacherprofile.php" class="sidebar-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherprofile.php') echo ' active';?>"><i class="fas fa-user"></i>Profile</a>
        <a href="teachernotifications.php" class="sidebar-link<?php if(basename($_SERVER['PHP_SELF'])=='teachernotifications.php') echo ' active';?>"><i class="fas fa-bell"></i>Notifications</a>
    </div>
</aside>
<style>
.sidebar {
    position: fixed; left: 0; top: 0; height: 100vh; width: 280px;
    background: linear-gradient(135deg, #2563eb, #1e40af);
    color: white; z-index: 1000; transition: all 0.3s ease;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1); display: flex; flex-direction: column;
}
.sidebar-header { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center; }
.sidebar-brand { font-size: 1.5rem; font-weight: 700; color: white; text-decoration: none; }
.sidebar-menu { flex: 1; overflow-y: auto; padding: 1rem 0; }
.sidebar-link { display: flex; align-items: center; padding: 0.75rem 1rem; color: rgba(255,255,255,0.8); text-decoration: none; border-radius: 10px; transition: all 0.3s ease; font-weight: 600; font-size: 1.1rem; gap: 0.75rem; }
.sidebar-link:hover, .sidebar-link.active { background: rgba(255,255,255,0.15); color: white; }
.sidebar-classes-list {
    list-style: none;
    padding-left: 2.2rem;
    margin: 0.25rem 0 0.5rem 0;
}
.sidebar-classes-list li {
    font-size: 1rem;
    color: #e0e7ef;
    margin-bottom: 0.15rem;
    padding-left: 0.5rem;
    border-left: 2px solid #fff2;
}
.sidebar-classes-list li:last-child { margin-bottom: 0; }
.sidebar-link.with-classes { position: relative; }
.sidebar-link.with-classes:after {
    content: '\25BC';
    font-size: 0.7em;
    position: absolute;
    right: 1.2rem;
    top: 50%;
    transform: translateY(-50%);
    color: #fff8;
}
</style> 