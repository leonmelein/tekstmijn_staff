<?php $this->layout('main_layout', ['title' => $title, 'pageJS' => $page_js]); ?>
<div class="row">
    <div class="col-md-12">
        <?php echo $breadcrumbs; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <?php echo $menu; ?>
    </div>
    <div class="col-md-9">
        <h1 class="page_title"><?php echo $page_title; ?></h1>
        <h3 class="page_title"><?php echo $page_subtitle; ?></h3>
        <?php echo $tabs; ?>
        <div class="tab-content">
            <div id="ingeleverd" class="tab-pane active">
                <?php echo $table_ingeleverd; ?>
            </div>
            <div id="telaat" class="tab-pane">
                <?php echo $table_telaat; ?>
            </div>
            <div id="nietingeleverd" class="tab-pane">
                <?php echo $table_nietingeleverd; ?>
            </div>
            <div id="beoordelen" class="tab-pane">
                <?php
                    $submission_ids = Array();
                    foreach ($students_ingeleverd as $key => $value) {
                    $submission_id = $value['id'];
                    array_push($submission_ids, $submission_id);
                    }
                    foreach ($submission_ids as $submission_id){
                        $submission_array = $submission_array.$submission_id.",";
                    }
                    $submissions = substr($submission_array, 0, -1);
                ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th colspan="2">Leerlinggegevens</th>
                            <th colspan="4">Beoordelen</th>
                        </tr>
                        <tr>
                            <th>Leerlingnummer</th>
                            <th>Naam</th>
                            <th colspan="4">
                                <div class="row">
                                    <div class="col-md-6">
                                        Score
                                    </div>
                                    <div class="col-md-3 text-center">
                                        Opmerkingen
                                    </div>
                                    <div class="col-md-3 text-center">
                                        Opslaan
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach ($students_ingeleverd as $key => $value) {
                            $current_grades = getGrades($db, $staff_id, $value['id'], ["Score"]);
                            $submission_id = $value['id'];
                            ?>
                            <form id="grade_<?php echo $submission_id; ?>" class="grade" method="post" action="grade/">
                                <input name="class_id"type="hidden" value="<?php echo $class_id; ?>">
                                <input name="assignment_id"type="hidden" value="<?php echo $assignment_id; ?>">
                                <input name="submission_id"type="hidden" value="<?php echo $value['id']; ?>">
                                <tr id="students_<?php echo $submission_id; ?>" class="students">
                                    <td><?php echo $value['student_id']; ?></td>
                                    <td><?php echo $value['name']; ?></td>
                                    <td colspan="4">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input name="grading_name[]" type="hidden" placeholder="Type beoordeling" class="form-control input-md" value="Score">
                                                <input value="<?php echo $current_grades['Score']; ?>" name="grading_grade[]" type="number" placeholder="50" min="0" max="150" step="1" class="form-control input-md">
                                            </div>
                                            <div id="notes_button_<?php echo $submission_id; ?>" class="col-md-3 text-center">
                                                <button id="add_button" type="submit" onclick="addPencil(this.parentNode.parentNode.parentNode, this)" class="btn btn-default"><i class="glyphicon glyphicon-pencil"></i></button>
                                            </div>
                                            <div class="col-md-3 text-center">
                                                <button type="submit" class="btn btn-default"><i class="glyphicon glyphicon-floppy-open"></i></button>
                                            </div>
                                        </div>
                                        <div id="content_<?php echo $submission_id; ?>" style="display: none;">
                                            <div class="col-md-9">
                                                </br>
                                                <textarea name="grade_Opmerkingen" class="form-control input-md" rows="3"><?php echo $current_grades['Notes']; ?></textarea>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </form>
                        <?php }; ?>
                    </tbody>
                </table>
                <div class="row">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <button type="button" onclick="saveAll('<?php echo $submissions; ?>')" class="btn btn-default"><i class="glyphicon glyphicon-floppy-open"></i> Alle beoordelingen opslaan</button>
                    </div>
                </div>
            </div>
            </br></br>
        </div>
    </div>
</div>
