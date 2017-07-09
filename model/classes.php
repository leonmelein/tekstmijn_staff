<?php
/**
 * Classes
 *
 * Enables management of classes and their respective students.
 */
class classes extends admin {
    /*
     * Page routers
     */

    /**
     * Renders an overview of all classes for a given institutions.
     *
     * @param $school_id int containing the institution ID
     */
    function overview($school_id){
        $this->get_session();
        $institution = $this->getInstitution($school_id);
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap,
            [
                $_SESSION["staff_name"] => "/staff/account/",
                "Administratie" => "/staff/administration/",
                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                "Klassen" => "#"
            ]
        );

        $tbl_schools_data = $this->getClasses($school_id);
        $tbl_schools_columns = [
            ["Klas", "name"],
            ["Jaar", "year"],
            ["Niveau", "level"],
        ];
        $tbl_schools_options = [
            ["<a class='pull-right' href='%s/edit'><i class='glyphicon glyphicon-pencil'></i> Bewerken</a>"],
            ["<a class='pull-right' href='%s/'><i class='glyphicon glyphicon-education'></i> Leerlingen</a>"]
        ];
        $tbl_schools = $this->table($this->bootstrap, $tbl_schools_columns, $tbl_schools_data, $tbl_schools_options, '<a href="%s/edit">%s</a>');

        echo $this->templates->render("admin_classes::classes", [
            "title" => "Tekstmijn | Administratie",
            "page_title" => "Klassen",
            "menu" => $menu,
            "breadcrumbs" => $breadcrumbs,
            "tbl_class" => $tbl_schools
        ]);
    }

    /**
     * Renders an overview of all students in a given class.
     *
     * @param $school_id int containing the institution ID
     * @param $class_id int containing the class ID
     */
    function individualClass($school_id, $class_id){
        $this->get_session();
        $institution = $this->getInstitution($school_id);
        $name = sprintf("Klas %s", $this->getClassName($class_id));
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap,
            [
                $_SESSION["staff_name"] => "/staff/account/",
                "Administratie" => "/staff/administration/",
                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                "Klassen" => "../",
                $name => "#"
            ]
        );

        $students = $this->getClassStudents($class_id);
        $columns = [
            ["#", "id"],
            ["Naam", "name"]
        ];
        $options = [
            ["<a class='pull-right' href='../../students/%s/edit'><i class='glyphicon glyphicon-pencil'></i> Bewerken</a>"],
            ["<a class='pull-right' id='%s' onclick='reset_student_pwd(%s, self.document);'><i class='glyphicon glyphicon-repeat'></i> Wachtwoord resetten</a>", "id", '']
        ];

        $table = $this->table($this->bootstrap, $columns, $students, $options, '<a href="../../students/%s/edit">%s</a>');
        echo $this->templates->render("classes::class", ["title" => "Tekstmijn | Administratie",
            "page_title" => $name, "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table" => $table, "page_js" => "/staff/vendor/application/reset_pwd_students.js"]);
    }

    /**
     * Provides a form to create a new class for a given institution.
     *
     * @param $school_id int containing the institution ID
     */
    function newClass($school_id){
        $this->get_session();
        $institution = $this->getInstitution($school_id);
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap,
            [
                $_SESSION["staff_name"] => "/staff/account/",
                "Administratie" => "/staff/administration/",
                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                "Klassen" => sprintf("/staff/administration/institution/%s/classes/", $school_id),
                "Klas toevoegen" => "#",
            ]
        );

        echo $this->templates->render("admin_classes::add", [
            "title" => "Tekstmijn | Administratie",
            "page_title" => "Klas toevoegen",
            "menu" => $menu,
            "breadcrumbs" => $breadcrumbs,
        ]);
    }

    /**
     * Provides a form to edit the details of an existing class.
     *
     * @param $school_id int containing the institution ID
     * @param $class_id int containing the class ID
     */
    function editClass($school_id, $class_id){
        $this->get_session();
        $institution = $this->getInstitution($school_id);
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap,
            [
                $_SESSION["staff_name"] => "/staff/account/",
                "Administratie" => "/staff/administration/",
                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                "Klassen" => sprintf("/staff/administration/institution/%s/classes/", $school_id),
                "Klas bewerken" => "#",
            ]
        );
        $class = $this->getClass($class_id);

        echo $this->templates->render("admin_classes::edit", [
            "title" => "Tekstmijn | Administratie",
            "page_title" => "Klas bewerken",
            "menu" => $menu,
            "breadcrumbs" => $breadcrumbs,
            "name" => $class["name"],
            "levelid" => $class["levelid"],
            "levelname" => $class["level"],
            "year" => $class["year"]
        ]);
    }

    /**
     * Saves the new class to the system.
     *
     * @param $school_id int containing the institution ID
     */
    function saveClass($school_id){
        if ($this->addClass($school_id, $_POST)) {
            $this->redirect("../?institution_update=true");
        } else {
            $this->redirect("../?institution_update=false");
        }
    }

    /**
     * Saves updates to an existing class to the system.
     *
     * @param $school_id int containing the institution ID
     * @param $class_id int containing the class ID
     */
    function saveUpdatedClass($school_id, $class_id){
        if ($this->updateClass($class_id, $_POST)) {
            $this->redirect("../../?institution_update=true");
        } else {
            $this->redirect("../../?institution_update=false");
        }
    }

    /**
     * Deletes a class from the system.
     *
     * @param $school_id int containing the institution ID
     * @param $class_id int containing the class ID
     */
    function delClass($school_id, $class_id){
        if ($this->deleteClass($class_id)) {
            $this->redirect("../../?institution_update=true");
        } else {
            $this->redirect("../../?institution_update=false");
        }
    }
}