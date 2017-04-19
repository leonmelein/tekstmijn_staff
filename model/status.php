<?php
/**
 * Created by PhpStorm.
 * User: leon
 * Date: 19-04-17
 * Time: 10:34
 */

function getTotalOverview($database){
    $query = "SELECT promised_grades.assignment_id AS id, title, promised, fullfilled
                FROM (
                      SELECT assignments.id AS assignment_id, assignments.title, COUNT(submission_id) AS promised
                      FROM submissions_staff, submissions, assignments
                      WHERE submissions_staff.submission_id = submissions.id
                      AND submissions.assignment_id = assignments.id
                      AND staff_id NOT IN (1,24)
                      GROUP BY assignments.id
                ) AS promised_grades, (
                                SELECT assignment_id, COUNT(grading.grade) AS fullfilled
                                FROM grading, submissions
                                WHERE grading.submission_id = submissions.id
                                AND grading.staff_id NOT IN (1,24)
                                GROUP BY assignment_id
                ) AS fullfilled_grades
                  WHERE promised_grades.assignment_id = fullfilled_grades.assignment_id
                ORDER BY title";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

function getAssignmentOverview($database, $assignment_id){
    $quoted_assignment_id = $database->quote($assignment_id);
    $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) AS StaffName, PromisedGrades.Promised, FullfilledGrades.Fullfilled
                FROM (SELECT grading.staff_id, COUNT(grading.grade) as Fullfilled
                      FROM grading
                      WHERE grading.submission_id IN (
                        SELECT id
                        FROM submissions
                        WHERE assignment_id = $quoted_assignment_id
                      ) AND grading.notes = ''
                      AND grading.staff_id NOT IN (1,24)
                      GROUP BY staff_id) AS FullfilledGrades,
                  (SELECT submissions_staff.staff_id, COUNT(submissions_staff.submission_id) as Promised
                   FROM submissions_staff
                   WHERE submissions_staff.submission_id IN (
                     SELECT id
                     FROM submissions
                     WHERE assignment_id = $quoted_assignment_id
                   ) AND staff_id NOT IN (1, 24)
                   GROUP BY staff_id) AS PromisedGrades,
                   staff
                WHERE PromisedGrades.staff_id = FullfilledGrades.staff_id
                AND staff.id = FullfilledGrades.staff_id
                ORDER BY StaffName
                ";

    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
}