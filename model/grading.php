<?php

/**
 * Created by PhpStorm.
 * User: leon
 * Date: 21-06-17
 * Time: 13:19
 */
class grading extends model
{

    public function setIndividualGrade(){
        $this->get_session();
        $staff_id = $_SESSION['staff_id'];
        $submission_id = $_POST["submission_id"];
        $grading_name = $_POST["grading_name"];
        $grading_grade = $_POST["grading_grade"];
        $grading_notes = $_POST["grade_Opmerkingen"];

        $result = $this->insertGrades($staff_id, $submission_id, $grading_name, $grading_grade, $grading_notes);
        if ($result){
            getRedirect("../?success=true");
        } else {
            getRedirect("../?success=false");
        }
    }

    /*
     * Supporting functions
     */

    function insertGrades($staff_id, $submission_id, $types, $grades, $grading_notes){
        $this->database->delete("grading", [
            "AND" => [
                "staff_id" => $staff_id,
                "submission_id" => $submission_id
            ]
        ]);
        foreach($types as $index => $type) {
            $grade = str_replace(",",".", $grades[$index]);
            $this->database->insert("grading", [
                "staff_id" => $staff_id,
                "submission_id" => $submission_id,
                "type" => $type,
                "grade" => $grade
            ]);
        }
        if ($grading_notes != "") {
            $this->database->insert("grading", [
                "staff_id" => $staff_id,
                "submission_id" => $submission_id,
                "type" => "Notes",
                "notes" => $grading_notes
            ]);
        }
        return 1;
    }


}