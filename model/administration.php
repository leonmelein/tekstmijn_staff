<?php
/**
 * Administration
 *
 * Module to support administrative functions like adding and removing schools, classes and reviewers.
 */

/*
 * Gets an array of participating schools from the database.
 *
 * @param Medoo $database A database instance passed as an Medoo object.
 * @return Array, associative
 */
function getSchools($database) {
    $query = "SELECT * FROM schools WHERE type_school = 0";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

/*
 * Gets an array of participating universities from the database.
 *
 * @param Medoo $database A database instance passed as an Medoo object.
 * @return Array, associative
 */
function getUniversities($database) {
    $query = "SELECT * FROM schools WHERE type_school = 1";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

/*
 * Gets the name of a participating school.
 *
 * @param Medoo $database A database instance passed as an Medoo object.
 * @return string
 */
function getSchoolName($database, $school_id) {
    $quoted_school_id = $database->quote($school_id);
    $query = "SELECT name FROM schools WHERE id = $quoted_school_id";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['name'];
}


/*
 * Gets the type of a participating school.
 *
 * @param Medoo $database A database instance passed as an Medoo object.
 * @return int
 */
function getSchoolType($database, $school_id) {
    $quoted_school_id = $database->quote($school_id);
    $query = "SELECT type_school FROM schools WHERE id = $quoted_school_id";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['type_school'];
}