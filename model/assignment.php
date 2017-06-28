<?php

/**
 * Created by PhpStorm.
 * User: leon
 * Date: 28-06-17
 * Time: 11:48
 */
class assignment extends model {
    function overview(){
        $this->get_session();
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Opdrachten" => "#"]);
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/assignment/", "align" => "stacked"], $_SESSION['type']);
        $students = $this->getAssignment($_SESSION['staff_id']);
        $columns = [
            ["Titel", "title"]
        ];
        $options = [
            ["<a class='pull-right' href='%s/'><i class='glyphicon glyphicon-pencil'></i> Bewerken</a>"],
        ];

        $table = $this->table($this->bootstrap, $columns, $students, $options, '<a href="%s/">%s</a>');
        echo $this->templates->render("assignment::overview", ["title" => "Tekstmijn | Opdrachten",
            "page_title" => "Opdrachten",
            "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table" => $table
        ]);
    }

    function individualAssignment($assignmentid){
        $this->get_session();
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Opdrachten" => "/staff/assignment", "Bewerk opdracht" => "#"]);
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/assignment/", "align" => "stacked"], $_SESSION['type']);

        $classes = $this->options($this->getSchoolsAndClasses());
        $reviewers = $this->options($this->getReviewers());

        echo $this->templates->render("assignment::edit", ["title" => "Tekstmijn | Opdrachten",
            "page_title" => "Nieuwe opdracht",
            "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "classes" => $classes, "reviewers" => $reviewers,
            "page_js" => "/staff/vendor/application/load_date_picker.js"
        ]);
    }

    function getAssignment($staff){
        $quoted_staff_id = $this->database->quote($staff);
        $query = "SELECT assignments.title, assignments.id FROM assignments ORDER BY title ASC";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    function getSchoolsAndClasses(){
        $separator = "': '";
        return $this->database->query("SELECT CONCAT_WS($separator, schools.name, class.name) AS name, class.id FROM schools, class WHERE schools.id = class.school_id")->fetchAll(PDO::FETCH_ASSOC);
    }

    function getReviewers(){
        $reviewers =  $this->database->select(
            "staff",
            [
                "firstname",
                "lastname",
                "prefix",
                "id"
            ],
            [
                "type" => [1,2],
                "ORDER" => [
                    "firstname" => "ASC"
                ]
            ]
        );
        foreach ($reviewers as &$reviewer) {
            $reviewer["name"] = $this->generateNameStr(
                $reviewer['firstname'],
                $reviewer['prefix'],
                $reviewer['lastname']
            );
            unset(
                $reviewer['firstname'],
                    $reviewer['prefix'],
                    $reviewer['lastname']
            );
        }
        return $reviewers;
    }

    private function generateNameStr($firstname, $prefix, $lastname){
        if (isset($prefix)){
            return sprintf("%s %s %s", $firstname, $prefix, $lastname);
        } else {
            return sprintf("%s %s", $firstname, $lastname);
        }
    }
}