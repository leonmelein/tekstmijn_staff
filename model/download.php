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
    $query = "SELECT file
                FROM submissions, submissions_staff
                WHERE submissions_staff.staff_id = $quoted_staffid
                AND submissions_staff.submission_id = submissions.id
                AND submissions.assignment_id = $quoted_assignemntid";
    return $db->query($query)->fetchAll(PDO::FETCH_COLUMN);
}