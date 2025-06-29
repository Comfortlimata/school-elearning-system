-- School Grading and Subject Structure
-- Run this SQL to set up grades, subjects, and teacher/student mappings

-- 1. Grades Table
CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(10) NOT NULL UNIQUE -- e.g., '8', '9', '10', '11', '12', 'GCE'
);

-- 2. Subjects Table
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE -- e.g., 'English Language', 'Mathematics', etc.
);

-- 3. Teacher-Grade-Subject Mapping
CREATE TABLE IF NOT EXISTS teacher_grade_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    grade_id INT NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES teacher(id) ON DELETE CASCADE,
    FOREIGN KEY (grade_id) REFERENCES grades(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_grade_subject (teacher_id, grade_id, subject_id)
);

-- 4. Student-Teacher-Subject Mapping
CREATE TABLE IF NOT EXISTS student_teacher_subject (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teacher(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_teacher_subject (student_id, teacher_id, subject_id)
);

-- 5. Update Students Table: Replace 'program' with 'grade_id'
-- (You may need to migrate data and drop the old column manually if needed)
ALTER TABLE students 
    ADD COLUMN grade_id INT AFTER id,
    ADD CONSTRAINT fk_students_grade FOREIGN KEY (grade_id) REFERENCES grades(id);

-- Optional: Remove 'program' column after migration
-- ALTER TABLE students DROP COLUMN program;

-- 6. Insert Default Grades
INSERT IGNORE INTO grades (name) VALUES ('8'), ('9'), ('10'), ('11'), ('12'), ('GCE');

-- 7. Insert Default Subjects
INSERT IGNORE INTO subjects (name) VALUES
('English Language'),
('Mathematics'),
('Science'),
('Religious Education'),
('Moral Education'),
('Civic Education'),
('Physical Education'),
('Information and Communication Technology'),
('Physics'),
('Chemistry'),
('Biology'); 