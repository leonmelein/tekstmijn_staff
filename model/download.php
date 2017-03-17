<?php
/**
 * Created by PhpStorm.
 * User: leon
 * Date: 17-03-17
 * Time: 10:33
 */

function getFiles($db, $staffid){
    $quoted_id = $db->quote($staffid);
    $query = "SELECT file
                FROM submissions, submissions_staff
                WHERE submissions_staff.staff_id = $quoted_id
                AND submissions_staff.submission_id = submissions.id";
    return $db->query($query)->fetchAll(PDO::FETCH_COLUMN);
}