<?php

/**
 * Analysis
 *
 * Provides insight into the amount of grades entered per assignment and per reviewer and provides exports of
 * grades, element scores and texts for further analysis.
 */

class analysis extends model
{
    /**
     * Generates a page with the status and statistics
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
                ["<a download='' class='pull-right' href='gradings/%s'><i class='glyphicon glyphicon-signal'></i> Beoordelingen</a>"],
                ["<a download='' class='pull-right' href='elementscores/%s'><i class='glyphicon glyphicon-th-list'></i> Beoordelingslijsten</a>"],
                ["<a download='' class='pull-right' href='texts/%s'><i class='glyphicon glyphicon-file'></i> Teksten</a>"]
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

    /**
     * Generates a page with the number of items reviewed for a specific assignment
     *
     * @param $assignment_id int containing the assignment's ID
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

    /**
     * Generates a csv file with the reviews of a specific assignment
     *
     * @param $assignment_id int containing the assignment id
     */
    public function downloadGradings($assignment_id){
        $assignment_name = $this->getAssignmentName($assignment_id);

        //Get reviews from database
        //Based on Python Program export_grades.py
        $data = $this->getReviews($assignment_id);
        $grades = Array();
        $students = Array();
        $beoordelaars = Array();

        //Save grades of students in $grades
        //Save names of students in $students
        foreach ($data as $key => $value) {
            $student_id = $value['student_id'];
            $beoordelaar = $value['staff_name'];
            $beoordelaar_id = $value['staff_id'];
            $beoordelaars[$beoordelaar_id] = $beoordelaar;
            $student_name = $value['student_name'];
            $grade = $value['grade'];

            if (array_key_exists($student_id, $grades) == False){
                $grades[$student_id] = Array($beoordelaar_id => $grade);
            }
            else {
                $grades[$student_id][$beoordelaar_id] = $grade;
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
        $beoordelaars_id = Array();
        foreach ($beoordelaars as $staff_id => $staff_name) {
            array_push($beoordelaars_id, $staff_id);
        }

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
                $index = array_search($beoordelaar, $beoordelaars_id);
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

    /**
     * Generates a csv file with the element scores per submissions
     *
     * @param $assignment_id Int containing the assignment's ID
     */
    public function downloadElementScores($assignment_id){
        //Gather all data of the gradings
        $assignment_name = $this->getAssignmentName($assignment_id);
        $gradings = Array();
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
            $gradings[$staff_id][$student_id][$question_id] = $question_value;
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
        foreach ($gradings as $staff_id => $students) {
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


    /**
     * Generates a csv file with the texts of the assignments
     *
     * @param $assignment_id int containing the assignment's ID
     */
    public function downloadTexts($assignment_id){
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
     * Supporting functions
     */

    /**
    * Gets array with all the texts submitted for an specific assignment
    *
    * @param $assignment_id String containing the assignment's UUID
    * @return array containing the student id and text of each submission for the assignment
    */
    private function getTexts($assignment_id){
        $quoted_assignment_id = $this->database->quote($assignment_id);
        $query = "SELECT student_id, text from submissions WHERE assignment_id = $quoted_assignment_id";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets array with all the reviews of an specific assignment
     *
     * @param $assignment_id Int containing the assignment's ID
     * @return array containing the reviews per submission
    */
    private function getReviews($assignment_id){
        $quoted_assignement_id = $this->database->quote($assignment_id);
        $query = "
                    SELECT `student_id`, `staff_id`, CONCAT_WS(' ', `s_firstname`, `s_prefix`, `s_lastname`) as `staff_name`, CONCAT_WS(' ', `firstname`, `prefix`, `lastname`) as `student_name`, `grade` FROM
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

    /**
     * Returns an string with the formatted name.
     *
     * @param $firstname string containing the user's first name
     * @param $prefix string containing the user's prefix, if there is one
     * @param $lastname string containing the user's last name
     * @return string containing the concatenated, full name
     */
    private function generateNameStr($firstname, $prefix, $lastname){
        if (isset($prefix)){
            return sprintf("%s %s %s", $firstname, $prefix, $lastname);
        } else {
            return sprintf("%s %s", $firstname, $lastname);
        }
    }

    /**
     * Returns the question string beloning to a question ID.
     *
     * @param $question_id int containing the question ID
     * @return string containing the question
     */
    private function getQuestionTxt($question_id) {
        $quoted_question_id = $this->database->quote($question_id);
        $query = "SELECT label FROM reviewerlistsquestions
                  WHERE id = $quoted_question_id";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['label'];
    }

    /**
     * Returns the firstname, prefix and lastname of a student.
     *
     * @param $student_id int containing the student ID
     * @return array containing the student's first and last name, including a possible prefix
     */
    private function getStudentName($student_id) {
        $quoted_student_id = $this->database->quote($student_id);
        $query = "SELECT firstname, prefix, lastname FROM students
                  WHERE id = $quoted_student_id";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    /**
     * Returns the firstname, prefix and lastname of a staff member.
     *
     * @param $staff_id int containing the staff member's ID
     * @return array containing the staff member's first and last name, including a possible prefix
     */
    private function getStaffName($staff_id) {
        $quoted_staff_id = $this->database->quote($staff_id);
        $query = "SELECT firstname, prefix, lastname FROM staff
                  WHERE id = $quoted_staff_id";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    /**
     * Returns an array with all the element gradings of the submissions of a specific assignment.
     *
     * @param $assignment_id int containing the assignment's ID
     * @return array containing all element grades per reviewer per submission
     */
    private function getReviewingsOfSubmission($assignment_id) {
        $quoted_assignement_id = $this->database->quote($assignment_id);
        $query = "SELECT * FROM reviewing
                  WHERE reviewerlist_id IN (
                  SELECT id FROM reviewerlists WHERE assignment_id = $quoted_assignement_id
                  )";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets the student's ID belonging to a particular submission ID.
     *
     * @param $submission_id int containing the submission ID
     * @return string containing the student ID
     */
    private function getStudentID($submission_id){
        $quoted_submission_id = $this->database->quote($submission_id);
        $query = "SELECT student_id FROM submissions
                  WHERE id = $quoted_submission_id";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['student_id'];
    }

    /**
     * Gets an overview of the assignments in the system and the number of texts that are reviewed by the reviewers
     *
     * @return array containing all assignments with their respective number of promised and fullfilled grades
     */
    private function getTotalOverview(){
        $query = "SELECT promised_grades.assignment_id AS id, title, promised, fullfilled
                    FROM (
                           SELECT assignment_id, title, COUNT(*) as promised
                           FROM allocations, staff, assignments
                           WHERE allocations.staff_id = staff.id
                                 AND staff.type != 2
                                 AND allocations.assignment_id = assignments.id
                           GROUP BY assignment_id
                           ORDER BY title ASC) AS promised_grades,
                      (SELECT assignment_id, COUNT(grading.grade) AS fullfilled
                       FROM grading, submissions, staff
                       WHERE grading.submission_id = submissions.id
                             AND grading.staff_id = staff.id
                             AND staff.type != 2
                             AND grading.type = 'Score'
                       GROUP BY assignment_id) AS fullfilled_grades
                      WHERE promised_grades.assignment_id = fullfilled_grades.assignment_id";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets an overview of the number of reviews made by the reviewers
     *
     * @param $assignment_id int containing the assignment ID
     * @return array containing all reviewers with the # of assigned submissions and the # they've already graded
     */
    private function getAssignmentOverview($assignment_id){
        $quoted_assignment_id = $this->database->quote($assignment_id);
        $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) AS StaffName, Promised, Fullfilled
                    FROM (SELECT staff_id, COUNT(*) as promised
                          FROM allocations, staff
                          WHERE assignment_id = $quoted_assignment_id
                                AND allocations.staff_id = staff.id
                                AND staff.type != 2
                          GROUP BY allocations.staff_id) AS promised_grades,
                        (SELECT staff_id, COUNT(grading.grade) as fullfilled
                         FROM grading, staff
                         WHERE grading.submission_id IN (
                          SELECT id
                          FROM submissions
                          WHERE assignment_id = $quoted_assignment_id
                         )
                         AND grading.staff_id = staff.id
                         AND staff.type != 2
                         AND grading.type = 'Score'
                         GROUP BY staff_id) AS fullfilled_grades,
                        staff
                    WHERE promised_grades.staff_id = fullfilled_grades.staff_id
                    AND promised_grades.staff_id = staff.id
                    ORDER BY StaffName ASC";

        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets the name of an assignment using it's corresponding ID.
     *
     * @param $id int containing the assignment's ID
     * @return array containing the assignment title
     */
    private function getAssignmentName($id){
        return $this->database->get(
            "assignments",
            "title",
            ["id" => $id]
        );
    }

    /**
     * Gets an array of assignments in the system.
     *
     * @return array of assignments, including title and ID
     */
    private function getAssignments()
    {
        $query = "SELECT * FROM assignments ORDER BY title";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
}