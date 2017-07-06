<?php class personnel extends admin {
    function overview($school_id){
        $this->get_session();
        $institution = $this->getInstitution($school_id);
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap,
            [
                $_SESSION["staff_name"] => "/staff/account/",
                "Administratie" => "/staff/administration/",
                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                "Personeel" => "#"
            ]
        );

        $personnel = $this->getPersonnel($school_id);
        $columns = [
            ["Naam", "fullname"],
            ["Emailadres", "email"]
        ];
        $options = [
            ["<a class='pull-right' href='%s/edit'><i class='glyphicon glyphicon-pencil'></i> Bewerken</a>"],
        ];
        $table = $this->table($this->bootstrap, $columns, $personnel, $options, '<a href="%s/edit">%s</a>');

        echo $this->templates->render("admin_personnel::personnel", [
            "title" => "Tekstmijn | Administratie",
            "menu" => $menu,
            "page_title" => "Personeel",
            "breadcrumbs" => $breadcrumbs,
            "tbl" => $table,
        ]);
    }
    function editPersonnelMember($school_id, $personnel_id){
        $this->get_session();
        $institution = $this->getInstitution($school_id);
        $classes = $this->getClassList($school_id);
        $personnelMember = $this->getPersonnelMember($personnel_id, $institution["id"]);
        $klassen = $this->options_selected($this->getClassesSelect($school_id),$this->getCoupledClasses($personnel_id));

        if ($personnelMember) {
            $menu = $this->menu($this->bootstrap, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
            $breadcrumbs = $this->breadcrumbs($this->bootstrap,
                [
                    $_SESSION["staff_name"] => "/staff/account/",
                    "Administratie" => "/staff/administration/",
                    sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                    "Personeel" => sprintf("/staff/administration/institution/%s/personnel/", $school_id),
                    sprintf("Bewerk personeelslid: %s %s %s", $personnelMember['firstname'], $personnelMember['prefix'], $personnelMember['lastname']) => "#"
                ]
            );

            echo $this->templates->render("admin_personnel::edit",
                [
                    "title" => "Tekstmijn | Administratie",
                    "page_title" => sprintf("Personeelslid: %s %s %s", $personnelMember['firstname'], $personnelMember['prefix'], $personnelMember['lastname']),
                    "menu" => $menu,
                    "breadcrumbs" => $breadcrumbs,
                    "personnelmember" => $personnelMember,
                    "classes" => $this->options($classes),
                    "page_js" => "/staff/vendor/application/load_date_picker.js",
                    "klassen" => $klassen
                ]
            );
        } else {
            echo "U heeft geen toegang tot deze gegevens."; // TODO: better error message?
        }
    }
    function updatePersonnel($school_id, $personnel_id){
        if ($this->updatePersonnelMember($personnel_id, $_POST)) {
            $this->redirect("../../?personnel_update=true");
        } else {
            $this->redirect("../../?personnel_update=false");
        }
    }
    function deletePersonnel($school_id, $personnel_id){
        if ($this->deletePersonnelMember($personnel_id)) {
            $this->redirect("../../?personnel_deleted=true");
        } else {
            $this->redirect("../../?personnel_deleted=false");
        }
    }
    function newPersonnelMember($school_id){
        $this->get_session();
        $institution = $this->getInstitution($school_id);
        $classes = $this->options($this->getClassesSelect($school_id));

        $menu = $this->menu($this->bootstrap, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap,
            [
                $_SESSION["staff_name"] => "/staff/account/",
                "Administratie" => "/staff/administration/",
                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                "Personeel" => sprintf("/staff/administration/institution/%s/personnel/", $school_id),
                "Nieuw personeelslid" => "#",
            ]
        );

        echo $this->templates->render("admin_personnel::add",
            [
                "title" => "Tekstmijn | Administratie",
                "page_title" => "Nieuwe personeelslid",
                "menu" => $menu,
                "breadcrumbs" => $breadcrumbs,
                "page_js" => "/staff/vendor/application/load_date_picker.js",
                "klassen" => $classes
            ]
        );
    }
    function savePersonnel($school_id){
        $redir = "../?personnel_added=true";
        $redir_negative = "../?personnel_added=false";

        if ($this->addPersonnelMember($_POST, $school_id)) {
            $sanitized_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            if (filter_var($sanitized_email, FILTER_VALIDATE_EMAIL)) {
                $result = $this->mail($sanitized_email, "Tekstmijn - Nieuwe gebruiker", "mail::newuser");
                if(!$result) {
                    $this->redirect($redir_negative);
                } else {
                    $this->redirect($redir);
                }
            }
        } else {
            $this->redirect($redir_negative);
        }
    }

    //Helper functions

    function getClassesSelect($school_id){
        $quoted_school_id = $this->database->quote($school_id);
        $separator = "': '";
        return $this->database->query("SELECT name, id FROM class WHERE school_id = $quoted_school_id")->fetchAll(PDO::FETCH_ASSOC);
    }

    function getCoupledClasses($id){
        $return = Array();
        $id = $this->database->quote($id);
        $query = "SELECT class_id
                  FROM class_staff
                  WHERE staff_id = $id
                  ";
        $export = $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($export as $index => $class_id) {
            array_push($return, $class_id['class_id']);
        }
        return $return;
    }
}
