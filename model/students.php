<?php
/**
 * Created by PhpStorm.
 * User: leon
 * Date: 26-01-17
 * Time: 10:37
 */

function getClassesForStaff($database, $id){
    $quoted_id = $database->quote($id);
    $query = "SELECT class.id, class.year AS year, level.name AS level, class.name AS name
                FROM level, class
                WHERE class.level_id = level.id
                AND class.id in (
                  SELECT class_id
                  FROM class_staff
                  WHERE class_staff.staff_id = $quoted_id
                )
                ORDER BY level, year ASC";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
}


function getClassName($database, $id){
    return $database->select("class", ["name"], [id => $id])[0]["name"];
}

function getClassStudents($database, $id){
    $quoted_id = $database->quote($id);
    $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) AS name, id
    FROM students
    WHERE class_id = $quoted_id";

    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

function resetStudentPassword($database, $student_id){
    return $database->update("students", ["password" => null], ["id" => $student_id]);
}