<?php
/**
 * Questionnaires
 *
 * Enables creation and modification of student questionnaires.
 */
class questionnaires extends model {

    /**
     * Renders an overview of all active questionnaires.
     */
    function overview(){
        $this->get_session();
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Vragenlijsten" => "#"]);
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/questionnaire/", "align" => "stacked"], $_SESSION['type']);

        $questionnaires = $this->getQuestionnaires();
        $columns = [
            ["#", "id"],
            ["Titel", "title"],
            ["School", "name"]
        ];
        $options = [
            ["<a class='pull-right' href='%s/'><i class='glyphicon glyphicon-pencil'></i> Bewerken</a>"],
        ];

        $table = $this->table($this->bootstrap, $columns, $questionnaires, $options, '<a href="%s/">%s</a>');

        echo $this->templates->render("questionnaire::overview",
            [
                "breadcrumbs" => $breadcrumbs,
                "menu" => $menu,
                "table" => $table,
                "page_title" => "Vragenlijsten",
                "title" => "Tekstmijn | Vragenlijsten"
            ]);
    }

    /**
     * Provides a form to add a new questionnaire for a school.
     */
    function newQuestionnaire(){
        $this->get_session();
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Vragenlijsten" => "/staff/questionnaire", "Nieuwe vragenlijst" => "#"]);
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/questionnaire/", "align" => "stacked"], $_SESSION['type']);


        echo $this->templates->render("questionnaire::new",
            [
                "breadcrumbs" => $breadcrumbs,
                "menu" => $menu,
                "page_title" => "Nieuwe vragenlijst",
                "title" => "Tekstmijn | Vragenlijsten",
                "options" => $this->options($this->getSchools())
            ]);
    }

    /**
     * Provides a form to edit an existing questionnaire.
     *
     * @param $questionnaire_id int containing the questionnaire ID
     */
    function editQuestionnaire($questionnaire_id){
        $this->get_session();
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Vragenlijsten" => "/staff/questionnaire", "Vragenlijst bewerken" => "#"]);
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/questionnaire/", "align" => "stacked"], $_SESSION['type']);
        $questionnaire = $this->getQuestionnaire($questionnaire_id);

        echo $this->templates->render("questionnaire::edit",
            [
                "breadcrumbs" => $breadcrumbs,
                "menu" => $menu,
                "page_title" => "Bewerk vragenlijst",
                "title" => "Tekstmijn | Vragenlijsten",
                "options" => $this->options($this->getSchools()),
                "questionnaire" => $questionnaire
            ]);
    }

    /**
     * Adds a new questionnaire to the system.
     */
    function addQuestionnaire(){
        $result = $this->database->insert("questionnaire",
            [
                "title" => $_POST['title'],
                "qualtrics_url" => $_POST['qualtrics_url'],
                "school_id" => $_POST['school_id']
            ]);

        if ($result) {
            $this->redirect("../?success=true", 303);
        }
        else {
            $this->redirect("../?success=false", 303);
        }
    }

    /**
     * Saves updates to a questionnaire to the system.
     *
     * @param $questionnaire_id int containing the questionnaire ID
     */
    function updateQuestionnaire($questionnaire_id){
        $result = $this->database->update(
            "questionnaire",
            [
                "title" => $_POST['title'],
                "qualtrics_url" => $_POST['qualtrics_url'],
                "school_id" => $_POST['school_id']
            ],
            ["id" => $questionnaire_id]
        );

        if ($result) {
            $this->redirect("../../?success=true", 303);
        }
        else {
            $this->redirect("../../?success=false", 303);
        }
    }

    /**
     * Removes a questionnaire from the system.
     *
     * @param $questionnaire_id int containing the questionnaire ID
     */
    function deleteQuestionnaire($questionnaire_id){
        $result = $this->database->delete(
            "questionnaire",
            ["id" => $questionnaire_id]
        );

        if ($result) {
            $this->redirect("../../?deleted=true", 303);
        }
        else {
            $this->redirect("../../?deleted=false", 303);
        }
    }

    /*
     * Supporting functions
     */

    /**
     * Retrieves a list of all questionnaires entered into the system.
     *
     * @return array of all questionnaires with ID, title and assigned school per questionnaire
     */
    function getQuestionnaires(){
        return $this->database
            ->query("SELECT questionnaire.id, questionnaire.title, questionnaire.school_id, schools.name 
                            FROM questionnaire, schools WHERE schools.id = questionnaire.school_id")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves an individual questionnaire.
     *
     * @param $id int containing the questionnaire ID
     * @return array containing the title and URL of the questionnaire and name and ID of the relevant school
     */
    function getQuestionnaire($id){
        $qid = $this->database->quote($id);
        return $this->database->query(
            "SELECT title, qualtrics_url, name, school_id FROM questionnaire, schools WHERE questionnaire.school_id = schools.id AND questionnaire.id = $qid"
        )->fetch();
    }

    /**
     * Retrieves a list of schools for use in a <select> element.
     *
     * @return array of schools, containing id and name for each school
     */
    function getSchools() {
        return $this->database->select("schools", ["id", "name"], ["type_school" => 0]);
    }
}