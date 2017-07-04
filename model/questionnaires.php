<?php class questionnaires extends model {

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
    function getQuestionnaires(){
        return $this->database
            ->query("SELECT questionnaire.id, questionnaire.title, questionnaire.school_id, schools.name 
                            FROM questionnaire, schools WHERE schools.id = questionnaire.school_id")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    function getQuestionnaire($id){
        $qid = $this->database->quote($id);
        return $this->database->query(
            "SELECT title, qualtrics_url, name, school_id FROM questionnaire, schools WHERE questionnaire.school_id = schools.id AND questionnaire.id = $qid"
        )->fetch();
    }

    function getSchools() {
        return $this->database->select("schools", ["id", "name"], ["type_school" => 0]);
    }
}