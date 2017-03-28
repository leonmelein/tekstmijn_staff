<?php

function generate_reviewquestionnaire($database, $assignment_id, $staff_id, $submission_id) {
    $staff_id_quoted = $database->quote($staff_id);
    $submission_id_quoted = $database->quote($submission_id);
    $assignment_id_quoted = $database->quote($assignment_id);
    $query = "SELECT id, name, action, method
                FROM reviewerlists
                WHERE reviewerlists.assignment_id = $assignment_id_quoted";
    $questionnaire = $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];

    $questionnaire_id_quoted = $database->quote($questionnaire['id']);
    $query = "SELECT id, elementtype, label
                FROM reviewerlistsquestions
                WHERE reviewerlistsquestions.reviewerlists_id = $questionnaire_id_quoted";
    $questions = $database->query($query)->fetchAll(PDO::FETCH_ASSOC);

    echo "<h4>".$questionnaire['name']."</h4>";
    Form::open ($questionnaire['id'], $values = NULL, $attributes = Array("method" => $questionnaire['method'], "action" => $questionnaire['action']));
    Form::Hidden ("submission_id", $values = $submission_id, $attributes = NULL);
    Form::Hidden ("staff_id", $values = $staff_id, $attributes = NULL);
    Form::Hidden ("reviewerlist_id", $values = $questionnaire['id'], $attributes = NULL);
        foreach ($questions as $id => $value) {
            $elementtype = $value['elementtype'];
            $id = $value['id'];
            $id_quoted = $database->quote($id);
            $label = $value['label'];

            // Get attributes
            $query = "SELECT attribute_key, attribute_value
                FROM reviewerlistsquestions_attributes
                WHERE reviewerlistsquestions_attributes.reviewerlistsquestions_id = $id_quoted";
            $attributes_db = $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
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
            $options_db = $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
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
            $values_db = $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['value'];
            if (!empty($values_db)){
                $options_local['value'] = $values_db;
            }

            if ($elementtype == 'title'){
                echo "</br>";
                echo "<h5>".$label."</h5>";
            }
            else{
                Form::$elementtype ($label, $id, $options_local, $attributes_local);
            }
        }
        Form::Button ("Opslaan");
    Form::close (false);

}

function save_questionnaire($database, $values, $staff_id, $submission_id, $reviewerlist_id) {
    $database->delete("reviewing", [
        "AND" => [
            "staff_id" => $staff_id,
            "submission_id" => $submission_id,
            "reviewerlist_id" => $reviewerlist_id
        ]
    ]);
    foreach( $values as $key => $value){
        $database->insert("reviewing", [
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