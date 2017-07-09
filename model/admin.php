<?php class admin extends model
{

    /*
     * Supporting functions
     */

    /**
     * Gets an array of participating schools from the database.
     *
     * @return array, associative
     */
    function getSchools() {
        $query = "SELECT * FROM schools WHERE type_school = 0";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets an array of participating universities from the database.
     *
     * @return array, associative
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

    /**
     * Gets the name of a participating school.
     *
     * @param $school_id int The id of the institution
     * @return string containing the schoo name
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

    /**
     * Gets the type of a participating school.
     *
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

    /**
     * Get the ID, name and type of institution.
     *
     * @param $school_id int containing the institution ID
     * @return array containing id, name and type
     */
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

    /**
     * Updates the institution's name and type
     *
     * @param $id int ID of the institution
     * @param $post array The values posted by the update form
     * @return int indicating if the operation succeeded
     */
    function updateInstitution($id, $post){
        return $this->database->update(
            "schools",
            ["name" => $post['name'], "type_school" => $post['type']],
            ["id" => $id]);
    }

    /**
     * Adds a new institution with its name and type
     *
     * @param $post array The values posted by the update form
     * @return int indicating if the operation succeeded
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

    /**
     * Delete an institution
     *
     * @param $id int The ID of the institution
     * @return int indicating if the operation succeeded
     */
    function deleteInstitution( $id){
        return $this->database->delete(
            "schools",
            ["id" => $id]
        );
    }

    /**
     * Gets all classes for an institution
     *
     * @param $id int containing the institution ID
     * @return array containing class ID, name, level and year
     */
    function getClasses($schoolid){
        $quoted_id = $this->database->quote($schoolid);
        $query = "SELECT class.id, class.name AS name, class.year AS year, level.name AS level
FROM level, class
WHERE class.level_id = level.id
AND class.school_id = $quoted_id
ORDER BY year, name, level ASC";
        return $this->database->query($query)->fetchAll();

    }

    /**
     * Gets a single class of an institution
     *
     * @param $id int containing the class ID
     * @return
     */
    function getClass($classid){
        $quoted_id = $this->database->quote($classid);
        $query = "SELECT class.id, class.name AS name, class.year AS year, class.level_id AS levelid, level.name AS level
FROM level, class
WHERE class.level_id = level.id
AND class.id = $quoted_id
ORDER BY year, name, level ASC";
        return $this->database->query($query)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Adds class to institution
     *
     * @param $post array values posted by the update form
     * @return int indicating if the operation succeeded
     */
    function addClass($schoolid, $post){
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

    /**
     * Updates the class details.
     *
     * @param $classid int containing the class ID
     * @param $post array containing the $_POST values
     * @return int indicating if the operation succeeded
     */
    function updateClass($classid, $post){
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

    /**
     * Delete a class
     *
     * @param $id int containing the class ID
     * @return int indicating if the operation succeeded
     */
    function deleteClass( $id){
        return $this->database->delete(
            "class",
            ["id" => $id]
        );
    }

    /**
     * Retrieve the name of a class.
     *
     * @param $id int containing the class ID
     * @return string containing the class name
     */
    function getClassName($id){
        return $this->database->select("class", ["name"], ['id' => $id])[0]["name"];
    }

    /**
     * Generate a list of student ID's and names for a given class.
     *
     * @param $id containing the class ID
     * @return array containing the student ID's and names for the class
     */
    function getClassStudents($id){
        $quoted_id = $this->database->quote($id);
        $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) AS name, id
    FROM students
    WHERE class_id = $quoted_id";

        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate a list of all students enrolled at a particular institution.
     *
     * @param $schoolid int containing the school ID
     * @return array containing the names and ids of all students
     */
    function getInstitutionStudents($schoolid){
        $quoted_id = $this->database->quote($schoolid);
        $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) AS name, id
    FROM students
    WHERE school_id = $quoted_id";

        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the information of an individual student.
     *
     * @param $studentid int containing the student ID
     * @param $schoolid int containing the school ID
     * @return array containing the ID, name, birthday and class for the student.
     */
    function getStudent($studentid, $schoolid){
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

    /**
     * Update an existing student's details.
     *
     * @param $studentid int containing the student ID
     * @param $post array containing the $_POST values
     * @return int indicating if the operation succeeded
     */
    function updateStudent($studentid, $post) {
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

    /**
     * Add a new student to the school.
     *
     * @param $post array containing the $_POST values
     * @param $schoolid int containing the school ID
     * @return int indicating if the operation succeeded
     */
    function addStudent($post, $schoolid){
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

    /**
     * Removes a student from the school and system.
     *
     * @param $studentid int containing the student ID
     * @return int indicating if the operation succeeded
     */
    function deleteStudent($studentid){
        return $this->database->delete(
            "students",
            ["id" => $studentid]
        );
    }

    /**
     * Gather a list of classes and their ID's for a given school.
     *
     * @param $schoolid int containing the school ID
     * @return array containing each class's name and ID
     */
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

    /**
     * Generate a full name string for its comprising parts.
     *
     * @param $firstname string containing the person's first name
     * @param $prefix string containing possible prefixes
     * @param $lastname string containing the person's last name
     * @return string containing the full name
     */
    function generateNameStr($firstname, $prefix, $lastname){
        if (isset($prefix)){
            return sprintf("%s %s %s", $firstname, $prefix, $lastname);
        } else {
            return sprintf("%s %s", $firstname, $lastname);
        }
    }

    /**
     * Generate an overview of all personnel members of a school.
     *
     * @param $schoolid int containing the school ID
     * @return array containing the personnel members' ID, name and email address
     */
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

    /**
     * Retrieve an individual personnel member.
     *
     * @param $personnelid int containing the staff member's ID
     * @param $schoolid int containing the school ID
     * @return array containing the personnel member's ID, name, email address and type
     */
    function getPersonnelMember($personnelid, $schoolid){
        return $this->database->get(
            "staff",
            ["id", "firstname", "prefix", "lastname", "email", "type"],
            [
                "AND" => [
                    "id" => $personnelid,
                    "school_id" => $schoolid
                ]
            ]
        );
    }

    /**
     * Update the details of an existing personnel member.
     *
     * @param $personnelid int containing the staff member's ID
     * @param $post array containing the $_POST values
     * @return int indicating if the operation succeeded
     */
    function updatePersonnelMember($personnelid, $post){
        $results = Array();

        //Check if a 'beoordelaar', 'beheerder' or 'personeelslid' wordt geupadte
        if (!isset($post["type"])) {
            $type = 0;
        }
        else {
            $type = $post["type"];
        }
        $result = $this->database->update(
            "staff",
            [
                "firstname" => $post["firstname"],
                "prefix" => $post["prefix"],
                "lastname" => $post["lastname"],
                "email" => $post["email"],
                "type" => $type
            ],
            ["id" => $personnelid]
        );
        array_push($results, $result);

        //Update class_staff
        if (isset($post["klassen"])) {
            $klassen = $post["klassen"];
        }
        else {
            $klassen = Array();
        }

        $this->database->delete('class_staff',['staff_id' => $personnelid]);
        foreach ($klassen as $index => $class_id) {
            $result = $this->database->insert(
                "class_staff",
                [
                    "class_id" => $class_id,
                    "staff_id" => $personnelid
                ]
            );
            array_push($results, $result);
        }

        //Check if everything went fine
        if (!in_array(False, $results)) {
            return True;
        }
        else {
            return False;
        }
    }

    /**
     * Adds a new personnel member to a school.
     *
     * @param $post array containing the $_POST values
     * @param $schoolid int containing the school ID
     * @return int indicating if the operation succeeded
     */
    function addPersonnelMember($post, $schoolid){
        $results = Array();

        //Check if a 'beoordelaar', 'beheerder' or 'personeelslid' wordt toegevoegd
        if (!isset($post["type"])) {
            $type = 0;
        }
        else {
            $type = $post["type"];
        }
        $result = $this->database->insert(
            "staff",
            [
                "firstname" => $post["firstname"],
                "prefix" => $post["prefix"],
                "lastname" => $post["lastname"],
                "email" => $post["email"],
                "school_id" => $schoolid,
                "type" => $type
            ]
        );
        array_push($results, $result);

        //Check if there are classes to couple
        $personnell_id = $this->database->get('staff','id',['email' => $post["email"]]);

        if (isset($post["klassen"])){
            foreach ($post["klassen"] as $index => $class_id) {
                $result = $this->database->insert(
                    "class_staff",
                    [
                        "class_id" => $class_id,
                        "staff_id" => $personnell_id
                    ]
                );
                array_push($results, $result);
            }
        }

        //Check if everything went fine
        if (!in_array(False, $results)) {
            return True;
        }
        else {
            return False;
        }
    }

    /**
     * Removes a personnel member from the school and the system.
     *
     * @param $personnelid int containing the personnel member's ID
     * @return int indicating if the operation succeeded
     */
    function deletePersonnelMember($personnelid){
        return $this->database->delete(
            "staff",
            ["id" => $personnelid]
        );
    }

}