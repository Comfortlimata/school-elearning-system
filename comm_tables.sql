-- Teacher-Student Communication Tables
-- Run this SQL in your database

-- Main communication table
CREATE TABLE IF NOT EXISTS teacher_student_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    student_id INT NOT NULL,
    message TEXT NOT NULL,
    sender_type ENUM('teacher', 'student') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teacher(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL,
    course_code VARCHAR(50) NOT NULL,
    course_description TEXT,
    program VARCHAR(100) NOT NULL,
    document_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample courses
INSERT IGNORE INTO courses (course_name, course_code, course_description, program) VALUES
('IGCSE Cambridge Mathematics (0580)', 'MATH0580', 'Comprehensive mathematics course covering algebra, geometry, and statistics.', 'Mathematics'),
('AS - Probability & Statistics (9709)', 'MATH9709-PS', 'Advanced study of probability theory and statistical analysis.', 'Mathematics'),
('AS - Mechanics (9709)', 'MATH9709-M', 'Physics-based mathematics focusing on motion and forces.', 'Mathematics'),
('Computer Science Fundamentals', 'CS101', 'Introduction to programming, algorithms, and computer systems.', 'Computer Science'); 