<?php
function getSubmissionsForStaff($database, $id){
    $quoted_id = $database->quote($id);
    $query = "SELECT class.id as id, class.year as year, level.name as level, class.name as class 
                FROM class, level
                WHERE class.level_id = level.id
                AND class.id in (
                  SELECT class_id
                  FROM class_staff
                  WHERE class_staff.staff_id = $quoted_id
                )
                ORDER BY level, year ASC";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

function getAssignmentsforClass($database, $id){
    $quoted_id = $database->quote($id);
    $query = "SELECT id, title, status, DATE_FORMAT(start_date, '%d %M %Y %H:%i') as start_date, DATE_FORMAT(end_date, '%d %M %Y %H:%i') AS end_date
                    FROM (
                        SELECT assignments.id AS id, assignments.title AS title,
                          IF(NOW() BETWEEN assignments_class.start_date AND assignments_class.end_date,
                            'Open', 'Gesloten') AS status,
                             assignments_class.end_date AS end_date,
                             assignments_class.start_date AS start_date
                        FROM assignments, assignments_class
                        WHERE assignments_class.class_id = $quoted_id
                        AND assignments_class.assignment_id = assignments.id
                    ) AS classwork";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

function getSubmissionsForAssignment($database, $id_class, $id_assignment){
    $quoted_id_class = $database->quote($id_class);
    $quoted_id_assignment = $database->quote($id_assignment);
    $query = "SELECT submissions.id as id, students.id as student_id, CONCAT_WS(' ',students.firstname, students.prefix, students.lastname) as name, DATE_FORMAT(submissions.time, '%d %M %Y, %H:%i') as submission_date, submissions.submission_count as submission_count
                FROM students, submissions
                WHERE students.class_id = $quoted_id_class
                      AND submissions.assignment_id = $quoted_id_assignment
                      AND students.id = submissions.student_id";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

function getSubmissionsForAssignmentToLate($database, $id_class, $id_assignment){
    $quoted_id_class = $database->quote($id_class);
    $quoted_id_assignment = $database->quote($id_assignment);
    $query = "SELECT submissions.id as id, students.id as student_id, CONCAT_WS(' ',students.firstname, students.prefix, students.lastname) as name, DATE_FORMAT(submissions.time, '%d %M %Y, %H:%i') as submission_date, submissions.submission_count as submission_count
                FROM students, submissions
                WHERE students.class_id = $quoted_id_class
                      AND submissions.assignment_id = $quoted_id_assignment
                      AND students.id = submissions.student_id 
                      AND submissions.time > (
                        SELECT end_date
                        FROM assignments_class
                        WHERE assignments_class.assignment_id = $quoted_id_assignment
                        AND assignments_class.class_id = $quoted_id_class
                      )";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

function getSubmissionsForAssignmentNoShow($database, $id_class, $id_assignment){
    $quoted_id_class = $database->quote($id_class);
    $quoted_id_assignment = $database->quote($id_assignment);
    $query = "SELECT students.id, CONCAT_WS(' ',students.firstname, students.prefix, students.lastname) as name
                FROM students
                WHERE students.class_id = $quoted_id_class
                  AND students.id NOT IN (
                  SELECT student_id FROM submissions
                  WHERE assignment_id = $quoted_id_assignment
                )";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

function getAssignmentName($database, $id){
    $quoted_id = $database->quote($id);
    $query = "SELECT assignments.title
                FROM assignments
                WHERE assignments.id = $quoted_id";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['title'];
}

function getStudentName($database, $id){
    $quoted_id = $database->quote($id);
    $query = "SELECT CONCAT_WS(' ',students.firstname, students.prefix, students.lastname) as name
                FROM students
                WHERE students.id = (
                SELECT submissions.student_id
                FROM submissions
                WHERE submissions.id = $quoted_id
                )";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['name'];
}

function getSubmissionInfo($database, $id){
    $quoted_id = $database->quote($id);
    $query = "SELECT submissions.time as submission_date, submissions.file as submission_file, submissions.submission_count as submission_count, submissions.original_file as submission_originalfile
                FROM submissions
                WHERE submissions.id = $quoted_id";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
}

function insertGrading($database, $staff_id, $submission_id, $type, $grade){
    $last_user_id = $database->insert("grading", [
        "staff_id" => $staff_id,
        "submission_id" => $submission_id,
        "type" => $type,
        "grade" => $grade
    ]);

    return $last_user_id;
}

function getAssignmentID($database, $id){
    $quoted_id = $database->quote($id);
    $query = "SELECT submissions.assignment_id
                FROM submissions
                WHERE submissions.id = $quoted_id";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['title'];
}