<?php
/**
 * Classrooms
 *
 * Displays a list of classes and their respective students to teachers.
 */
class classroom extends model
{

    /**
     * Renders an overview of all classes assigned to the teacher.
     */
    public function teacherOverview(){
        $this->get_session();

        // Generating navigational items
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/classes/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "../account/", "Leerlingen" => "#"]);

        // Generating classes overview
        $classes = $this->getClassesForStaff($_SESSION["staff_id"]);
        $columns = [
            ["Naam", "name"],
            ["Niveau", "level"],
            ["Jaar", "year"]
        ];
        $table = $this->table($this->bootstrap, $columns, $classes, null, '<a href="%s/">%s</a>');

        // Generating page
        echo $this->templates->render("classes::index", ["title" => "Tekstmijn | Leerlingen",
            "page_title" => "Leerlingen", "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table" => $table]);
    }

    /**
     * Renders an overview of all students in an individual class.
     *
     * @param $class_id int containing the class ID
     */
    public function individualClass($class_id){
        session_start("staff");

        $name = sprintf("Klas %s", $this->getClassName($class_id));
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/classes/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Leerlingen" => "/staff/classes/",
            $name => "#"]);

        $students = $this->getClassStudents($class_id);
        $columns = [
            ["#", "id"],
            ["Naam", "name"]
        ];
        $options = [
            ["<a class='pull-right' id='%s' onclick='reset_student_pwd(%s, self.document)'><i class='glyphicon glyphicon-repeat'></i> Wachtwoord resetten</a>", "id", '']
        ];

        $table = $this->table($this->bootstrap, $columns, $students, $options);
        echo $this->templates->render("classes::class", ["title" => "Tekstmijn | Leerlingen",
            "page_title" => $name, "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table" => $table, "page_js" => "/staff/vendor/application/reset_pwd_students.js"]);
    }

    /**
     * Resets a student's password on the request of a teacher or administrator.
     *
     * @param $student_id int containing the ID of the student involved
     * @return array containing the status of the action, encoded in JSON
     */
    public function resetStudentPwd($student_id){
        if($this->resetStudentPassword($student_id)){
            echo '{"status": "success"}';
        } else {
            echo '{"status": "failure"}';
        }
    }

    /*
     * Supporting functions
     */

    /**
     * Gets a list of classes assigned to a given member of staff.
     *
     * @param $id int containing the staff member's ID
     * @return array containing the different classes, their respective level and years.
     */
    private function getClassesForStaff($id){
        $quoted_id = $this->database->quote($id);
        $query = "SELECT class.id, class.year AS year, level.name AS level, class.name AS name
                FROM level, class
                WHERE class.level_id = level.id
                AND class.id in (
                  SELECT class_id
                  FROM class_staff
                  WHERE class_staff.staff_id = $quoted_id
                )
                ORDER BY year, name, level ASC";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves the name of a class for a given class ID.
     *
     * @param $id int containing the class ID
     * @return bool|string containing the name or False if the class ID does not exist.
     */
    function getClassName($id){
        return $this->database->get("class", "name", ["id" => $id]);
    }

    /**
     * Retrieves a list of all students included in a given class.
     *
     * @param $id int containing the class ID
     * @return array containing the name and ID of each student
     */
    private function getClassStudents($id){
        $quoted_id = $this->database->quote($id);
        $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) AS name, id
    FROM students
    WHERE class_id = $quoted_id";

        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Resets the student password in the database by null'ing the password.
     *
     * @param $student_id int containing the student ID
     * @return bool|int indicating if the operation succeeded
     */
    private function resetStudentPassword($student_id){
        return $this->database->update("students", ["password" => null], ["id" => $student_id]);
    }


}