<?php

/**
 * Created by PhpStorm.
 * User: leon
 * Date: 21-06-17
 * Time: 11:54
 */
class submissions extends classroom
{
    /*
     * Routing functions
     */
    public function overview(){
        $this->get_session();
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/submissions/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "../account/", "Inzendingen" => "#"]);

        $classes = $this->getClasses($_SESSION["staff_id"]);
        $columns = [
            ["Klas", "class"],
            ["Niveau", "level"],
            ["Jaar", "year"]
        ];
        $table = $this->table($this->bootstrap, $columns, $classes, null, '<a href="%s/">%s</a>');
        echo $this->templates->render("submissions::index", ["title" => "Tekstmijn | Inzendingen",
            "page_title" => "Inzendingen", "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table" => $table]);
    }

    public function assignmentOverview($class_id){
        $this->get_session();
        $class = $this->getClassName($class_id);
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/submissions/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Inzendingen" => "/staff/submissions/",
            sprintf("Klas %s", $class) => "#"]);

        $students = $this->getAssignments($class_id);
        $columns = [
            ["Titel", "title"],
            ["Status", "status"],
            ["Startdatum", "start_date"],
            ["Uiterste inleverdatum", "end_date"],
        ];

        $table = $this->table($this->bootstrap, $columns, $students, null, '<a href="%s/">%s</a>');
        echo $this->templates->render("submissions::classes", ["title" => "Tekstmijn | Inzendingen",
            "page_title" => "Inzendingen", "page_subtitle" => sprintf("Klas %s", $class),  "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table" => $table]);
    }

    public function assignmentSubmissions($class_id, $assignment_id){
        $this->get_session();
        $staff_id = $_SESSION['staff_id'];
        $title = $this->getAssignmentName($assignment_id);
        $class = $this->getClassName($class_id);
        $tabs = $this->tabs($this->bootstrap, ["Ingeleverd" => "#ingeleverd", "Te laat" => "#telaat", "Niet ingeleverd" => "#nietingeleverd", "Beoordelen" => "#beoordelen"], 'Ingeleverd');
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/submissions/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Inzendingen" => "/staff/submissions/", sprintf("Klas %s", $class) => "/staff/submissions/$class_id",
            $title => "#"]);

        $students_ingeleverd =  $this->getAssignmentSubmissions($class_id, $assignment_id);
        $columns = [
            ["Leerlingnummer", "student_id"],
            ["Naam", "name"],
            ["Inleverdatum", "submission_date"],
            ["Aantal pogingen", "submission_count"],
        ];

        $table_ingeleverd = $this->table($this->bootstrap, $columns, $students_ingeleverd, null, '<a href="%s/">%s</a>');

        $students = $this->getAssignmentLateSubmissions($class_id, $assignment_id);
        $table_telaat = $this->table($this->bootstrap, $columns, $students, null, '<a href="%s/">%s</a>');

        $students = $this->getAssignmentMissingSubmissions($class_id, $assignment_id);
        $columns = [
            ["Leerlingnummer", "id"],
            ["Naam", "name"],
        ];
        $table_nietingeleverd = $this->table($this->bootstrap, $columns, $students);
        $gradingtable = $this->generateGradingTable($students_ingeleverd, $staff_id, $class_id, $assignment_id);

        $page_js = "/staff/vendor/application/add_pencil.js";

        echo $this->templates->render("submissions::submissions", [
            "title" => "Tekstmijn | Inzendingen",
            "page_title" => "Inzendingen",
            "page_subtitle" => $title,
            "menu" => $menu,
            "breadcrumbs" => $breadcrumbs,
            "table_ingeleverd" => $table_ingeleverd,
            "table_telaat" => $table_telaat,
            "table_nietingeleverd" => $table_nietingeleverd,
            "students_ingeleverd" => $students_ingeleverd,
            "tabs" => $tabs,
            "page_js" => $page_js,
            "class_id" => $class_id,
            "assignment_id" => $assignment_id,
            "staff_id" => $staff_id,
            "gradingarray" => $this->generateGradingArray($students_ingeleverd),
            "gradingtable" => $gradingtable]);
    }

    public function individualSubmission($class_id, $assignment_id, $submission_id){
        $this->get_session();
        $title = "Inzending";
        $assignment_name = $this->getAssignmentName($assignment_id);
        $student_name = $this->getStudentName($submission_id);
        $subtitle = sprintf("%s : %s", $assignment_name, $student_name);
        $class = $this->getClassName($class_id);
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/submissions/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Inzendingen" => "/staff/submissions/", sprintf("Klas %s", $class) => "/staff/submissions/$class_id", $assignment_name => "/staff/submissions/$class_id/$assignment_id", $title => "#"]);

        $submission_info = $this->getSubmissionInfo($submission_id);
        $page_js = "/staff/vendor/application/add_field.js";

        $staff_id = $_SESSION['staff_id'];
        $current_grades= $this->getIndividualGrades($staff_id, $submission_id, ["Score"]);

        echo $this->templates->render("submissions::grading", ["title" => "Tekstmijn | Inzendingen",
            "page_title" => $title, "page_subtitle" => $subtitle, "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "class_id" => $class_id,
            "assignment_id" => $assignment_id,
            "submission_id" => $submission_id,
            "page_js" => $page_js,
            "submission_date" => $submission_info["submission_date"],
            "submission_file" => $submission_info["submission_file"],
            "submission_count" => $submission_info["submission_count"],
            "submission_originalfile" => $submission_info["original_file"],
            "text" => $submission_info["text"],
            "current_grades" => $current_grades,
        ]);
    }

    /*
     * Supporting functions
     */
    function getClasses($id){
        $quoted_id = $this->database->quote($id);
        $query = "SELECT class.id as id, class.year as year, level.name as level, class.name as class 
                FROM class, level
                WHERE class.level_id = level.id
                AND class.id in (
                  SELECT class_id
                  FROM class_staff
                  WHERE class_staff.staff_id = $quoted_id
                )
                ORDER BY year, class ASC";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    function getAssignments($id){
        $quoted_id = $this->database->quote($id);
        $query = "SELECT id, title, status, DATE_FORMAT(start_date, '%d %M %Y %H:%i') as start_date, DATE_FORMAT(end_date, '%d %M %Y %H:%i') AS end_date
                    FROM (
                        SELECT assignments.id AS id, assignments.title AS title,
                          IF(NOW() BETWEEN assignments_class.start_date AND assignments_class.end_date,
                            'Open', 'Gesloten') AS status,
                             assignments_class.end_date AS end_date,
                             assignments_class.start_date AS start_date
                        FROM assignments, assignments_class
                        WHERE assignments_class.class_id = $quoted_id
                        AND assignments_class.assignment_id = assignments.id
                    ) AS classwork
                    ORDER BY title, start_date";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    function getAssignmentSubmissions($id_class, $id_assignment){
        $quoted_id_class = $this->database->quote($id_class);
        $quoted_id_assignment = $this->database->quote($id_assignment);
        $query = "SELECT submissions.id as id, students.id as student_id, CONCAT_WS(' ',students.firstname, students.prefix, students.lastname) as name, DATE_FORMAT(submissions.time, '%d %M %Y, %H:%i') as submission_date, submissions.submission_count as submission_count
                FROM students, submissions
                WHERE students.class_id = $quoted_id_class
                      AND submissions.assignment_id = $quoted_id_assignment
                      AND students.id = submissions.student_id
                      ORDER BY name";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    function getAssignmentLateSubmissions($id_class, $id_assignment){
        $quoted_id_class = $this->database->quote($id_class);
        $quoted_id_assignment = $this->database->quote($id_assignment);
        $query = "SELECT submissions.id as id, students.id as student_id, CONCAT_WS(' ',students.firstname, students.prefix, students.lastname) as name, DATE_FORMAT(submissions.time, '%d %M %Y, %H:%i') as submission_date, submissions.submission_count as submission_count
                FROM students, submissions
                WHERE students.class_id = $quoted_id_class
                      AND submissions.assignment_id = $quoted_id_assignment
                      AND students.id = submissions.student_id 
                      AND submissions.time > (
                        SELECT end_date
                        FROM assignments_class
                        WHERE assignments_class.assignment_id = $quoted_id_assignment
                        AND assignments_class.class_id = $quoted_id_class
                      )
                ORDER BY name";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    function getAssignmentMissingSubmissions($id_class, $id_assignment){
        $quoted_id_class = $this->database->quote($id_class);
        $quoted_id_assignment = $this->database->quote($id_assignment);
        $query = "SELECT students.id, CONCAT_WS(' ',students.firstname, students.prefix, students.lastname) as name
                FROM students
                WHERE students.class_id = $quoted_id_class
                  AND students.id NOT IN (
                  SELECT student_id FROM submissions
                  WHERE assignment_id = $quoted_id_assignment
                )
                ORDER BY name";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    function getAssignmentName($id){
        $quoted_id = $this->database->quote($id);
        $query = "SELECT assignments.title
                FROM assignments
                WHERE assignments.id = $quoted_id";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['title'];
    }

    function getStudentName($id){
        $quoted_id = $this->database->quote($id);
        $query = "SELECT CONCAT_WS(' ',students.firstname, students.prefix, students.lastname) as name
                FROM students
                WHERE students.id = (
                SELECT submissions.student_id
                FROM submissions
                WHERE submissions.id = $quoted_id
                )";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['name'];
    }

    function getSubmissionInfo($id){
        return $this->database->get(
            "submissions",
            [
                "time(submission_date)",
                "file(submission_file)",
                "submission_count",
                "original_file",
                "text",
                "student_id"
            ],
            ["id" => $id]
        );
    }

    function generateGradingTable($submissions, $staff_id, $class_id, $assignment_id)
    {
        $gradingtable = "";
        $basetbl = '<form id="grade_%s" class="grade" method="post" action="grade/">
                <input name="class_id" type="hidden" value="%s">
                <input name="assignment_id" type="hidden" value="%s">
                <input name="submission_id" type="hidden" value="%s">
                    <tr id="students_%s" class="students">
                        <td>%s</td>
                        <td>%s</td>
                        <td colspan="4">
                            <div class="row">
                                <div class="col-md-6">
                                    <input name="grading_name[]" type="hidden" placeholder="Type beoordeling" class="form-control input-md" value="Score">
                                    <input value="%s" name="grading_grade[]" type="number" placeholder="50" min="0" max="150" step="1" class="form-control input-md">
                                </div>
                                <div id="notes_button_%s" class="col-md-3 text-center">
                                    <button id="add_button" type="submit" onclick="addPencil(this.parentNode.parentNode.parentNode, this)" class="btn btn-default"><i class="glyphicon glyphicon-pencil"></i></button>
                                </div>
                                <div class="col-md-3 text-center">
                                    <button type="submit" class="btn btn-default"><i class="glyphicon glyphicon-floppy-open"></i></button>
                                </div>
                            </div>
                            <div id="content_%s" style="display: none;">
                                <div class="col-md-9">
                                    </br>
                                    <textarea name="grade_Opmerkingen" class="form-control input-md" rows="3">%s</textarea>
                                </div>
                            </div>
                        </td>
                    </tr>
                </form>';

        foreach ($submissions as $key => $value) {
            $current_grades = $this->getSubmissionGrades($staff_id, $value['id'], ["Score"]);
            $submission_id = $value['id'];

            $gradingtable .= sprintf($basetbl,
                $submission_id,
                $class_id,
                $assignment_id,
                $value['id'],
                $submission_id,
                $value['student_id'],
                $value['name'],
                $current_grades['Score'],
                $submission_id,
                $submission_id,
                $current_grades['Notes']);

        }

        return $gradingtable;
    }

    function generateGradingArray($submissions){
        $submission_ids = Array();
        foreach ($submissions as $key => $value) {
            $submission_id = $value['id'];
            array_push($submission_ids, $submission_id);
        }

        $submission_array = "";
        foreach ($submission_ids as $submission_id){
            $submission_array = $submission_array.$submission_id.",";
        }

        return substr($submission_array, 0, -1);
    }

    function getSubmissionGrades($staff_id, $submission_id, $types){
        $current_grades = [];
        $quoted_staff_id = $this->database->quote($staff_id);
        $quoted_submission_id = $this->database->quote($submission_id);
        $query = "SELECT notes FROM grading
                       WHERE grading.staff_id = $quoted_staff_id
                       AND grading.submission_id = $quoted_submission_id
                       AND grading.type = 'Notes'";
        $note_txt = $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]["notes"];
        $current_grades['Notes'] = $note_txt;
        foreach ($types as $type) {
            $quoted_type = $this->database->quote($type);
            $query = "SELECT grade FROM grading
                     WHERE grading.staff_id = $quoted_staff_id
                     AND grading.submission_id = $quoted_submission_id
                     AND grading.type = $quoted_type";
            $grade = $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]["grade"];
            $current_grades[$type] = $grade;
        }
        return $current_grades;
    }

    function getIndividualGrades($staff_id, $submission_id, $types){
        $current_grades = [];
        $quoted_staff_id = $this->database->quote($staff_id);
        $quoted_submission_id = $this->database->quote($submission_id);
        $query = "SELECT notes FROM grading
                       WHERE grading.staff_id = $quoted_staff_id
                       AND grading.submission_id = $quoted_submission_id
                       AND grading.type = 'Notes'";
        $note_txt = $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['notes'];
        $current_grades['Notes'] = $note_txt;

        foreach ($types as $type) {
            $quoted_type = $this->database->quote($type);
            $query = "SELECT grade FROM grading
                     WHERE grading.staff_id = $quoted_staff_id
                     AND grading.submission_id = $quoted_submission_id
                     AND grading.type = $quoted_type";
            $grade = $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['grade'];
            $current_grades[$type] = $grade;
        }
        return $current_grades;
    }

}