<?php

/**
 * Created by PhpStorm.
 * User: leon
 * Date: 21-06-17
 * Time: 11:54
 */
class review extends submissions
{
    /*
     * Routing functions
     */

    public function overview(){
        $this->get_session();
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/review/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Beoordelen" => "/review/"]);

        $students = $this->getAssignment($_SESSION['staff_id']);
        $columns = [
            ["Titel", "title"]
        ];
        $options = [
            ["<a class='pull-right' href='%s/'><i class='glyphicon glyphicon-pencil'></i> Beoordelen</a>"],
            ["<a download class='pull-right' href='%s/download'><i class='glyphicon glyphicon-download-alt'></i> Download beoordelingspakket</a>"],
        ];

        $table = $this->table($this->bootstrap, $columns, $students, $options, '<a href="%s/">%s</a>');
        echo $this->templates->render("submissions::classes", ["title" => "Tekstmijn | Beoordelen",
            "page_title" => "Beoordelen", "page_subtitle" => $_SESSION["staff_name"],  "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table" => $table]);
    }

    public function assignment($assignmentid){
        $this->get_session();
        $staff_id = $_SESSION['staff_id'];

        $title = $this->getAssignmentName($assignmentid);
        $tabs = $this->tabs($this->bootstrap, ["Individueel beoordelen" => "#ingeleverd", "Beoordelen in tabel" => "#beoordelen"], 'Individueel beoordelen');
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/review/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Beoordelen" => "/staff/review/", $title => "#"]);

        $students_ingeleverd = $this->getSubmissions($assignmentid, $staff_id);
        $columns = [
            ["Leerlingnummer", "student_id"],
            ["Naam", "name"],
            ["Inleverdatum", "submission_date"],
            ["Aantal pogingen", "submission_count"],
        ];
        $table_ingeleverd = $this->table($this->bootstrap, $columns, $students_ingeleverd, null, '<a href="%s/">%s</a>');
        $gradingtable = $this->generateGradingTable($students_ingeleverd, $staff_id, "", $assignmentid);

        $page_js = "/staff/vendor/application/add_pencil.js";

        echo $this->templates->render(
            "submissions::review",
            [
                "title" => "Tekstmijn | Beoordelen",
                "page_title" => "Beoordelen",
                "page_subtitle" => $title,
                "menu" => $menu,
                "breadcrumbs" => $breadcrumbs,
                "table_ingeleverd" => $table_ingeleverd,
                "students_ingeleverd" => $students_ingeleverd,
                "gradingarray" => $this->generateGradingArray($students_ingeleverd),
                "gradingtable" => $gradingtable,
                "tabs" => $tabs,
                "page_js" => $page_js,
                "assignment_id" => $assignmentid,
                "staff_id" => $staff_id
            ]
        );
    }

    public function submission($assignmentid, $submissionid){
        session_start("staff");

        $assignment_name = $this->getAssignmentName($assignmentid);
        $student_name = $this->getStudentName($submissionid);
        $subtitle = sprintf("%s : %s", $assignment_name, $student_name);
        $title = "Beoordelen";
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/review/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Beoordelen" => "/staff/review/", $assignment_name => "/staff/review/$assignmentid", "Beoordeel inzending" => "#"]);

        $tabs = "";
        if ($_SESSION['type'] == 1) {$tabs = $this->tabs($this->bootstrap, ["Lezen en beoordelen" => "#beoordelen"], 'Lezen en beoordelen');}
        elseif ($_SESSION['type'] == 2) {$tabs = $this->tabs($this->bootstrap, ["Lezen en beoordelen" => "#beoordelen", "Beoordelingslijst" => "#beoordelingslijst", "Qualtrics" => "http://rug.eu.qualtrics.com/jfe/form/SV_6tCfini6XjL53md"], 'Lezen en beoordelen');}

        $submission_info = $this->getSubmissionInfo($submissionid);
        $page_js = "/staff/vendor/application/add_field.js";

        $staff_id = $_SESSION['staff_id'];
        $current_grades= $this->getIndividualGrades($staff_id, $submissionid, ["Score"]);
        $questionnaire = $this->getQuestionnaire($assignmentid);

        echo $this->templates->render(
            "review::grading",
            [
                "title" => "Tekstmijn | Beoordelen",
                "page_title" => $title, "page_subtitle" => $subtitle, "menu" => $menu, "breadcrumbs" => $breadcrumbs,
                "assignment_id" => $assignmentid,
                "submission_id" => $submissionid,
                "staff_id" => $_SESSION['staff_id'],
                "page_js" => $page_js,
                "submission_date" => $submission_info["submission_date"],
                "submission_file" => $submission_info["submission_file"],
                "submission_count" => $submission_info["submission_count"],
                "submission_originalfile" => $submission_info["original_file"],
                "text" => $submission_info["text"],
                "current_grades" => $current_grades,
                "tabs" => $tabs,
                "user_type" => $_SESSION['type'],
                "form" => $this->generateQuestionnaire($assignmentid, $staff_id, $submissionid),
                "questionnaire" => $questionnaire
            ]
        );
    }

    public function questionnaire(){
        $this->get_session();
        $staff_id = $_POST['staff_id'];
        $submission_id = $_POST['submission_id'];
        $reviewerlist_id = $_POST['reviewerlist_id'];
        $saved_data = $_POST;
        unset($saved_data['staff_id']);
        unset($saved_data['submission_id']);
        unset($saved_data['reviewerlist_id']);

        $result = $this->saveQuestionnaire($saved_data, $staff_id, $submission_id, $reviewerlist_id);
        if ($result){
            getRedirect("../?success=true");
        } else {
            getRedirect("../?success=false");
        }
    }

    public function downloadSubmissions($assignmentid){
        $this->get_session();
        $staff_id = $_SESSION['staff_id'];
        $files = $this->gatherSubmissionFiles($staff_id, $assignmentid);
        $filename_vars = $this->gatherNames($staff_id, $assignmentid);
        chdir("tmp");
        $filename = sprintf("Beoordelingspakket - %s - %s.zip", $filename_vars['fullname'], $filename_vars['assignment_name']);
        $zip = \Comodojo\Zip\Zip::create($filename);
        foreach ($files as $file){
            $zip->add("../assets/submissions/".$file['file'], $file['original_file']);
        }
        $zip->close();

        $is_valid = false;
        try {
            $is_valid = \Comodojo\Zip\Zip::check($filename);
        } catch (\Comodojo\Exception\ZipException $exception){
            $this->redirect('../../?download_generated=false');
        }

        if ($is_valid) {
            header("Content-Description: File Transfer");
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=$filename");
            readfile($filename);
            unlink($filename);
        } else {
            $this->redirect('../../?download_generated=false');
        }

    }

    /*
     * Supporting functions
     */

    function getAssignment($staff){
        $quoted_staff_id = $this->database->quote($staff);
        $query = "SELECT DISTINCT assignments.id, assignments.title
                    FROM assignments, allocations
                    WHERE assignments.id in (
                      SELECT DISTINCT assignment_id
                      FROM allocations
                      WHERE staff_id = $quoted_staff_id
                    )
                    ORDER BY title ASC";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    function getSubmissions($assignment, $staff){
        $quoted_assignment_id = $this->database->quote($assignment);
        $quoted_staff_id = $this->database->quote($staff);
        $query = "SELECT submissions.id, submissions.student_id, submissions.assignment_id,
                      CONCAT_WS(' ',students.firstname, students.prefix, students.lastname) as name,
                      DATE_FORMAT(submissions.time, '%d %M %Y, %H:%i') as submission_date,
                      submissions.submission_count as submission_count
                    FROM submissions, students
                    WHERE submissions.assignment_id = $quoted_assignment_id
                    AND submissions.student_id = students.id
                    AND submissions.student_id IN (SELECT student_id
                                                   FROM allocations
                                                   WHERE assignment_id = $quoted_assignment_id
                                                         AND staff_id = $quoted_staff_id)";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    function getQuestionnaire($assignment_id){
        return $this->database->get("reviewerlist", "qualtrics_url", ["assignment_id" => $assignment_id]);
    }

    function generateQuestionnaire($assignment_id, $staff_id, $submission_id) {
        ob_start();
        $staff_id_quoted = $this->database->quote($staff_id);
        $submission_id_quoted = $this->database->quote($submission_id);
        $assignment_id_quoted = $this->database->quote($assignment_id);
        $query = "SELECT id, name, action, method
                FROM reviewerlists
                WHERE reviewerlists.assignment_id = $assignment_id_quoted";
        $questionnaire = $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];

        $questionnaire_id_quoted = $this->database->quote($questionnaire['id']);
        $query = "SELECT id, elementtype, label
                FROM reviewerlistsquestions
                WHERE reviewerlistsquestions.reviewerlists_id = $questionnaire_id_quoted";
        $questions = $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);

        echo "<h4>".$questionnaire['name']."</h4>";
        Form::open ($questionnaire['id'], $values = NULL, $attributes = Array("method" => $questionnaire['method'], "action" => $questionnaire['action']));
        Form::Hidden ("submission_id", $values = $submission_id, $attributes = NULL);
        Form::Hidden ("staff_id", $values = $staff_id, $attributes = NULL);
        Form::Hidden ("reviewerlist_id", $values = $questionnaire['id'], $attributes = NULL);
        foreach ($questions as $id => $value) {
            $elementtype = $value['elementtype'];
            $id = $value['id'];
            $id_quoted = $this->database->quote($id);
            $label = $value['label'];

            // Get attributes
            $query = "SELECT attribute_key, attribute_value
                FROM reviewerlistsquestions_attributes
                WHERE reviewerlistsquestions_attributes.reviewerlistsquestions_id = $id_quoted";
            $attributes_db = $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
            $attributes_local = Array();
            if (!empty($attributes_db)){
                foreach ($attributes_db as $key => $value){
                    $attributes_local[$value['attribute_key']] = $value['attribute_value'];
                }
            }

            // Get options
            $query = "SELECT option_key, option_value
                FROM reviewerlistsquestions_options
                WHERE reviewerlistsquestions_options.reviewerlistsquestions_id = $id_quoted";
            $options_db = $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
            $options_local = Array();
            if (!empty($options_db)){
                foreach ($options_db as $key => $value){
                    $options_local[$value['option_value']] = $value['option_key'];
                }
            }

            // Get saved values
            $query = "SELECT value
                FROM reviewing
                WHERE reviewing.staff_id = $staff_id_quoted
                AND reviewing.submission_id = $submission_id_quoted
                AND reviewing.question_id = $id_quoted";
            $values_db = $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['value'];
            if ($values_db != ""){
                $attributes_local['value'] = $values_db;
            }

            if ($elementtype == 'title'){
                echo "</br>";
                echo "<h5>".$label."</h5>";
            }
            elseif ( in_array($elementtype, Array('YesNo', 'Number')) ){
                Form::$elementtype ($label, $id, $attributes_local);
            }
            else{
                Form::$elementtype ($label, $id, $options_local, $attributes_local);
            }
        }
        Form::Button ("Opslaan");
        Form::close (false);
        $form =  ob_get_contents();
        ob_end_clean();
        return $form;
    }

    function saveQuestionnaire($values, $staff_id, $submission_id, $reviewerlist_id) {
        $this->database->delete("reviewing", [
            "AND" => [
                "staff_id" => $staff_id,
                "submission_id" => $submission_id,
                "reviewerlist_id" => $reviewerlist_id
            ]
        ]);
        foreach($values as $key => $value){
            $this->database->insert("reviewing", [
                "staff_id" => $staff_id,
                "submission_id" => $submission_id,
                "reviewerlist_id" => $reviewerlist_id,
                "question_id" => $key,
                "value" => $value
            ]);
        }

        $result = 1;
        return $result;
    }

    function gatherSubmissionFiles($staffid, $assignmentid){
        $quoted_staffid = $this->database->quote($staffid);
        $quoted_assignmentid = $this->database->quote($assignmentid);
        $query = "SELECT submissions.file, submissions.original_file
                    FROM submissions, students
                    WHERE submissions.assignment_id = $quoted_assignmentid
                    AND submissions.student_id = students.id
                    AND submissions.student_id IN (SELECT student_id
                                                   FROM allocations
                                                   WHERE assignment_id = $quoted_assignmentid
                                                         AND staff_id = $quoted_staffid);";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    function gatherNames($staffid, $assignmentid){
        $quoted_staffid = $this->database->quote($staffid);
        $quoted_assignemntid = $this->database->quote($assignmentid);
        $query = "SELECT CONCAT(firstname,prefix,lastname) as fullname, title as assignment_name
                FROM staff, assignments
                WHERE staff.id = $quoted_staffid
                AND assignments.id = $quoted_assignemntid";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
    }
}