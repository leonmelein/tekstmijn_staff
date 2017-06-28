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
                    "classes" => generateOptions($classes),
                    "page_js" => "/staff/vendor/application/load_date_picker.js"
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

        $menu = $this->menu($this->bootstrap, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap,
            [
                $_SESSION["staff_name"] => "/staff/account/",
                "Administratie" => "/staff/administration/",
                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                "Personeel" => sprintf("/staff/administration/institution/%s/personnel/", $school_id),
                "Nieuw personeelslid" => "#"
            ]
        );

        echo $this->templates->render("admin_personnel::add",
            [
                "title" => "Tekstmijn | Administratie",
                "page_title" => "Nieuwe personeelslid",
                "menu" => $menu,
                "breadcrumbs" => $breadcrumbs,
                "page_js" => "/staff/vendor/application/load_date_picker.js"
            ]
        );
    }
    function savePersonnel($school_id){
        if ($this->addPersonnelMember($_POST, $school_id)) {
            $this->redirect("../?personnel_added=true");
        } else {
            $this->redirect("../?personnel_added=false");
        }
    }
}