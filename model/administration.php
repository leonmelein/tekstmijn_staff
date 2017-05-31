<?php
/**
 * Created by PhpStorm.
 * User: reinardvandalen
 * Date: 31-05-17
 * Time: 13:29
 */

function getSchools($database)
{
    $query = "SELECT * FROM schools WHERE type_school = 0";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

function getUniversities($database)
{
    $query = "SELECT * FROM schools WHERE type_school = 1";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

function getSchoolName($database, $school_id)
{
    $quoted_school_id = $database->quote($school_id);
    $query = "SELECT name FROM schools WHERE id = $quoted_school_id";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['name'];
}

function getSchoolType($database, $school_id)
{
    $quoted_school_id = $database->quote($school_id);
    $query = "SELECT type_school FROM schools WHERE id = $quoted_school_id";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['type_school'];
}