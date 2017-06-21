<?php
/**
 * Created by PhpStorm.
 * User: leon
 * Date: 26-01-17
 * Time: 10:37
 */


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
