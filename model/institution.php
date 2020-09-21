<?php
/**
 * Institution
 *
 * Enables management of schools (providing students and teaching personnel)
 * and universities (providing reviewers and administrators).
 */
class institution extends admin {

    /**
     * Renders an overview of all included institutions, split between schools and universities.
     */
    function overview(){
        $this->get_session();
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Administratie" => "#"]);
        $tabs = $this->tabs($this->bootstrap, ["Scholen" => "#schools", "Universiteiten" => "#universities"], 'Scholen');
        $tbl_schools_data = $this->getSchools();
        $tbl_schools_columns = [
            ["School", "name"],
        ];
        $tbl_schools_options = [
            ["<a class='pull-right' href='institution/%s/edit'><i class='glyphicon glyphicon-pencil'></i> Bewerken</a>"],
            ["<a class='pull-right' href='institution/%s/classes'><i class='glyphicon glyphicon-menu-hamburger'></i> Klassen</a>"],
            ["<a class='pull-right' href='institution/%s/students'><i class='glyphicon glyphicon-education'></i> Leerlingen</a>"],
            ["<a class='pull-right' href='institution/%s/personnel'><i class='glyphicon glyphicon-user'></i> Personeel</a>"]
        ];
        $tbl_schools = $this->table($this->bootstrap, $tbl_schools_columns, $tbl_schools_data, $tbl_schools_options, '<a href="institution/%s/edit">%s</a>');

        $tbl_universities_data = $this->getUniversities();
        $tbl_universities_columns = [
            ["Universiteit", "name"],
        ];
        $tbl_universities_options = [
            ["<a class='pull-right' href='institution/%s/edit'><i class='glyphicon glyphicon-pencil'></i> Bewerken</a>"],
            ["<a class='pull-right' href='institution/%s/reviewers'><i class='glyphicon glyphicon-user'></i> Beoordelaars</a>"]
        ];
        $tbl_universities = $this->table($this->bootstrap, $tbl_universities_columns, $tbl_universities_data, $tbl_universities_options, '<a href="institution/%s/edit">%s</a>');

        echo $this->templates->render("administration::institutions", [
            "title" => "Tekstmijn | Administratie",
            "page_title" => "Administratie",
            "menu" => $menu,
            "breadcrumbs" => $breadcrumbs,
            "tbl_schools" => $tbl_schools,
            "tbl_universities" => $tbl_universities,
            "tabs" => $tabs
        ]);
    }

    /**
     * Provides a form to create new institutions.
     */
    function newInstitution(){
        $this->get_session();

        // Set the type for the new institution
        $type = $_GET["type"];
        if ($type == "school") {
            $school_type = 0;
        } else {
            $school_type = 1;
        }

        // Generate navigational items
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Administratie" => "/staff/administration/", "Onderwijsinstelling toevoegen" => "#"]);

        // Generate page
        echo $this->templates->render("administration::institutions_add", [
            "title" => "Tekstmijn | Administratie",
            "page_title" => "Onderwijsinstelling toevoegen",
            "menu" => $menu,
            "breadcrumbs" => $breadcrumbs,
            "school_type" => $school_type
        ]);
    }

    /**
     * Provides a form to edit an existing institution.
     *
     * @param $school_id int containing the institution ID
     */
    function editInstitution($school_id){
        $this->get_session();

        // Retrieve institution info
        $school_name = $this->getSchoolName($school_id);
        $school_type = $this->getSchoolType($school_id);

        $typestring = "School";
        if ($school_type == 1) {
            $typestring = "Universiteit";
        }

        // Generate navigational items
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Administratie" => "/staff/administration/", sprintf("%s: %s", $typestring, $school_name) => "#"]);

        // Generate page
        echo $this->templates->render("administration::institutions_edit", [
            "title" => "Tekstmijn | Administratie",
            "page_title" => sprintf("%s: %s", $typestring, $school_name),
            "menu" => $menu,
            "breadcrumbs" => $breadcrumbs,
            "school_id" => $school_id,
            "school_name" => $school_name,
            "school_type" => $school_type
        ]);

    }


    /**
     * Saves the newly created institution to the system.
     */
    function saveInstitution(){
        if ($this->addInstitution($_POST)) {
            $this->redirect("/staff/administration/?institution_update=true");
        } else {
            $this->redirect("/staff/administration/?institution_update=false");
        }
    }

    /**
     * Saves updates to the existing institution to the system.
     *
     * @param $school_id int containing the institution ID
     */
    function saveUpdatedInstitution($school_id){
        $this->get_session();
        if ($this->updateInstitution($school_id, $_POST)) {
            $this->redirect("/staff/administration/?institution_update=true");
        } else {
            $this->redirect("/staff/administration/?institution_update=false");
        }
    }

    /**
     * Removes an existing institution from the system.
     *
     * @param $school_id int containing the institution ID
     */
    function delInstitution($school_id){
        if ($this->deleteInstitution($school_id)) {
            $this->redirect("/staff/administration/?institution_update=true");
        } else {
            $this->redirect("/staff/administration/?institution_update=false");
        }
    }
}