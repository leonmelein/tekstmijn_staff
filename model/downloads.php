<?php
/**
 * Created by PhpStorm.
 * User: leon
 * Date: 17-03-17
 * Time: 10:33
 */

function getFiles($db, $staffid, $assignmentid){
    $quoted_staffid = $db->quote($staffid);
    $quoted_assignemntid = $db->quote($assignmentid);
    $query = "SELECT CONCAT('/volume1/hofstad/assets/submissions/',file) AS file, original_file
                FROM submissions, submissions_staff
                WHERE submissions_staff.staff_id = $quoted_staffid
                AND submissions_staff.submission_id = submissions.id
                AND submissions.assignment_id = $quoted_assignemntid";
    return $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

function getNames($db, $staffid, $assignmentid){
    $quoted_staffid = $db->quote($staffid);
    $quoted_assignemntid = $db->quote($assignmentid);
    $query = "SELECT CONCAT(firstname,prefix,lastname) as fullname, title as assignment_name
                FROM staff, assignments
                WHERE staff.id = $quoted_staffid
                AND assignments.id = $quoted_assignemntid";
    return $db->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
}