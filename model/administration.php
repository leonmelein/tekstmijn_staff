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

function getInstitution($database, $school_id){
    $data = $database->get(
        "schools",
        [
            "id",
            "name",
            "type_school"
        ],
        [
            "id" => $school_id
        ]
    );

    $typestring = "School";
    if ($data['type_school'] == 1) {
        $typestring = "Universiteit";
    }

    return ["id" => $data['id'],"name" => $data['name'], "type" => $typestring];
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


/*
 * Delete an institution
 *
 * @param Medoo $database A database instance passed as an Medoo object.
 * @param int $id The ID of the institution
 */
function deleteInstitution($database, $id){
    return $database->delete(
        "schools",
        ["id" => $id]
    );
}

/*
 * Gets all classes for an institution
 *
 * @param Medoo $database A database instance passed as an Medoo object.
 * @param int $id The ID of the institution
 */
function getClasses($database, $schoolid){
    $quoted_id = $database->quote($schoolid);
    $query = "SELECT class.id, class.name AS name, class.year AS year, level.name AS level
FROM level, class
WHERE class.level_id = level.id
AND class.school_id = $quoted_id
ORDER BY year, name, level ASC";
    return $database->query($query)->fetchAll();

}

/*
 * Gets a single class of an institution
 *
 * @param Medoo $database A database instance passed as an Medoo object.
 * @param int $id The ID of the class
 */
function getClass($database, $classid){
    $quoted_id = $database->quote($classid);
    $query = "SELECT class.id, class.name AS name, class.year AS year, class.level_id AS levelid, level.name AS level
FROM level, class
WHERE class.level_id = level.id
AND class.id = $quoted_id
ORDER BY year, name, level ASC";
    return $database->query($query)->fetch(PDO::FETCH_ASSOC);
}

/*
 * Adds class to institution
 *
 * @param Medoo $database A database instance passed as an Medoo object.
 * @param Array $post The values posted by the update form
 */
function addClass($database, $schoolid, $post){
    return $database->insert(
        "class",
        [
            "name" => $post["name"],
            "level_id" => $post["type"],
            "year" => $post["year"],
            "school_id" => $schoolid,
            "staff_id" => 0,
        ]
    );
}

function updateClass($database, $classid, $post){
    return $database->update(
        "class",
        [
            "name" => $post["name"],
            "level_id" => $post["type"],
            "year" => $post["year"],
            "staff_id" => 0,
        ],
        ["id" => $classid]
    );
}

/*
 * Delete a class
 *
 * @param Medoo $database A database instance passed as an Medoo object.
 * @param int $id The ID of the class
 */
function deleteClass($database, $id){
    return $database->delete(
        "class",
        ["id" => $id]
    );
}

function getInstitutionStudents($database, $schoolid){
    $quoted_id = $database->quote($schoolid);
    $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) AS name, id
    FROM students
    WHERE school_id = $quoted_id";

    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

function getStudent($database, $studentid, $schoolid){
    return $database->get(
        "students",
        ["[>]class" => ["class_id" => "id"]],
        ["students.id", "firstname", "prefix", "lastname", "birthday", "name", "class_id"],
        [
            "AND" => [
                "students.id" => $studentid,
                "students.school_id" => $schoolid
            ]
        ]
    );
}

function updateStudent($database, $studentid, $post) {
    return $database->update(
        "students",
        [
            "id" => $post["studentid"],
            "firstname" => $post["firstname"],
            "prefix" => $post["prefix"],
            "lastname" => $post["lastname"],
            "birthday" => $post["birthday"],
            "class_id" => $post["class"]
        ],
        ["id" => $studentid]
    );
}

function addStudent($database, $post, $schoolid){
    return $database->insert(
        "students",
        [
            "id" => $post["studentid"],
            "firstname" => $post["firstname"],
            "prefix" => $post["prefix"],
            "lastname" => $post["lastname"],
            "birthday" => $post["birthday"],
            "class_id" => $post["class"],
            "school_id" => $schoolid
        ]
    );
}

function deleteStudent($database, $studentid){
    return $database->delete(
        "students",
        ["id" => $studentid]
    );
}

function getClassList($database, $schoolid){
    return $database->select(
        "class",
        ["id", "name"],
        [
            "school_id" => $schoolid,
            "ORDER" => "year"
        ]
    );
}

function generateNameStr($firstname, $prefix, $lastname){
    if (isset($prefix)){
        return sprintf("%s %s %s", $firstname, $prefix, $lastname);
    } else {
        return sprintf("%s %s", $firstname, $lastname);
    }
}

function getPersonnel($database, $schoolid){
    $personnel = $database->select(
        "staff",
        ["id", "firstname", "prefix", "lastname", "email"],
        ["school_id" => $schoolid, "ORDER" => "firstname"]
    );

    foreach ($personnel as &$person) {
        $person["fullname"] = generateNameStr($person["firstname"], $person["prefix"], $person["lastname"]);
    }

    return $personnel;
}

function getPersonnelMember($database, $personnelid, $schoolid){
    return $database->get(
        "staff",
        ["id", "firstname", "prefix", "lastname", "email"],
        [
            "AND" => [
                "id" => $personnelid,
                "school_id" => $schoolid
            ]
        ]
    );
}

function updatePersonnelMember($database, $personnelid, $post){
    return $database->update(
        "staff",
        [
            "firstname" => $post["firstname"],
            "prefix" => $post["prefix"],
            "lastname" => $post["lastname"],
            "email" => $post["email"]
        ],
        ["id" => $personnelid]
    );
}

function addPersonnelMember($database, $post, $schoolid){
    return $database->insert(
        "staff",
        [
            "firstname" => $post["firstname"],
            "prefix" => $post["prefix"],
            "lastname" => $post["lastname"],
            "email" => $post["email"],
            "school_id" => $schoolid
        ]
    );
}

function deletePersonnelMember($database, $personnelid){
    return $database->delete(
        "staff",
        ["id" => $personnelid]
    );
}