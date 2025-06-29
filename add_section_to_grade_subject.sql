-- Add section column to existing grade_subject_assignments table
-- This allows assigning subjects to specific grade sections (e.g., Grade 8A, Grade 8B)

-- 1. Add section column to grade_subject_assignments table
ALTER TABLE grade_subject_assignments 
ADD COLUMN section VARCHAR(10) AFTER grade_id;

-- 2. Update unique constraint to include section
ALTER TABLE grade_subject_assignments 
DROP INDEX unique_grade_subject;

ALTER TABLE grade_subject_assignments 
ADD UNIQUE KEY unique_grade_section_subject (grade_id, section, subject_id);

-- 3. Insert sample grade-section-subject assignments
-- Grade 8A Assignments
INSERT IGNORE INTO grade_subject_assignments (grade_id, section, subject_id, is_required, is_elective, credits, description) VALUES
((SELECT id FROM grades WHERE name = '8'), 'A', (SELECT id FROM subjects WHERE name = 'English Language'), 1, 0, 1, 'Core English for Grade 8A'),
((SELECT id FROM grades WHERE name = '8'), 'A', (SELECT id FROM subjects WHERE name = 'Mathematics'), 1, 0, 1, 'Core Mathematics for Grade 8A'),
((SELECT id FROM grades WHERE name = '8'), 'A', (SELECT id FROM subjects WHERE name = 'Science'), 1, 0, 1, 'Core Science for Grade 8A'),
((SELECT id FROM grades WHERE name = '8'), 'A', (SELECT id FROM subjects WHERE name = 'Religious Education'), 1, 0, 1, 'Religious Education for Grade 8A'),
((SELECT id FROM grades WHERE name = '8'), 'A', (SELECT id FROM subjects WHERE name = 'Physical Education'), 1, 0, 1, 'Physical Education for Grade 8A'),
((SELECT id FROM grades WHERE name = '8'), 'A', (SELECT id FROM subjects WHERE name = 'Information and Communication Technology'), 0, 1, 1, 'ICT for Grade 8A'),
((SELECT id FROM grades WHERE name = '8'), 'A', (SELECT id FROM subjects WHERE name = 'Geography'), 0, 1, 1, 'Geography for Grade 8A');

-- Grade 8B Assignments
INSERT IGNORE INTO grade_subject_assignments (grade_id, section, subject_id, is_required, is_elective, credits, description) VALUES
((SELECT id FROM grades WHERE name = '8'), 'B', (SELECT id FROM subjects WHERE name = 'English Language'), 1, 0, 1, 'Core English for Grade 8B'),
((SELECT id FROM grades WHERE name = '8'), 'B', (SELECT id FROM subjects WHERE name = 'Mathematics'), 1, 0, 1, 'Core Mathematics for Grade 8B'),
((SELECT id FROM grades WHERE name = '8'), 'B', (SELECT id FROM subjects WHERE name = 'Science'), 1, 0, 1, 'Core Science for Grade 8B'),
((SELECT id FROM grades WHERE name = '8'), 'B', (SELECT id FROM subjects WHERE name = 'Religious Education'), 1, 0, 1, 'Religious Education for Grade 8B'),
((SELECT id FROM grades WHERE name = '8'), 'B', (SELECT id FROM subjects WHERE name = 'Physical Education'), 1, 0, 1, 'Physical Education for Grade 8B'),
((SELECT id FROM grades WHERE name = '8'), 'B', (SELECT id FROM subjects WHERE name = 'Information and Communication Technology'), 0, 1, 1, 'ICT for Grade 8B'),
((SELECT id FROM grades WHERE name = '8'), 'B', (SELECT id FROM subjects WHERE name = 'History'), 0, 1, 1, 'History for Grade 8B');

-- Grade 9A Assignments
INSERT IGNORE INTO grade_subject_assignments (grade_id, section, subject_id, is_required, is_elective, credits, description) VALUES
((SELECT id FROM grades WHERE name = '9'), 'A', (SELECT id FROM subjects WHERE name = 'English Language'), 1, 0, 1, 'Core English for Grade 9A'),
((SELECT id FROM grades WHERE name = '9'), 'A', (SELECT id FROM subjects WHERE name = 'Mathematics'), 1, 0, 1, 'Core Mathematics for Grade 9A'),
((SELECT id FROM grades WHERE name = '9'), 'A', (SELECT id FROM subjects WHERE name = 'Science'), 1, 0, 1, 'Core Science for Grade 9A'),
((SELECT id FROM grades WHERE name = '9'), 'A', (SELECT id FROM subjects WHERE name = 'Religious Education'), 1, 0, 1, 'Religious Education for Grade 9A'),
((SELECT id FROM grades WHERE name = '9'), 'A', (SELECT id FROM subjects WHERE name = 'Physical Education'), 1, 0, 1, 'Physical Education for Grade 9A'),
((SELECT id FROM grades WHERE name = '9'), 'A', (SELECT id FROM subjects WHERE name = 'Information and Communication Technology'), 0, 1, 1, 'ICT for Grade 9A'),
((SELECT id FROM grades WHERE name = '9'), 'A', (SELECT id FROM subjects WHERE name = 'Geography'), 0, 1, 1, 'Geography for Grade 9A'),
((SELECT id FROM grades WHERE name = '9'), 'A', (SELECT id FROM subjects WHERE name = 'Art'), 0, 1, 1, 'Art for Grade 9A');

-- Grade 10A Assignments
INSERT IGNORE INTO grade_subject_assignments (grade_id, section, subject_id, is_required, is_elective, credits, description) VALUES
((SELECT id FROM grades WHERE name = '10'), 'A', (SELECT id FROM subjects WHERE name = 'English Language'), 1, 0, 1, 'Core English for Grade 10A'),
((SELECT id FROM grades WHERE name = '10'), 'A', (SELECT id FROM subjects WHERE name = 'Mathematics'), 1, 0, 1, 'Core Mathematics for Grade 10A'),
((SELECT id FROM grades WHERE name = '10'), 'A', (SELECT id FROM subjects WHERE name = 'Physics'), 0, 1, 1, 'Physics for Grade 10A'),
((SELECT id FROM grades WHERE name = '10'), 'A', (SELECT id FROM subjects WHERE name = 'Chemistry'), 0, 1, 1, 'Chemistry for Grade 10A'),
((SELECT id FROM grades WHERE name = '10'), 'A', (SELECT id FROM subjects WHERE name = 'Biology'), 0, 1, 1, 'Biology for Grade 10A'),
((SELECT id FROM grades WHERE name = '10'), 'A', (SELECT id FROM subjects WHERE name = 'Religious Education'), 1, 0, 1, 'Religious Education for Grade 10A'),
((SELECT id FROM grades WHERE name = '10'), 'A', (SELECT id FROM subjects WHERE name = 'Physical Education'), 1, 0, 1, 'Physical Education for Grade 10A'),
((SELECT id FROM grades WHERE name = '10'), 'A', (SELECT id FROM subjects WHERE name = 'Computer Science'), 0, 1, 1, 'Computer Science for Grade 10A');

-- Grade 11A Assignments
INSERT IGNORE INTO grade_subject_assignments (grade_id, section, subject_id, is_required, is_elective, credits, description) VALUES
((SELECT id FROM grades WHERE name = '11'), 'A', (SELECT id FROM subjects WHERE name = 'English Language'), 1, 0, 1, 'Core English for Grade 11A'),
((SELECT id FROM grades WHERE name = '11'), 'A', (SELECT id FROM subjects WHERE name = 'Mathematics'), 1, 0, 1, 'Core Mathematics for Grade 11A'),
((SELECT id FROM grades WHERE name = '11'), 'A', (SELECT id FROM subjects WHERE name = 'Physics'), 0, 1, 1, 'Physics for Grade 11A'),
((SELECT id FROM grades WHERE name = '11'), 'A', (SELECT id FROM subjects WHERE name = 'Chemistry'), 0, 1, 1, 'Chemistry for Grade 11A'),
((SELECT id FROM grades WHERE name = '11'), 'A', (SELECT id FROM subjects WHERE name = 'Biology'), 0, 1, 1, 'Biology for Grade 11A'),
((SELECT id FROM grades WHERE name = '11'), 'A', (SELECT id FROM subjects WHERE name = 'Economics'), 0, 1, 1, 'Economics for Grade 11A'),
((SELECT id FROM grades WHERE name = '11'), 'A', (SELECT id FROM subjects WHERE name = 'Computer Science'), 0, 1, 1, 'Computer Science for Grade 11A'),
((SELECT id FROM grades WHERE name = '11'), 'A', (SELECT id FROM subjects WHERE name = 'French'), 0, 1, 1, 'French for Grade 11A');

-- Grade 12A Assignments
INSERT IGNORE INTO grade_subject_assignments (grade_id, section, subject_id, is_required, is_elective, credits, description) VALUES
((SELECT id FROM grades WHERE name = '12'), 'A', (SELECT id FROM subjects WHERE name = 'English Language'), 1, 0, 1, 'Core English for Grade 12A'),
((SELECT id FROM grades WHERE name = '12'), 'A', (SELECT id FROM subjects WHERE name = 'Mathematics'), 1, 0, 1, 'Core Mathematics for Grade 12A'),
((SELECT id FROM grades WHERE name = '12'), 'A', (SELECT id FROM subjects WHERE name = 'Physics'), 0, 1, 1, 'Physics for Grade 12A'),
((SELECT id FROM grades WHERE name = '12'), 'A', (SELECT id FROM subjects WHERE name = 'Chemistry'), 0, 1, 1, 'Chemistry for Grade 12A'),
((SELECT id FROM grades WHERE name = '12'), 'A', (SELECT id FROM subjects WHERE name = 'Biology'), 0, 1, 1, 'Biology for Grade 12A'),
((SELECT id FROM grades WHERE name = '12'), 'A', (SELECT id FROM subjects WHERE name = 'Economics'), 0, 1, 1, 'Economics for Grade 12A'),
((SELECT id FROM grades WHERE name = '12'), 'A', (SELECT id FROM subjects WHERE name = 'Computer Science'), 0, 1, 1, 'Computer Science for Grade 12A'),
((SELECT id FROM grades WHERE name = '12'), 'A', (SELECT id FROM subjects WHERE name = 'Literature'), 0, 1, 1, 'Literature for Grade 12A');

-- GCE A Assignments
INSERT IGNORE INTO grade_subject_assignments (grade_id, section, subject_id, is_required, is_elective, credits, description) VALUES
((SELECT id FROM grades WHERE name = 'GCE'), 'A', (SELECT id FROM subjects WHERE name = 'English Language'), 1, 0, 1, 'GCE English for Section A'),
((SELECT id FROM grades WHERE name = 'GCE'), 'A', (SELECT id FROM subjects WHERE name = 'Mathematics'), 1, 0, 1, 'GCE Mathematics for Section A'),
((SELECT id FROM grades WHERE name = 'GCE'), 'A', (SELECT id FROM subjects WHERE name = 'Physics'), 0, 1, 1, 'GCE Physics for Section A'),
((SELECT id FROM grades WHERE name = 'GCE'), 'A', (SELECT id FROM subjects WHERE name = 'Chemistry'), 0, 1, 1, 'GCE Chemistry for Section A'),
((SELECT id FROM grades WHERE name = 'GCE'), 'A', (SELECT id FROM subjects WHERE name = 'Biology'), 0, 1, 1, 'GCE Biology for Section A'),
((SELECT id FROM grades WHERE name = 'GCE'), 'A', (SELECT id FROM subjects WHERE name = 'Economics'), 0, 1, 1, 'GCE Economics for Section A'),
((SELECT id FROM grades WHERE name = 'GCE'), 'A', (SELECT id FROM subjects WHERE name = 'Computer Science'), 0, 1, 1, 'GCE Computer Science for Section A'),
((SELECT id FROM grades WHERE name = 'GCE'), 'A', (SELECT id FROM subjects WHERE name = 'French'), 0, 1, 1, 'GCE French for Section A');

-- Verify the setup
SELECT 
    'Grade-Section-Subject Setup Complete!' as status,
    COUNT(DISTINCT g.id) as total_grades,
    COUNT(DISTINCT CONCAT(gsa.grade_id, gsa.section)) as total_grade_sections,
    COUNT(DISTINCT s.id) as total_subjects,
    COUNT(gsa.id) as total_assignments
FROM grades g 
LEFT JOIN grade_subject_assignments gsa ON g.id = gsa.grade_id
LEFT JOIN subjects s ON gsa.subject_id = s.id; 