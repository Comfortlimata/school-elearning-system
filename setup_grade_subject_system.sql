-- Grade-Subject Assignment System Setup
-- Run this SQL file to set up the complete grade-subject management system

-- 1. Create grades table if it doesn't exist
CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(10) NOT NULL UNIQUE -- e.g., '8', '9', '10', '11', '12', 'GCE'
);

-- 2. Create subjects table if it doesn't exist
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE -- e.g., 'English Language', 'Mathematics', etc.
);

-- 3. Create grade_subject_assignments table
CREATE TABLE IF NOT EXISTS grade_subject_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grade_id INT NOT NULL,
    subject_id INT NOT NULL,
    is_required BOOLEAN DEFAULT FALSE,
    is_elective BOOLEAN DEFAULT TRUE,
    credits INT DEFAULT 1,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (grade_id) REFERENCES grades(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_grade_subject (grade_id, subject_id),
    INDEX idx_grade_id (grade_id),
    INDEX idx_subject_id (subject_id)
);

-- 4. Insert default grades
INSERT IGNORE INTO grades (name) VALUES 
('8'), ('9'), ('10'), ('11'), ('12'), ('GCE');

-- 5. Insert default subjects
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
('Biology'),
('Geography'),
('History'),
('Economics'),
('Business Studies'),
('Computer Science'),
('Art'),
('Music'),
('French'),
('Spanish'),
('Literature'),
('Accounting'),
('Sociology'),
('Psychology'),
('Environmental Science'),
('Agriculture'),
('Home Economics'),
('Technical Drawing'),
('Woodwork'),
('Metalwork');

-- 6. Insert sample grade-subject assignments
-- Grade 8 Assignments
INSERT IGNORE INTO grade_subject_assignments (grade_id, subject_id, is_required, is_elective, credits, description) VALUES
((SELECT id FROM grades WHERE name = '8'), (SELECT id FROM subjects WHERE name = 'English Language'), 1, 0, 1, 'Core English language skills'),
((SELECT id FROM grades WHERE name = '8'), (SELECT id FROM subjects WHERE name = 'Mathematics'), 1, 0, 1, 'Basic mathematics and problem solving'),
((SELECT id FROM grades WHERE name = '8'), (SELECT id FROM subjects WHERE name = 'Science'), 1, 0, 1, 'General science introduction'),
((SELECT id FROM grades WHERE name = '8'), (SELECT id FROM subjects WHERE name = 'Religious Education'), 1, 0, 1, 'Religious and moral education'),
((SELECT id FROM grades WHERE name = '8'), (SELECT id FROM subjects WHERE name = 'Physical Education'), 1, 0, 1, 'Physical fitness and sports'),
((SELECT id FROM grades WHERE name = '8'), (SELECT id FROM subjects WHERE name = 'Information and Communication Technology'), 0, 1, 1, 'Basic computer skills'),
((SELECT id FROM grades WHERE name = '8'), (SELECT id FROM subjects WHERE name = 'Geography'), 0, 1, 1, 'World geography and maps'),
((SELECT id FROM grades WHERE name = '8'), (SELECT id FROM subjects WHERE name = 'History'), 0, 1, 1, 'World history introduction');

-- Grade 9 Assignments
INSERT IGNORE INTO grade_subject_assignments (grade_id, subject_id, is_required, is_elective, credits, description) VALUES
((SELECT id FROM grades WHERE name = '9'), (SELECT id FROM subjects WHERE name = 'English Language'), 1, 0, 1, 'Advanced English language skills'),
((SELECT id FROM grades WHERE name = '9'), (SELECT id FROM subjects WHERE name = 'Mathematics'), 1, 0, 1, 'Intermediate mathematics'),
((SELECT id FROM grades WHERE name = '9'), (SELECT id FROM subjects WHERE name = 'Science'), 1, 0, 1, 'Intermediate science concepts'),
((SELECT id FROM grades WHERE name = '9'), (SELECT id FROM subjects WHERE name = 'Religious Education'), 1, 0, 1, 'Religious and moral education'),
((SELECT id FROM grades WHERE name = '9'), (SELECT id FROM subjects WHERE name = 'Physical Education'), 1, 0, 1, 'Physical fitness and sports'),
((SELECT id FROM grades WHERE name = '9'), (SELECT id FROM subjects WHERE name = 'Information and Communication Technology'), 0, 1, 1, 'Intermediate computer skills'),
((SELECT id FROM grades WHERE name = '9'), (SELECT id FROM subjects WHERE name = 'Geography'), 0, 1, 1, 'Regional geography'),
((SELECT id FROM grades WHERE name = '9'), (SELECT id FROM subjects WHERE name = 'History'), 0, 1, 1, 'Modern history'),
((SELECT id FROM grades WHERE name = '9'), (SELECT id FROM subjects WHERE name = 'Art'), 0, 1, 1, 'Creative arts and design'),
((SELECT id FROM grades WHERE name = '9'), (SELECT id FROM subjects WHERE name = 'Music'), 0, 1, 1, 'Music theory and practice');

-- Grade 10 Assignments
INSERT IGNORE INTO grade_subject_assignments (grade_id, subject_id, is_required, is_elective, credits, description) VALUES
((SELECT id FROM grades WHERE name = '10'), (SELECT id FROM subjects WHERE name = 'English Language'), 1, 0, 1, 'Advanced English language and literature'),
((SELECT id FROM grades WHERE name = '10'), (SELECT id FROM subjects WHERE name = 'Mathematics'), 1, 0, 1, 'Advanced mathematics'),
((SELECT id FROM grades WHERE name = '10'), (SELECT id FROM subjects WHERE name = 'Physics'), 0, 1, 1, 'Physics fundamentals'),
((SELECT id FROM grades WHERE name = '10'), (SELECT id FROM subjects WHERE name = 'Chemistry'), 0, 1, 1, 'Chemistry fundamentals'),
((SELECT id FROM grades WHERE name = '10'), (SELECT id FROM subjects WHERE name = 'Biology'), 0, 1, 1, 'Biology fundamentals'),
((SELECT id FROM grades WHERE name = '10'), (SELECT id FROM subjects WHERE name = 'Religious Education'), 1, 0, 1, 'Religious and moral education'),
((SELECT id FROM grades WHERE name = '10'), (SELECT id FROM subjects WHERE name = 'Physical Education'), 1, 0, 1, 'Physical fitness and sports'),
((SELECT id FROM grades WHERE name = '10'), (SELECT id FROM subjects WHERE name = 'Information and Communication Technology'), 0, 1, 1, 'Advanced computer skills'),
((SELECT id FROM grades WHERE name = '10'), (SELECT id FROM subjects WHERE name = 'Geography'), 0, 1, 1, 'Advanced geography'),
((SELECT id FROM grades WHERE name = '10'), (SELECT id FROM subjects WHERE name = 'History'), 0, 1, 1, 'Advanced history'),
((SELECT id FROM grades WHERE name = '10'), (SELECT id FROM subjects WHERE name = 'Economics'), 0, 1, 1, 'Basic economics'),
((SELECT id FROM grades WHERE name = '10'), (SELECT id FROM subjects WHERE name = 'Business Studies'), 0, 1, 1, 'Business fundamentals'),
((SELECT id FROM grades WHERE name = '10'), (SELECT id FROM subjects WHERE name = 'Computer Science'), 0, 1, 1, 'Programming fundamentals');

-- Grade 11 Assignments
INSERT IGNORE INTO grade_subject_assignments (grade_id, subject_id, is_required, is_elective, credits, description) VALUES
((SELECT id FROM grades WHERE name = '11'), (SELECT id FROM subjects WHERE name = 'English Language'), 1, 0, 1, 'Advanced English language and literature'),
((SELECT id FROM grades WHERE name = '11'), (SELECT id FROM subjects WHERE name = 'Mathematics'), 1, 0, 1, 'Advanced mathematics and calculus'),
((SELECT id FROM grades WHERE name = '11'), (SELECT id FROM subjects WHERE name = 'Physics'), 0, 1, 1, 'Advanced physics'),
((SELECT id FROM grades WHERE name = '11'), (SELECT id FROM subjects WHERE name = 'Chemistry'), 0, 1, 1, 'Advanced chemistry'),
((SELECT id FROM grades WHERE name = '11'), (SELECT id FROM subjects WHERE name = 'Biology'), 0, 1, 1, 'Advanced biology'),
((SELECT id FROM grades WHERE name = '11'), (SELECT id FROM subjects WHERE name = 'Economics'), 0, 1, 1, 'Advanced economics'),
((SELECT id FROM grades WHERE name = '11'), (SELECT id FROM subjects WHERE name = 'Business Studies'), 0, 1, 1, 'Advanced business studies'),
((SELECT id FROM grades WHERE name = '11'), (SELECT id FROM subjects WHERE name = 'Computer Science'), 0, 1, 1, 'Advanced programming'),
((SELECT id FROM grades WHERE name = '11'), (SELECT id FROM subjects WHERE name = 'Geography'), 0, 1, 1, 'Advanced geography'),
((SELECT id FROM grades WHERE name = '11'), (SELECT id FROM subjects WHERE name = 'History'), 0, 1, 1, 'Advanced history'),
((SELECT id FROM grades WHERE name = '11'), (SELECT id FROM subjects WHERE name = 'Accounting'), 0, 1, 1, 'Financial accounting'),
((SELECT id FROM grades WHERE name = '11'), (SELECT id FROM subjects WHERE name = 'Sociology'), 0, 1, 1, 'Social studies'),
((SELECT id FROM grades WHERE name = '11'), (SELECT id FROM subjects WHERE name = 'Psychology'), 0, 1, 1, 'Human behavior studies'),
((SELECT id FROM grades WHERE name = '11'), (SELECT id FROM subjects WHERE name = 'French'), 0, 1, 1, 'French language'),
((SELECT id FROM grades WHERE name = '11'), (SELECT id FROM subjects WHERE name = 'Spanish'), 0, 1, 1, 'Spanish language');

-- Grade 12 Assignments
INSERT IGNORE INTO grade_subject_assignments (grade_id, subject_id, is_required, is_elective, credits, description) VALUES
((SELECT id FROM grades WHERE name = '12'), (SELECT id FROM subjects WHERE name = 'English Language'), 1, 0, 1, 'Advanced English language and literature'),
((SELECT id FROM grades WHERE name = '12'), (SELECT id FROM subjects WHERE name = 'Mathematics'), 1, 0, 1, 'Advanced mathematics and calculus'),
((SELECT id FROM grades WHERE name = '12'), (SELECT id FROM subjects WHERE name = 'Physics'), 0, 1, 1, 'Advanced physics'),
((SELECT id FROM grades WHERE name = '12'), (SELECT id FROM subjects WHERE name = 'Chemistry'), 0, 1, 1, 'Advanced chemistry'),
((SELECT id FROM grades WHERE name = '12'), (SELECT id FROM subjects WHERE name = 'Biology'), 0, 1, 1, 'Advanced biology'),
((SELECT id FROM grades WHERE name = '12'), (SELECT id FROM subjects WHERE name = 'Economics'), 0, 1, 1, 'Advanced economics'),
((SELECT id FROM grades WHERE name = '12'), (SELECT id FROM subjects WHERE name = 'Business Studies'), 0, 1, 1, 'Advanced business studies'),
((SELECT id FROM grades WHERE name = '12'), (SELECT id FROM subjects WHERE name = 'Computer Science'), 0, 1, 1, 'Advanced programming'),
((SELECT id FROM grades WHERE name = '12'), (SELECT id FROM subjects WHERE name = 'Geography'), 0, 1, 1, 'Advanced geography'),
((SELECT id FROM grades WHERE name = '12'), (SELECT id FROM subjects WHERE name = 'History'), 0, 1, 1, 'Advanced history'),
((SELECT id FROM grades WHERE name = '12'), (SELECT id FROM subjects WHERE name = 'Accounting'), 0, 1, 1, 'Financial accounting'),
((SELECT id FROM grades WHERE name = '12'), (SELECT id FROM subjects WHERE name = 'Sociology'), 0, 1, 1, 'Social studies'),
((SELECT id FROM grades WHERE name = '12'), (SELECT id FROM subjects WHERE name = 'Psychology'), 0, 1, 1, 'Human behavior studies'),
((SELECT id FROM grades WHERE name = '12'), (SELECT id FROM subjects WHERE name = 'French'), 0, 1, 1, 'French language'),
((SELECT id FROM grades WHERE name = '12'), (SELECT id FROM subjects WHERE name = 'Spanish'), 0, 1, 1, 'Spanish language'),
((SELECT id FROM grades WHERE name = '12'), (SELECT id FROM subjects WHERE name = 'Literature'), 0, 1, 1, 'English literature'),
((SELECT id FROM grades WHERE name = '12'), (SELECT id FROM subjects WHERE name = 'Environmental Science'), 0, 1, 1, 'Environmental studies');

-- GCE Assignments
INSERT IGNORE INTO grade_subject_assignments (grade_id, subject_id, is_required, is_elective, credits, description) VALUES
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'English Language'), 1, 0, 1, 'GCE English language'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Mathematics'), 1, 0, 1, 'GCE Mathematics'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Physics'), 0, 1, 1, 'GCE Physics'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Chemistry'), 0, 1, 1, 'GCE Chemistry'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Biology'), 0, 1, 1, 'GCE Biology'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Economics'), 0, 1, 1, 'GCE Economics'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Business Studies'), 0, 1, 1, 'GCE Business Studies'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Computer Science'), 0, 1, 1, 'GCE Computer Science'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Geography'), 0, 1, 1, 'GCE Geography'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'History'), 0, 1, 1, 'GCE History'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Accounting'), 0, 1, 1, 'GCE Accounting'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Sociology'), 0, 1, 1, 'GCE Sociology'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Psychology'), 0, 1, 1, 'GCE Psychology'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'French'), 0, 1, 1, 'GCE French'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Spanish'), 0, 1, 1, 'GCE Spanish'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Literature'), 0, 1, 1, 'GCE Literature'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Environmental Science'), 0, 1, 1, 'GCE Environmental Science'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Agriculture'), 0, 1, 1, 'GCE Agriculture'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Home Economics'), 0, 1, 1, 'GCE Home Economics'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Technical Drawing'), 0, 1, 1, 'GCE Technical Drawing'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Woodwork'), 0, 1, 1, 'GCE Woodwork'),
((SELECT id FROM grades WHERE name = 'GCE'), (SELECT id FROM subjects WHERE name = 'Metalwork'), 0, 1, 1, 'GCE Metalwork');

-- Display summary
SELECT 
    'Setup Complete!' as status,
    COUNT(DISTINCT g.id) as total_grades,
    COUNT(DISTINCT s.id) as total_subjects,
    COUNT(gsa.id) as total_assignments
FROM grades g 
CROSS JOIN subjects s 
LEFT JOIN grade_subject_assignments gsa ON g.id = gsa.grade_id AND s.id = gsa.subject_id; 