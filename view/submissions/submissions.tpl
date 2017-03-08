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
                                    <div class="col-md-2">
                                        Spelling
                                    </div>
                                    <div class="col-md-2">
                                        Stijl
                                    </div>
                                    <div class="col-md-2">
                                        Vorm
                                    </div>
                                    <div class="col-md-3">
                                        Opmerkingen
                                    </div>
                                    <div class="col-md-3">
                                        Opslaan
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $submission_ids = Array();
                            foreach ($students_ingeleverd as $key => $value) {
                            $current_grades = getGrades($db, $staff_id, $value['id'], ["Stijl","Spelling","Vorm"]);
                            $submission_id = $value['id'];
                            array_push($submission_ids, $submission_id);
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
                                            <div class="col-md-2">
                                                <input name="grading_name[]" type="hidden" placeholder="Type beoordeling" class="form-control input-md" value="Stijl">
                                                <input value="<?php echo $current_grades['Stijl']; ?>" name="grading_grade[]" type="number" placeholder="8,0" min="1.0" max="10.0" step="0.1" class="form-control input-md">
                                            </div>
                                            <div class="col-md-2">
                                                <input name="grading_name[]" type="hidden" placeholder="Type beoordeling" class="form-control input-md" value="Spelling">
                                                <input value="<?php echo $current_grades['Spelling']; ?>"name="grading_grade[]" type="number" placeholder="8,0" min="1.0" max="10.0" step="0.1" class="form-control input-md">
                                            </div>
                                            <div class="col-md-2">
                                                <input name="grading_name[]" type="hidden" placeholder="Type beoordeling" class="form-control input-md" value="Vorm">
                                                <input value="<?php echo $current_grades['Vorm']; ?>" name="grading_grade[]" type="number" placeholder="8,0" min="1.0" max="10.0" step="0.1" class="form-control input-md">
                                            </div>
                                            <div id="notes_button_<?php echo $submission_id; ?>" class="col-md-3">
                                                <button id="add_button" type="submit" onclick="addPencil(this.parentNode.parentNode.parentNode, this)" class="btn btn-default"><i class="glyphicon glyphicon-pencil"></i></button>
                                            </div>
                                            <div class="col-md-3">
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
                        <?php
                            foreach ($submission_ids as $submission_id){
                                $submission_array = $submission_array.$submission_id.",";
                            }
                            $submissions = substr($submission_array, 0, -1);
                        ?>

                        <button type="button" onclick="saveAll('<?php echo $submissions; ?>')" class="btn btn-default"><i class="glyphicon glyphicon-floppy-open"></i> Alle beoordelingen opslaan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
