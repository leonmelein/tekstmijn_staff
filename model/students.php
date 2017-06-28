<?php

/**
 * Created by PhpStorm.
 * User: leon
 * Date: 28-06-17
 * Time: 12:29
 */
class students extends admin {
    function overview($school_id){
        $this->get_session();
        $institution = $this->getInstitution($school_id);
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap,
            [
                $_SESSION["staff_name"] => "/staff/account/",
                "Administratie" => "/staff/administration/",
                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                "Leerlingen" => "#"
            ]
        );

        $students = $this->getInstitutionStudents($school_id);
        $columns = [
            ["#", "id"],
            ["Naam", "name"]
        ];
        $options = [
            ["<a class='pull-right' href='%s/edit'><i class='glyphicon glyphicon-pencil'></i> Bewerken</a>"],
            ["<a class='pull-right' id='%s' onclick='reset_student_pwd(%s, self.document)'><i class='glyphicon glyphicon-repeat'></i> Wachtwoord resetten</a>", "id", '']
        ];

        $table = $this->table($this->bootstrap, $columns, $students, $options, '<a href="%s/edit">%s</a>');
        echo $this->templates->render("admin_students::students", ["title" => "Tekstmijn | Administratie",
            "page_title" => "Leerlingen", "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table" => $table, "page_js" => "/staff/vendor/application/reset_pwd_students.js"]);
    }
    function newStudent($school_id){
        $this->get_session();
        $classes = $this->getClassList($school_id);
        $institution = $this->getInstitution($school_id);

        $menu = $this->menu($this->bootstrap, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap,
            [
                $_SESSION["staff_name"] => "/staff/account/",
                "Administratie" => "/staff/administration/",
                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                "Leerlingen" => sprintf("/staff/administration/institution/%s/students/", $school_id),
                "Nieuwe leerling" => "#"
            ]
        );

        echo $this->templates->render("admin_students::add",
            [
                "title" => "Tekstmijn | Administratie",
                "page_title" => "Nieuwe leerling",
                "menu" => $menu,
                "breadcrumbs" => $breadcrumbs,
                "classes" => generateOptions($classes),
                "page_js" => "/staff/vendor/application/load_date_picker.js"
            ]
        );
    }
    function saveStudent($school_id){
        if ($this->addStudent($_POST, $school_id)) {
            $this->redirect("../?student_added=true");
        } else {
            $this->redirect("../?student_added=false");
        }
    }
    function editStudent($school_id, $student_id){
        $this->get_session();

        $institution = $this->getInstitution($school_id);
        $classes = $this->getClassList($school_id);
        $student = $this->getStudent($student_id, $institution["id"]);

        if ($student) {
            $menu = $this->menu($this->bootstrap, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
            $breadcrumbs = $this->breadcrumbs($this->bootstrap,
                [
                    $_SESSION["staff_name"] => "/staff/account/",
                    "Administratie" => "/staff/administration/",
                    sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                    "Leerlingen" => sprintf("/staff/administration/institution/%s/students/", $school_id),
                    sprintf("Bewerk leerling #%d: %s %s %s", $student['id'], $student['firstname'], $student['prefix'], $student['lastname']) => "#"
                ]
            );

            echo $this->templates->render("admin_students::edit",
                [
                    "title" => "Tekstmijn | Administratie",
                    "page_title" => sprintf("Leerling #%d: %s %s %s", $student['id'], $student['firstname'], $student['prefix'], $student['lastname']),
                    "menu" => $menu,
                    "breadcrumbs" => $breadcrumbs,
                    "student" => $student,
                    "classes" => generateOptions($classes),
                    "page_js" => "/staff/vendor/application/load_date_picker.js"
                ]
            );
        } else {
            echo "U heeft geen toegang tot deze gegevens."; // TODO: better error message?
        }
    }
    function saveUpdatedStudent($school_id, $student_id){
        if ($this->updateStudent($student_id, $_POST)) {
            $this->redirect("../../?student_update=true");
        } else {
            $this->redirect("../../?student_update=false");
        }
    }
    function delStudent($school_id, $student_id){
        if ($this->deleteStudent($student_id)) {
            $this->redirect("../../?student_deleted=true");
        } else {
            $this->redirect("../../?student_deleted=false");
        }
    }
}