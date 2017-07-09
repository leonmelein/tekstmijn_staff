<?php
/**
 * Grading
 *
 * Assists grading of assignments in a reusable fashion.
 */
class grading extends model
{

    /**
     * Provides a POST interface to save a grade for a submission to the system.
     */
    public function setIndividualGrade(){
        $this->get_session();
        $staff_id = $_SESSION['staff_id'];
        $submission_id = $_POST["submission_id"];
        $grading_name = $_POST["grading_name"];
        $grading_grade = $_POST["grading_grade"];
        $grading_notes = $_POST["grade_Opmerkingen"];

        $result = $this->insertGrades($staff_id, $submission_id, $grading_name, $grading_grade, $grading_notes);
        if ($result){
            $this->redirect("../?success=true");
        } else {
            $this->redirect("../?success=false");
        }
    }

    /*
     * Supporting functions
     */

    /**
     * Inserts a provided grading for a given assignment into the system's database.
     *
     * @param $staff_id int containing the staff member's ID
     * @param $submission_id int containing the submission ID
     * @param $types array containing the types of grades given
     * @param $grades array containing the grades themselves
     * @param $grading_notes string containing any notes given with regard to the submissions
     * @return int indicating if the operation succeeded
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