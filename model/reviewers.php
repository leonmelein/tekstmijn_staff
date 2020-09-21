<?php
/**
 * Reviewers
 *
 * Enables management of university reviewing staff.
 */
class reviewers extends admin {

    /**
     * Renders an overview of all reviewers for a given institution.
     *
     * @param $school_id int containing the university ID
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
                "Beoordelaars" => "#"
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

        echo $this->templates->render("admin_reviewers::reviewers", [
            "title" => "Tekstmijn | Administratie",
            "menu" => $menu,
            "breadcrumbs" => $breadcrumbs,
            "tbl" => $table,
        ]);
    }

    /**
     * Provides a form to add a new reviewer.
     *
     * @param $school_id int containing the university ID
     */
    function newReviewer($school_id){
        $this->get_session();
        $institution = $this->getInstitution($school_id);

        $menu = $this->menu($this->bootstrap, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap,
            [
                $_SESSION["staff_name"] => "/staff/account/",
                "Administratie" => "/staff/administration/",
                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                "Beoordelaars" => sprintf("/staff/administration/institution/%s/personnel/", $school_id),
                "Nieuwe beoordelaar" => "#"
            ]
        );

        echo $this->templates->render("admin_reviewers::add",
            [
                "title" => "Tekstmijn | Administratie",
                "page_title" => "Nieuwe beoordelaar",
                "menu" => $menu,
                "breadcrumbs" => $breadcrumbs,
                "page_js" => "/staff/vendor/application/load_date_picker.js"
            ]
        );
    }

    /**
     * Provides a form to edit an existing reviewer.
     *
     * @param $school_id int containing the university ID
     * @param $personnel_id int containing the reviewer ID
     */
    function editReviewer($school_id, $personnel_id){
        $this->get_session();

        $institution = $this->getInstitution($school_id);
        $personnelMember = $this->getPersonnelMember($personnel_id, $institution["id"]);

        if ($personnelMember) {
            $menu = $this->menu($this->bootstrap, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
            $breadcrumbs = $this->breadcrumbs($this->bootstrap,
                [
                    $_SESSION["staff_name"] => "/staff/account/",
                    "Administratie" => "/staff/administration/",
                    sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                    "Beoordelaars" => sprintf("/staff/administration/institution/%s/reviewers/", $school_id),
                    sprintf("Bewerk beoordelaar: %s %s %s", $personnelMember['firstname'], $personnelMember['prefix'], $personnelMember['lastname']) => "#"
                ]
            );

            echo $this->templates->render("admin_reviewers::edit",
                [
                    "title" => "Tekstmijn | Administratie",
                    "page_title" => sprintf("Beoordelaar: %s %s %s", $personnelMember['firstname'], $personnelMember['prefix'], $personnelMember['lastname']),
                    "menu" => $menu,
                    "breadcrumbs" => $breadcrumbs,
                    "personnelmember" => $personnelMember,
                    "page_js" => "/staff/vendor/application/load_date_picker.js"
                ]
            );
        } else {
            echo "U heeft geen toegang tot deze gegevens."; // TODO: better error message?
        }
    }


    /**
     * Saves a new reviewer to the system.
     *
     * @param $school_id int containing the university ID
     */
    function saveReviewer($school_id){
        $redir = "../?reviewer_added=true";
        $redir_negative = "../?reviewer_added=false";

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

    /**
     * Updates the details of an existing reviewer.
     *
     * @param $school_id int containing the school ID
     * @param $personnel_id int containing the reviewer ID
     */
    function updateReviewer($school_id, $personnel_id){
        if ($this->updatePersonnelMember($personnel_id, $_POST)) {
            $this->redirect("../../?reviewer_update=true");
        } else {
            $this->redirect("../../?reviewer_update=false");
        }
    }

    /**
     * Removes the reviewer from the system.
     *
     * @param $school_id int containing the school ID
     * @param $personnel_id int containing the reviewer ID
     */
    function deleteReviewer($school_id, $personnel_id){
        if ($this->deletePersonnelMember($personnel_id)) {
            $this->redirect("../../?reviewer_deleted=true");
        } else {
            $this->redirect("../../?reviewer_deleted=false");
        }
    }
}