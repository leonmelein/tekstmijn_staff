<?php

/**
 * Created by PhpStorm.
 * User: leon
 * Date: 19-06-17
 * Time: 14:38
 */

class analysis extends model
{
    /*
    * Generates a page with the status and statistics
    *
    * @return page
    */
    public function overview(){
        $this->get_session();
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/analysis/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "../account/", "Analyse" => "#"]);
        $tabs = $this->tabs($this->bootstrap, ["Status" => "#status", "Statistiek" => "#statistics"], 'Status');

        //Status Table
        $data = $this->getTotalOverview();
        $columns = [
            ["Opdracht", "title"],
            ["Toegewezen", "promised"],
            ["Ingevoerd", "fullfilled"]
        ];
        $status_tbl = $this->table($this->bootstrap, $columns, $data, null, '<a href="status/%s/">%s</a>');

        //Analysis Table
        $data = $this->getAssignments();
        $columns = [
            ["Opdracht", "title"]
        ];
        $options = [
                ["<a download='' class='pull-right' href='beoordelingen/%s'><i class='glyphicon glyphicon-signal'></i> Beoordelingen</a>"],
                ["<a download='' class='pull-right' href='beoordelingslijsten/%s'><i class='glyphicon glyphicon-th-list'></i> Beoordelingslijsten</a>"],
                ["<a download='' class='pull-right' href='teksten/%s'><i class='glyphicon glyphicon-file'></i> Teksten</a>"]
        ];

        $analysis_tbl = $this->table($this->bootstrap, $columns, $data, $options);

        echo $this->templates->render("analysis::overview", [
            "title" => "Tekstmijn | Analyse",
            "page_title" => "Analyse",
            "menu" => $menu,
            "breadcrumbs" => $breadcrumbs,
            "status_tbl" => $status_tbl,
            "tabs" => $tabs,
            "analysis_tbl" => $analysis_tbl
            ]);
    }

    /*
    * Generates a page with the number of items reviewed for a specific assignment
    *
    * @param Medoo $assignment_id An assignment ID
    * @return page
    */
    public function generateStatusDetail($assignment_id){
        $this->get_session();
        $title = $this->getAssignmentName($assignment_id);

        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Analyse" => "/staff/analysis/", "Status : ".$title => "#"]);
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/analysis/", "align" => "stacked"], $_SESSION['type']);
        $tabs = $this->tabs($this->bootstrap, ["Status" => "#status", "Statistiek" => "#statistics"], 'Status');

        //Status Detail Table
        $overview = $this->getAssignmentOverview($assignment_id);
        $columns = [
            ["Naam", "StaffName"],
            ["Toegewezen", "Promised"],
            ["Ingevoerd", "Fullfilled"]
        ];
        $table = $this->table($this->bootstrap, $columns, $overview);

        //Analysis Table
        $data = $this->getAssignments();
        $columns = [
            ["Opdracht", "title"]
        ];
        $options = [
            ["<a download='' class='pull-right' href='../../beoordelingen/%s'><i class='glyphicon glyphicon-signal'></i> Beoordelingen</a>"],
            ["<a download='' class='pull-right' href='../../beoordelingslijsten/%s'><i class='glyphicon glyphicon-th-list'></i> Beoordelingslijsten</a>"],
            ["<a download='' class='pull-right' href='../../teksten/%s'><i class='glyphicon glyphicon-file'></i> Teksten</a>"]
        ];

        $analysis_tbl = $this->table($this->bootstrap, $columns, $data, $options);

        echo $this->templates->render("analysis::statusdetail", [
            "title" => "Tekstmijn | Analyse",
            "page_title" => "Analyse",
            "page_subtitle" => $title,
            "menu" => $menu,
            "tabs" => $tabs,
            "breadcrumbs" => $breadcrumbs,
            "status_tbl_detail" => $table,
            "analysis_tbl" => $analysis_tbl
        ]);
    }

    /*
    * Generates a csv file with the reviews of a specific assignement
    * @param $assignment_id An assignment id
    * @return csv
    */
    public function downloadBeoordelingen($assignment_id){
        $assignment_name = $this->getAssignmentName($assignment_id);

        //Get reviews from database
        //Based on Python Program export_grades.py
        $data = $this->downloadReviews($assignment_id);
        $grades = Array();
        $students = Array();
        $beoordelaars = Array();

        //Save grades of students in $grades
        //Save names of students in $students
        foreach ($data as $key => $value) {
            $student_id = $value['student_id'];
            $beoordelaar = $value['staff_name'];
            array_push($beoordelaars, $beoordelaar);
            $student_name = $value['student_name'];
            $grade = $value['grade'];

            if (array_key_exists($student_id, $grades) == False){
                $grades[$student_id] = Array($beoordelaar => $grade);
            }
            else {
                $grades[$student_id][$beoordelaar] = $grade;
            }
            if (array_key_exists($student_id, $students) == False){
                $students[$student_id] = $student_name;
            }
        }

        //Remove empty items
        $items_to_remove = Array();
        foreach($grades as $student_id => $grading) {
            $grading = array_filter($grading);
            if (empty($grading)) {
                array_push($items_to_remove, $student_id);
            }
        }
        foreach($items_to_remove as $index => $student_id) {
            unset($grades[$student_id]);
        }

        //Create a list of beoordelaars
        $beoordelaars = array_filter(array_unique($beoordelaars));
        sort($beoordelaars);

        //Prepare csv file
        $file = "";
        $header = array_merge(Array('student_id', 'student_name'),$beoordelaars);
        $file = $file.join(';', $header)."\n";
        foreach ($grades as $student_id => $grading) {
            $export_row = Array();
            array_push($export_row, $student_id);
            array_push($export_row, $students[$student_id]);
            foreach (range(0,count($beoordelaars)-1) as $index) {
                array_push($export_row, '');
            }
            foreach ($grading as $beoordelaar => $grade) {
                $index = array_search($beoordelaar, $beoordelaars);
                $export_row[$index+2] = $grade;
            }
            $file = $file.join(';', $export_row)."\n";
        }

        //Write to csv file
        $filename = $assignment_name.date("d-m-Y_H:i:s").'.csv';
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename='.$filename.'');
        echo $file;
    }

    /*
    * Generates a csv file with the answers of the reviewerslists
    * @param $assignment_id An assignment id
    * @return csv
    */
    public function downloadBeoordelingslijsten($assignment_id){
        //Gather all data of the reviewings
        $assignment_name = $this->getAssignmentName($assignment_id);
        $reviewings = Array();
        $questions = Array();
        $data = $this->getReviewingsOfSubmission($assignment_id);
        $staff_members = Array();
        foreach ($data as $index => $value) {
            $staff_id = $value['staff_id'];
            array_push($staff_members, $staff_id);
            $submission_id = $value['submission_id'];
            $student_id = $this->getStudentID($submission_id);
            $question_id = $value['question_id'];
            array_push($questions, $question_id);
            $question_value = $value['value'];
            $reviewerlist_id = $value['reviewerlist_id'];
            $reviewings[$staff_id][$student_id][$question_id] = $question_value;
        }

        //Make questions array
        $questions = array_filter(array_unique($questions));
        sort($questions);
        $questions_name = Array();
        foreach ($questions as $index => $question_id) {
            $question_txt = $this->getQuestionTxt($question_id);
            array_push($questions_name, $question_txt);
        }

        //Create empty csv files with headers
        $files = Array();
        $headers = array_merge(Array('student_id', 'student_name'), $questions_name);
        foreach ($staff_members as $index => $staff_id) {
            $files[$staff_id] = join(";",$headers)."\n";
        }

        //Fill csv files with data
        foreach ($reviewings as $staff_id => $students) {
            foreach ($students as $student_id => $question_answer) {
                //Initialize variables
                $student_name = $this->getStudentName($student_id);
                $firstname = $student_name['firstname'];
                $prefix = $student_name['prefix'];
                $lastname = $student_name['lastname'];
                $student_name = $this->generateNameStr($firstname, $prefix, $lastname);

                //Create export row
                $export_row = Array();
                array_push($export_row, $student_id);
                array_push($export_row, $student_name);
                foreach (range(0,count($questions)-1) as $index) {
                    array_push($export_row, '');
                }
                //Save every answer
                foreach ($question_answer as $question => $answer) {
                    $index = array_search($question, $questions);
                    $export_row[$index+2] = $answer;
                }
                $result = $files[$staff_id];
                $result = $result.join(';', $export_row)."\n";
                $files[$staff_id] = $result;
            }
        }

        //Write csv files
        chdir("tmp");
        $csv_files = Array();
        foreach ($files as $staff_id => $csv) {
            //Set variables
            $staff_name = $this->getStaffName($staff_id);
            $firstname = $staff_name['firstname'];
            $prefix = $staff_name['prefix'];
            $lastname = $staff_name['lastname'];
            $staff_name = $this->generateNameStr($firstname, $prefix, $lastname);

            //Write to csv file
            $filename = "Beoordelingslijst_".$assignment_name."_".$staff_name."_".date("d-m-Y_H:i:s").'.csv';
            $tmp_file = fopen($filename, "w");
            fwrite($tmp_file, $csv);
            fclose($tmp_file);
            array_push($csv_files, $filename);
        }

        //Zip csv files and download
        $filename = "Beoordelingslijst_".$assignment_name."_".date("d-m-Y_H:i:s").'.zip';
        $zip = \Comodojo\Zip\Zip::create($filename);
        $zip->add($csv_files);
        $zip->close();

        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
        unlink($filename);
        foreach ($csv_files as $index => $filename) {
            unlink($filename);
        }
    }


    /*
    * Generates a csv file with the texts of the assignements
    * @param $assignment_id An assignment id
    * @return
    */
    public function downloadTeksten($assignment_id){
        //Get texts from database
        $assignment_name = $this->getAssignmentName($assignment_id);
        $texts = $this->getTexts($assignment_id);

        //Create empty csv files with headers
        $file = "";
        $header = Array('student_id', 'student_name', 'text');
        $file = $file.join(';', $header)."\n";

        //Loop through all texts
        foreach ($texts as $text => $attributes) {
            $export_row = Array();

            //Push student_id
            array_push($export_row, $attributes['student_id']);

            //Set variables
            $student_name = $this->getStudentName($attributes['student_id']);
            $firstname = $student_name['firstname'];
            $prefix = $student_name['prefix'];
            $lastname = $student_name['lastname'];
            $student_name = $this->generateNameStr($firstname, $prefix, $lastname);

            //Push student name
            array_push($export_row, $student_name);

            //Push grade
            $individual_text =  $attributes['text'];
            $individual_text = str_replace('"',"'",$individual_text);
            $individual_text =  '"'.$individual_text.'"';
            array_push($export_row, $individual_text);

            //Write to csv
            $file = $file.join(';', $export_row)."\n";
        }

        //Download csv file
        $filename = $assignment_name.date("d-m-Y_H:i:s").'.csv';
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename='.$filename.'');
        echo $file;

    }


    /*
    * Returns an array with all the reviews of an speciffic assignment
    * @param Medoo $database A database instance passed as an Medoo object.
    * @param $assignment_id An assignment id
    * @return Array
    */
    private function getTexts($assignement_id){
        $quoted_assignement_id = $this->database->quote($assignement_id);
        $query = "
                    SELECT student_id, text from submissions WHERE assignment_id = $quoted_assignement_id
           ";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
    * Returns an array with all the reviews of an speciffic assignment
    * @param Medoo $database A database instance passed as an Medoo object.
    * @param $assignment_id An assignment id
    * @return Array
    */
    private function downloadReviews($assignement_id){
        $quoted_assignement_id = $this->database->quote($assignement_id);
        $query = "
                    SELECT `student_id`, CONCAT_WS(' ', `s_firstname`, `s_prefix`, `s_lastname`) as `staff_name`, CONCAT_WS(' ', `firstname`, `prefix`, `lastname`) as `student_name`, `grade` FROM
                    (
                        SELECT `student_id`, `staff_id`, `firstname`, `prefix`, `lastname`, `grade` FROM
                        (
                            SELECT `student_id_original` as `student_id`, `firstname`, `prefix`, `lastname`, `submission_id` FROM
                                (
                                    SELECT `id` as `student_id_original`, `firstname`, `prefix`, `lastname` FROM `students` WHERE `id` IN
                                        (
                                            SELECT `student_id` FROM `submissions` WHERE `assignment_id` = $quoted_assignement_id
                                        ) 
                                ) `students_ass`
                    
                            JOIN
                    
                                (
                                    SELECT `id` as `submission_id`, `student_id` FROM `submissions` WHERE `assignment_id` = $quoted_assignement_id
                                ) `submissions_ass`
                    
                            ON `students_ass`.`student_id_original` = `submissions_ass`.`student_id`
                        ) `students_submissions`
                    
                        LEFT JOIN
                    
                            (
                                SELECT `staff_id`, `submission_id`, `grade`, `notes` FROM `grading`
                            ) `submissions_grading`
                    
                        ON `students_submissions`.`submission_id` = `submissions_grading`.`submission_id`
                        ORDER BY `student_id`
                        LIMIT 999999
                    ) `without_staff_name`
                    
                    LEFT JOIN
                    
                        (
                            SELECT `id`, `firstname` as `s_firstname`, `prefix` as `s_prefix`, `lastname` as `s_lastname` FROM `staff`
                        ) `staff_info`
                    
                    ON `staff_info`.`id` = `without_staff_name`.`staff_id`
                    LIMIT 999999
                    ";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
    * Returns an string with the formatted name
    * @param Medoo $database A database instance passed as an Medoo object.
    * @param $firstname
    * @param $prefix
    * @param $lastname
    * @return Str
    */
    private function generateNameStr($firstname, $prefix, $lastname){
        if (isset($prefix)){
            return sprintf("%s %s %s", $firstname, $prefix, $lastname);
        } else {
            return sprintf("%s %s", $firstname, $lastname);
        }
    }

    /*
    * Returns the question txt of a question id
    * @param Medoo $database A database instance passed as an Medoo object.
    * @param $question_id An question id
    * @param $questionnaire_id An questionnaire_id id
    * @return String
    */
    private function getQuestionTxt($question_id) {
        $quoted_question_id = $this->database->quote($question_id);
        $query = "SELECT label FROM reviewerlistsquestions
                  WHERE id = $quoted_question_id";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['label'];
    }

    /*
    * Returns the firstname, prefix and lastname of a student
    * @param Medoo $database A database instance passed as an Medoo object.
    * @param $student_id An assignment id
    * @return Array
    */
    private function getStudentName($student_id) {
        $quoted_student_id = $this->database->quote($student_id);
        $query = "SELECT firstname, prefix, lastname FROM students
                  WHERE id = $quoted_student_id";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    /*
    * Returns the firstname, prefix and lastname of a staff member
    * @param Medoo $database A database instance passed as an Medoo object.
    * @param $staff_id An assignment id
    * @return Array
    */
    private function getStaffName($staff_id) {
        $quoted_staff_id = $this->database->quote($staff_id);
        $query = "SELECT firstname, prefix, lastname FROM staff
                  WHERE id = $quoted_staff_id";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    /*
    * Returns an array with all the reviewings of the submissions of a specific assignment
    * @param Medoo $database A database instance passed as an Medoo object.
    * @param $assignment_id An assignment id
    * @return Array
    */
    private function getReviewingsOfSubmission($assignement_id) {
        $quoted_assignement_id = $this->database->quote($assignement_id);
        $query = "SELECT * FROM reviewing
                  WHERE reviewerlist_id IN (
                  SELECT id FROM reviewerlists WHERE assignment_id = $quoted_assignement_id
                  )";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
     * Gets the id of a student based on a submissions id.
     *
     * @param Medoo $database A database instance passed as an Medoo object.
     * @param $submission_id An submissions id
     * @return Array, associative
     */
    private function getStudentID($submission_id){
        $quoted_submission_id = $this->database->quote($submission_id);
        $query = "SELECT student_id FROM submissions
                  WHERE id = $quoted_submission_id";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['student_id'];
    }

    /*
    * Gets an overview of the assignments in the system and the number of texts that are reviewed by the reviewers
    *
    * @param Medoo $database A database instance passed as an Medoo object.
    * @return Array, associative
    */
    private function getTotalOverview(){
        $query = "SELECT promised_grades.assignment_id AS id, title, promised, fullfilled
                FROM (
                      SELECT assignments.id AS assignment_id, assignments.title, COUNT(submission_id) AS promised
                      FROM submissions_staff, submissions, assignments
                      WHERE submissions_staff.submission_id = submissions.id
                      AND submissions.assignment_id = assignments.id
                      AND staff_id NOT IN (1,24)
                      GROUP BY assignments.id
                ) AS promised_grades, (
                                SELECT assignment_id, COUNT(grading.grade) AS fullfilled
                                FROM grading, submissions
                                WHERE grading.submission_id = submissions.id
                                AND grading.staff_id NOT IN (1,24)
                                AND grading.notes = ''
                                GROUP BY assignment_id
                ) AS fullfilled_grades
                  WHERE promised_grades.assignment_id = fullfilled_grades.assignment_id
                ORDER BY title";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
    * Gets an overview of the number of reviews made by the reviewers
    *
    * @param Medoo $database A database instance passed as an Medoo object.
    * @param $id An assignment id
    * @return Array, associative
    */
    private function getAssignmentOverview($assignment_id){
        $quoted_assignment_id = $this->database->quote($assignment_id);
        $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) AS StaffName, PromisedGrades.Promised, FullfilledGrades.Fullfilled
                FROM (SELECT grading.staff_id, COUNT(grading.grade) as Fullfilled
                      FROM grading
                      WHERE grading.submission_id IN (
                        SELECT id
                        FROM submissions
                        WHERE assignment_id = $quoted_assignment_id
                      ) AND grading.notes = ''
                      AND grading.staff_id NOT IN (1,24)
                      GROUP BY staff_id) AS FullfilledGrades,
                  (SELECT submissions_staff.staff_id, COUNT(submissions_staff.submission_id) as Promised
                   FROM submissions_staff
                   WHERE submissions_staff.submission_id IN (
                     SELECT id
                     FROM submissions
                     WHERE assignment_id = $quoted_assignment_id
                   ) AND staff_id NOT IN (1, 24)
                   GROUP BY staff_id) AS PromisedGrades,
                   staff
                WHERE PromisedGrades.staff_id = FullfilledGrades.staff_id
                AND staff.id = FullfilledGrades.staff_id
                ORDER BY StaffName
                ";

        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
     * Gets the name of an assignment by selecting an assignment with a specific ID.
     *
     * @param Medoo $database A database instance passed as an Medoo object.
     * @param $id An assignment id
     * @return Array, associative
     */
    private function getAssignmentName($id){
        return $this->database->get(
            "assignments",
            "title",
            ["id" => $id]
        );
    }

    /*
     * Gets an array of assignments in the system.
     *
     * @param Medoo $database A database instance passed as an Medoo object.
     * @return Array, associative
     */
    private function getAssignments()
    {
        $query = "SELECT * FROM assignments ORDER BY title";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
}