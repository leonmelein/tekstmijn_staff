<?php

/**
 * Created by PhpStorm.
 * User: leon
 * Date: 18-06-17
 * Time: 23:48
 */
class classroom extends model
{

    public function teacherOverview(){
        session_start("staff");
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/classes/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "../account/", "Leerlingen" => "#"]);

        $classes = $this->getClassesForStaff($_SESSION["staff_id"]);
        $columns = [
            ["Naam", "name"],
            ["Niveau", "level"],
            ["Jaar", "year"]
        ];
        $table = $this->table($this->bootstrap, $columns, $classes, null, '<a href="%s/">%s</a>');
        echo $this->templates->render("classes::index", ["title" => "Tekstmijn | Leerlingen",
            "page_title" => "Leerlingen", "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table" => $table]);
    }

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

    public function resetStudentPwd($student_id){
        if($this->resetStudentPassword($student_id)){
            echo '{"status": "success"}';
        } else {
            echo '{"status": "failure"}';
        }
    }


    /**
     * Supporting functions
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

    function getClassName($id){
        return $this->database->get("class", "name", ["id" => $id]);
    }

    private function getClassStudents($id){
        $quoted_id = $this->database->quote($id);
        $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) AS name, id
    FROM students
    WHERE class_id = $quoted_id";

        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    private function resetStudentPassword($student_id){
        return $this->database->update("students", ["password" => null], ["id" => $student_id]);
    }


}