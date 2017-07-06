<?php class admin extends model
{

    /*
     * Supporting functions
     */

    /*
     * Gets an array of participating schools from the database.
     *
     * @param Medoo $this->database A database instance passed as an Medoo object.
     * @return Array, associative
     */
    function getSchools() {
        $query = "SELECT * FROM schools WHERE type_school = 0";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
     * Gets an array of participating universities from the database.
     *
     * @param Medoo $this->database A database instance passed as an Medoo object.
     * @return Array, associative
     */
    function getUniversities() {
        return $this->database->select(
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
     * @param Medoo $this->database A database instance passed as an Medoo object.
     *  @param int $school_id The id of the institution
     * @return string
     */
    function getSchoolName($school_id) {
        return $this->database->get(
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
     * @param Medoo $this->database A database instance passed as an Medoo object.
     * @param int $school_id The id of the institution
     * @return int
     */
    function getSchoolType($school_id) {
        return $this->database->get(
            "schools",
            "type_school",
            [
                "id" => $school_id
            ]
        );
    }

    function getInstitution($school_id){
        $data = $this->database->get(
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
     * @param Medoo $this->database A database instance passed as an Medoo object.
     * @param int $id The ID of the institution
     * @param Array $post The values posted by the update form
     */
    function updateInstitution($id, $post){
        return $this->database->update(
            "schools",
            ["name" => $post['name'], "type_school" => $post['type']],
            ["id" => $id]);
    }

    /*
     * Adds a new institution with its name and type
     *
     * @param Medoo $this->database A database instance passed as an Medoo object.
     * @param Array $post The values posted by the update form
     */
    function addInstitution($post) {
        return $this->database->insert(
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
     * @param Medoo $this->database A database instance passed as an Medoo object.
     * @param int $id The ID of the institution
     */
    function deleteInstitution( $id){
        return $this->database->delete(
            "schools",
            ["id" => $id]
        );
    }

    /*
     * Gets all classes for an institution
     *
     * @param Medoo $this->database A database instance passed as an Medoo object.
     * @param int $id The ID of the institution
     */
    function getClasses( $schoolid){
        $quoted_id = $this->database->quote($schoolid);
        $query = "SELECT class.id, class.name AS name, class.year AS year, level.name AS level
FROM level, class
WHERE class.level_id = level.id
AND class.school_id = $quoted_id
ORDER BY year, name, level ASC";
        return $this->database->query($query)->fetchAll();

    }

    /*
     * Gets a single class of an institution
     *
     * @param Medoo $this->database A database instance passed as an Medoo object.
     * @param int $id The ID of the class
     */
    function getClass( $classid){
        $quoted_id = $this->database->quote($classid);
        $query = "SELECT class.id, class.name AS name, class.year AS year, class.level_id AS levelid, level.name AS level
FROM level, class
WHERE class.level_id = level.id
AND class.id = $quoted_id
ORDER BY year, name, level ASC";
        return $this->database->query($query)->fetch(PDO::FETCH_ASSOC);
    }

    /*
     * Adds class to institution
     *
     * @param Medoo $this->database A database instance passed as an Medoo object.
     * @param Array $post The values posted by the update form
     */
    function addClass( $schoolid, $post){
        return $this->database->insert(
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

    function updateClass( $classid, $post){
        return $this->database->update(
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
     * @param Medoo $this->database A database instance passed as an Medoo object.
     * @param int $id The ID of the class
     */
    function deleteClass( $id){
        return $this->database->delete(
            "class",
            ["id" => $id]
        );
    }

    function getClassName($id){
        return $this->database->select("class", ["name"], ['id' => $id])[0]["name"];
    }

    function getClassStudents($id){
        $quoted_id = $this->database->quote($id);
        $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) AS name, id
    FROM students
    WHERE class_id = $quoted_id";

        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    function getInstitutionStudents( $schoolid){
        $quoted_id = $this->database->quote($schoolid);
        $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) AS name, id
    FROM students
    WHERE school_id = $quoted_id";

        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    function getStudent( $studentid, $schoolid){
        return $this->database->get(
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

    function updateStudent( $studentid, $post) {
        return $this->database->update(
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

    function addStudent( $post, $schoolid){
        return $this->database->insert(
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

    function deleteStudent($studentid){
        return $this->database->delete(
            "students",
            ["id" => $studentid]
        );
    }

    function getClassList($schoolid){
        return $this->database->select(
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

    function getPersonnel($schoolid){
        $personnel = $this->database->select(
            "staff",
            ["id", "firstname", "prefix", "lastname", "email"],
            ["school_id" => $schoolid, "ORDER" => "firstname"]
        );

        foreach ($personnel as &$person) {
            $person["fullname"] = $this->generateNameStr($person["firstname"], $person["prefix"], $person["lastname"]);
        }

        return $personnel;
    }

    function getPersonnelMember($personnelid, $schoolid){
        return $this->database->get(
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

    function updatePersonnelMember($personnelid, $post){
        return $this->database->update(
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

    function addPersonnelMember($post, $schoolid){
        return $this->database->insert(
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

    function deletePersonnelMember($personnelid){
        return $this->database->delete(
            "staff",
            ["id" => $personnelid]
        );
    }

}