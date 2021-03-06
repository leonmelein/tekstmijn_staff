<?php
/**
 * Assignments
 *
 * Enables creation and modification of student assignments.
 */
class assignment extends model {

    /*
     * Page functions
     */

    /**
     * Displays an overview of all assigments.
     */
    function overview(){
        $this->get_session();
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Opdrachten" => "#"]);
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/assignment/", "align" => "stacked"], $_SESSION['type']);
        $students = $this->getAssignments();
        $columns = [
            ["Titel", "title"]
        ];
        $options = [
            ["<a download class='pull-right' href='%s/download'><i class='glyphicon glyphicon-download-alt'></i> Download beoordelingspakketten</a>"],
            ["<a class='pull-right' href='%s/'><i class='glyphicon glyphicon-pencil'></i> Bewerken</a>"],
        ];

        $table = $this->table($this->bootstrap, $columns, $students, $options, '<a href="%s/">%s</a>');
        echo $this->templates->render("assignment::overview", ["title" => "Tekstmijn | Opdrachten",
            "page_title" => "Opdrachten",
            "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table" => $table
        ]);
    }

    /**
     * Displays an individual assignment.
     *
     * @param $assignmentid string containing the assignment ID
     */
    function individualAssignment($assignmentid){
        $this->get_session();
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Opdrachten" => "/staff/assignment", "Bewerk opdracht" => "#"]);
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/assignment/", "align" => "stacked"], $_SESSION['type']);

        $assignment = $this->getAssignment($assignmentid);
        $classes = $this->options_selected($this->getSchoolsAndClasses(),$this->getCoupledClasses($assignmentid));
        $reviewers = $this->options_selected($this->getReviewers(),$this->getCoupledReviewers($assignmentid));
        $qualtrics_url = $this->getQualtricsURL($assignmentid);

        echo $this->templates->render("assignment::edit", ["title" => "Tekstmijn | Opdrachten",
            "page_title" => "Nieuwe opdracht",
            "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "classes" => $classes, "reviewers" => $reviewers,
            "assignment" => $assignment,
            "page_js" => "/staff/vendor/application/load_date_picker.js",
            "qualtrics_url" => $qualtrics_url
        ]);
    }

    /**
     * Provides a zip download of all texts per reviewer.
     */
    function downloadSubmissions($assignmentid){
        $this->get_session();

        $reviewers = $this->getReviewersforAssignment($assignmentid);
        $zips = Array();
        chdir("tmp");

        foreach ($reviewers as $index => $staff_id) {
            $files = $this->gatherSubmissionFiles($staff_id, $assignmentid);
            $filename_vars = $this->gatherNames($staff_id, $assignmentid);
            $filename = sprintf("Beoordelingspakket - %s - %s.zip", $filename_vars['fullname'], $filename_vars['assignment_name']);
            $zip = \Comodojo\Zip\Zip::create($filename);
            foreach ($files as $file) {
                $zip->add("../assets/submissions/" . $file['file'], $file['original_file']);
            }
            $zip->close();
            array_push($zips, $filename);
        }

        $bestandsnaam = sprintf("Beoordelingspakketten - %s.zip", $this->gatherAssignmentName($assignmentid));
        $beoordelingspakketen = \Comodojo\Zip\Zip::create($bestandsnaam);
        foreach ($zips as $zip) {
            $beoordelingspakketen->add($zip);
        }
        $beoordelingspakketen->close();

        foreach ($zips as $zip) {
            unlink($zip);
        }

        $is_valid = false;
        try {
            $is_valid = \Comodojo\Zip\Zip::check($bestandsnaam);
        } catch (\Comodojo\Exception\ZipException $exception) {
            $this->redirect('../../?download_generated=false');
        }

        if ($is_valid) {
            header("Content-Description: File Transfer");
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=$bestandsnaam");
            readfile($bestandsnaam);
            unlink($bestandsnaam);
        } else {
            $this->redirect('../../?download_generated=false');
        }
    }

    /**
     * Provides a form to create a new assignment.
     */
    function newAssignment(){
        $this->get_session();
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Opdrachten" => "/staff/assignment", "Nieuwe opdracht" => "#"]);
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/assignment/", "align" => "stacked"], $_SESSION['type']);

        $classes = $this->options($this->getSchoolsAndClasses());
        $reviewers = $this->options($this->getReviewers());

        echo $this->templates->render("assignment::new", ["title" => "Tekstmijn | Opdrachten",
            "page_title" => "Nieuwe opdracht",
            "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "classes" => $classes, "reviewers" => $reviewers,
            "page_js" => "/staff/vendor/application/load_date_picker.js"
        ]);
    }

    /**
     * Saves the new assignment to the database
     */
    function addAssignment(){
        //Check if there are at least 3 reviewers
        if (count($_POST['reviewers']) < 3) {
            $this->redirect("../new/?success=false", 303);
        }

        //Create empty array to check if adding the assignment works fine
        $results = Array();

        //Get new UUID() using MySQL Database
        $query = "SELECT UUID()";
        $UUID = $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['UUID()'];

        //Insert new Assignment into Assignments Table
        $result = $this->database->insert(
            "assignments",
            [
                "id" => $UUID,
                "title" => $_POST['titel']
            ]
        );
        array_push($results, $result);

        //Insert the new assignment into the assignment_class table
        foreach ($_POST['class_id'] as $id=>$class_id) {
            $result = $this->database->insert(
                "assignments_class",
                [
                    "assignment_id" => $UUID,
                    "class_id" => $class_id,
                    "start_date" => $_POST['start_date'],
                    "end_date" => $_POST['end_date']
                ]
            );
            array_push($results, $result);
        }

        //Insert te new assignment and its allocations to reviewers into the allocations table
        //Create list with all students from the selected classes
        $classes = Array();
        foreach ($_POST['class_id'] as $id=>$class_id) {
            array_push($classes, $class_id);
        }
        $class_id_list = "(";
        foreach ($classes as $index => $class_id) {
            $class_id_list = $class_id_list.$class_id.",";
        }
        $class_id_list = substr($class_id_list,0,-1);
        $class_id_list = $class_id_list.")";
        $query = "SELECT id
                  FROM students
                  WHERE class_id IN $class_id_list
                 ";
        $students_x = $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
        $students = Array();
        foreach ($students_x as $key => $value){
            array_push($students, $value['id']);
        }

        //Create an array with all the reviewers
        $reviewers = Array();
        foreach ($_POST['reviewers'] as $id=>$staff_id) {
            array_push($reviewers, $staff_id);
        }

        //Calculate max number of students per reviewer
        $number_of_reviewers = count($reviewers);
        $number_of_students = count($students)*3;
        $max_number = ceil(($number_of_students/$number_of_reviewers))+1;

        //Create an array with the reviewers and the students the reviewers have to review
        //Create empty array
        $to_review = Array();
        foreach ($_POST['reviewers'] as $id=>$staff_id) {
            $to_review[$staff_id] = Array();
        }

        //Fill array with students
        foreach ($students as $index => $student_id) {
            foreach (range(1,3,1) as $number) {
                $allocated = False;
                while ($allocated == False) {
                    $reviewer = $reviewers[array_rand($reviewers, 1)];
                    if (!in_array($student_id, $to_review[$reviewer])) {
                        $existing_array = $to_review[$reviewer];
                        array_push($existing_array, $student_id);
                        $to_review[$reviewer] = $existing_array;
                        $number_of_allocations = count($to_review[$reviewer]);
                        if ($number_of_allocations >= $max_number) {
                            $index = array_search($reviewer, $reviewers);
                            unset($reviewers[$index]);
                        }
                        $allocated = True;
                    }
                }
            }
        }

        //Save the to_review array into the allocations table
        foreach ($to_review as $staff_id=>$students) {
            foreach ($students as $index => $student_id) {
                $result = $this->database->insert(
                    "allocations",
                        [
                            "staff_id" => $staff_id,
                            "assignment_id" => $UUID,
                            "student_id" => $student_id
                        ]
                    );
                array_push($results, $result);
            }
        }

        //Insert the qualtrics url into the reviewerlist table
        $result =$this->database->insert(
            "reviewerlist",
            [
                "assignment_id" => $UUID,
                "qualtrics_url" => $_POST['review_list']
            ]
        );
        array_push($results, $result);

        //Check if adding the assignment works fine
        if (!in_array(False,$results)) {
            $this->redirect("../?success=true", 303);
        }
        else {
            $this->redirect("../?success=true", 303);
        }
    }

    /**
     * Updates the assignment in the system based on the changes made on the edit page.
     *
     * @param $assignment_id string containing the assignment ID
     */
    function updateAssignment($assignment_id){
        //Quote assignment id
        $assignment_id_quoted = $this->database->quote($assignment_id);

        //Create empty array to check if adding the assignment works fine
        $results = Array();

        //Edit the Assignment in the Assignments Table
        $result = $this->database->update("assignments", [
            "title" => $_POST['titel']
            ], ["id" => $assignment_id]);
        array_push($results, $result);

        //Update the assignment values (start_date & end_date) into the assignment_class table
        $result = $this->database->update(
            "assignments_class",
            [
                "start_date" => $_POST['start_date'],
                "end_date" => $_POST['end_date']
            ], ["assignment_id" => $assignment_id]);
        array_push($results, $result);

        //Update the qualtrics url into the reviewerlist table
        $result =$this->database->update(
            "reviewerlist",
            [
                "qualtrics_url" => $_POST['review_list']
            ], ["assignment_id" => $assignment_id]);
        array_push($results, $result);

        //Check if adding the assignment works fine
        if (!in_array(False,$results)) {
            $this->redirect("../../?success=true", 303);
        }
        else {
            $this->redirect("../../?success=true", 303);
        }
    }

    /**
     * Removes an assignment from the system.
     *
     * @param $assignment_id
     */
    function deleteAssignment($assignment_id){
        //Create empty array to check if adding the assignment works fine
        $results = Array();

        //Remove Assignment from the assignments table
        $result = $this->database->delete("assignments",
            ["id" => $assignment_id]
        );
        array_push($results, $result);

        //Remove assignment from the assignment_class table
        $result = $this->database->delete("assignments_class",
            ["assignment_id" => $assignment_id]
        );
        array_push($results, $result);

        //Remove assignment from the allocations table
        $result = $this->database->delete("allocations",
            ["assignment_id" => $assignment_id]
        );
        array_push($results, $result);

        //Remove assignment from the reviewerlist table
        $result = $this->database->delete("reviewerlist",
            ["assignment_id" => $assignment_id]
        );
        array_push($results, $result);

        //Check if removing the assignment worked out fine
        if (!in_array(False,$results)) {
            $this->redirect("../../?delete=true", 303);
        }
        else {
            $this->redirect("../../?delete=false", 303);
        }
    }


    /*
     * Supporting functions
     */

    /**
     * Gets the element scoring list URL from the database.
     * @param $id string containing the assignment ID
     * @return string containing the URL
     */
    function getQualtricsURL($id){
        $assignmentid = $this->database->quote($id);
        $query = "SELECT qualtrics_url
                    FROM reviewerlist
                    WHERE assignment_id = $assignmentid;";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['qualtrics_url'];
    }

    /**
     * Get a list of all reviewers in use for this assignment.
     *
     * @param $id string containing the assignment ID
     * @return array containing a list of staff ID's
     */
    function getReviewersforAssignment($id) {
        $assignment_id = $this->database->quote($id);
        $query = "SELECT DISTINCT staff_id
                  FROM allocations
                  WHERE assignment_id = $assignment_id";
        $reviewers_x =  $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
        $reviewers = Array();
        foreach ($reviewers_x as $key => $value) {
            array_push($reviewers, $value['staff_id']);
        }
        return $reviewers;
    }

    /**
     * Gathers a list of files for submissions assigned to a certain member of staff for a particular assignment.
     *
     * @param $staffid int containing the staff member's ID
     * @param $assignmentid string containing the assignment ID
     * @return array containing both the file names on server and the file names at submission time
     */
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

    /**
     * Generates name parts for review package.
     *
     * @param $staffid int containing the staff member's ID
     * @param $assignmentid string containing the assignment ID
     * @return array containing the full name of the staff member and the assignment title
     */
    function gatherNames($staffid, $assignmentid){
        $quoted_staffid = $this->database->quote($staffid);
        $quoted_assignemntid = $this->database->quote($assignmentid);
        $query = "SELECT CONCAT(firstname,prefix,lastname) as fullname, title as assignment_name
                FROM staff, assignments
                WHERE staff.id = $quoted_staffid
                AND assignments.id = $quoted_assignemntid";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    /**
     * Retrieves assignment name for given ID.
     *
     * @param $assignmentid string containing the assignment ID
     * @return string containing the assignment name
     */
    function gatherAssignmentName($assignmentid){
        $quoted_assignemntid = $this->database->quote($assignmentid);
        $query = "SELECT title as assignment_name
                FROM assignments
                WHERE assignments.id = $quoted_assignemntid";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['assignment_name'];
    }

    /**
     * Retrieves all assignment details for a given ID.
     *
     * @param $id string containing the assignment ID
     * @return array containing title, start and end date
     */
    function getAssignment($id){
        $id = $this->database->quote($id);
        return $this->database->query("SELECT assignments.title, assignments_class.start_date, assignments_class.end_date
                                              FROM assignments, assignments_class
                                              WHERE assignments.id = assignments_class.assignment_id
                                              AND assignments.id = $id
                                              LIMIT 1")->fetch();
    }

    /**
     * Retrieves all classes assigned to a certain assignment.
     *
     * @param $id string containing the assignment ID
     * @return array containing all class ID's
     */
    function getCoupledClasses($id){
        $return = Array();
        $id = $this->database->quote($id);
        $query = "SELECT class_id
                  FROM assignments_class
                  WHERE assignment_id = $id
                  ";
        $export = $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($export as $index => $class_id) {
            array_push($return, $class_id['class_id']);
        }
        return $return;
    }

    /**
     * Retrieves all reviewers assigned to a certain assignment.
     *
     * @param $id string containing the assignment ID
     * @return array containing all staff ID's
     */
    function getCoupledReviewers($id){
        $return = Array();
        $id = $this->database->quote($id);
        $query = "SELECT DISTINCT staff_id
                  FROM allocations
                  WHERE assignment_id = $id
                  ";
        $export = $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($export as $index => $class_id) {
            array_push($return, $class_id['staff_id']);
        }
        return $return;
    }

    /**
     * Retrieves all known assignments in the system.
     *
     * @return array containing all assignments with their title and ID
     */
    function getAssignments(){
        $query = "SELECT assignments.title, assignments.id FROM assignments ORDER BY title ASC";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generates a list of classes for use in adding new assignments.
     *
     * @return array of all classes prepended with their school name.
     */
    function getSchoolsAndClasses(){
        $separator = "': '";
        return $this->database->query("SELECT CONCAT_WS($separator, schools.name, class.name) AS name, class.id FROM schools, class WHERE schools.id = class.school_id")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generates a list of reviewers for use in adding new assignmnts.
     *
     * @return array of all reviewers
     */
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

}