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
    return $database->select(
        "schools",
        "*",
        [
            "type_school" => 1
        ]
    );
}

/*
 * Gets the name of a participating school.
 *
 * @param Medoo $database A database instance passed as an Medoo object.
 *  @param int $school_id The id of the institution
 * @return string
 */
function getSchoolName($database, $school_id) {
    return $database->get(
        "schools",
        "name",
        [
            "id" => $school_id
        ]
    );
}

/*
 * Gets the type of a participating school.
 *
 * @param Medoo $database A database instance passed as an Medoo object.
 * @param int $school_id The id of the institution
 * @return int
 */
function getSchoolType($database, $school_id) {
    return $database->get(
        "schools",
        "type_school",
        [
            "id" => $school_id
        ]
    );
}

/*
 * Updates the institution's name and type
 *
 * @param Medoo $database A database instance passed as an Medoo object.
 * @param int $id The ID of the institution
 * @param Array $post The values posted by the update form
 */
function updateInstitution($database, $id, $post){
    return $database->update(
        "schools",
        ["name" => $post['name'], "type_school" => $post['type']],
        ["id" => $id]);
}

/*
 * Adds a new institution with its name and type
 *
 * @param Medoo $database A database instance passed as an Medoo object.
 * @param Array $post The values posted by the update form
 */
function addInstitution($database, $post) {
    return $database->insert(
        "schools",
        [
            "name" => $post['name'],
            "type_school" => $post['type']
        ]
    );
}