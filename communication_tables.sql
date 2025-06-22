-- Teacher-Student Communication System Tables
-- Run these SQL commands in your database to set up the communication features

-- 1. Create teacher_student_messages table for storing messages
CREATE TABLE IF NOT EXISTS teacher_student_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    student_id INT NOT NULL,
    message TEXT NOT NULL,
    sender_type ENUM('teacher', 'student') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teacher(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_student_id (student_id),
    INDEX idx_created_at (created_at)
);

-- 2. Create courses table for course management
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL,
    course_code VARCHAR(50) NOT NULL UNIQUE,
    course_description TEXT,
    program VARCHAR(100) NOT NULL,
    document_path VARCHAR(255),
    teacher_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teacher(id) ON DELETE SET NULL,
    INDEX idx_program (program),
    INDEX idx_teacher_id (teacher_id)
);

-- 3. Create course_enrollments table to track student enrollments
CREATE TABLE IF NOT EXISTS course_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    student_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'completed', 'dropped') DEFAULT 'active',
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (course_id, student_id),
    INDEX idx_course_id (course_id),
    INDEX idx_student_id (student_id),
    INDEX idx_status (status)
);

-- 4. Create course_materials table for course resources
CREATE TABLE IF NOT EXISTS course_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255),
    file_type VARCHAR(50),
    file_size INT,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES teacher(id) ON DELETE CASCADE,
    INDEX idx_course_id (course_id),
    INDEX idx_uploaded_by (uploaded_by)
);

-- 5. Create course_assignments table for assignments
CREATE TABLE IF NOT EXISTS course_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATETIME,
    total_points INT DEFAULT 100,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES teacher(id) ON DELETE CASCADE,
    INDEX idx_course_id (course_id),
    INDEX idx_due_date (due_date)
);

-- 6. Create assignment_submissions table
CREATE TABLE IF NOT EXISTS assignment_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_file VARCHAR(255),
    submission_text TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    grade DECIMAL(5,2),
    feedback TEXT,
    graded_by INT,
    graded_at TIMESTAMP NULL,
    FOREIGN KEY (assignment_id) REFERENCES course_assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES teacher(id) ON DELETE SET NULL,
    UNIQUE KEY unique_submission (assignment_id, student_id),
    INDEX idx_assignment_id (assignment_id),
    INDEX idx_student_id (student_id)
);

-- 7. Create notifications table for system notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('student', 'teacher', 'admin') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id, user_type),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);

-- Insert sample courses
INSERT IGNORE INTO courses (course_name, course_code, course_description, program) VALUES
('IGCSE Cambridge Mathematics (0580)', 'MATH0580', 'Comprehensive mathematics course covering algebra, geometry, and statistics.', 'Mathematics'),
('AS - Probability & Statistics (9709)', 'MATH9709-PS', 'Advanced study of probability theory and statistical analysis.', 'Mathematics'),
('AS - Mechanics (9709)', 'MATH9709-M', 'Physics-based mathematics focusing on motion and forces.', 'Mathematics'),
('AS - Pure Mathematics 1', 'MATH9709-PM1', 'Foundation course in pure mathematics and mathematical reasoning.', 'Mathematics'),
('A Level - Pure Mathematics 3 (9709)', 'MATH9709-PM3', 'Advanced pure mathematics for university preparation.', 'Mathematics'),
('Computer Science Fundamentals', 'CS101', 'Introduction to programming, algorithms, and computer systems.', 'Computer Science'),
('Programming in Python', 'CS102', 'Learn Python programming language and its applications.', 'Computer Science'),
('Data Structures and Algorithms', 'CS201', 'Advanced programming concepts and problem-solving techniques.', 'Computer Science');

-- Create indexes for better performance
CREATE INDEX idx_messages_conversation ON teacher_student_messages(teacher_id, student_id, created_at);
CREATE INDEX idx_courses_program_teacher ON courses(program, teacher_id);
CREATE INDEX idx_enrollments_student_status ON course_enrollments(student_id, status);
CREATE INDEX idx_materials_course_type ON course_materials(course_id, file_type);
CREATE INDEX idx_assignments_course_due ON course_assignments(course_id, due_date);
CREATE INDEX idx_submissions_assignment_student ON assignment_submissions(assignment_id, student_id);

-- Add comments for documentation
ALTER TABLE teacher_student_messages COMMENT = 'Stores messages between teachers and students';
ALTER TABLE courses COMMENT = 'Course catalog and information';
ALTER TABLE course_enrollments COMMENT = 'Student enrollments in courses';
ALTER TABLE course_materials COMMENT = 'Course materials and resources';
ALTER TABLE course_assignments COMMENT = 'Course assignments and homework';
ALTER TABLE assignment_submissions COMMENT = 'Student submissions for assignments';
ALTER TABLE notifications COMMENT = 'System notifications for users'; 