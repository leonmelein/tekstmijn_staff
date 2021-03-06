<?php $this->layout('main_layout', ['title' => $title, 'pageJS' => $page_js]); ?>
<div class="row" xmlns="http://www.w3.org/1999/html">
    <div class="col-md-12">
        <?php echo $breadcrumbs; ?>
        <?php
            if($_GET["success"] == "true") {
                echo '<div class="alert alert-success alert-dismissable" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Sluiten"><span aria-hidden="true">&times;</span></button>
        <strong>Gelukt.</strong> Uw beoordeling is opgeslagen.
    </div>';
    } else if($_GET["success"] == "true") {
    echo '<div class="alert alert-success alert-dismissable" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Sluiten"><span aria-hidden="true">&times;</span></button>
        <strong>Oeps.</strong> Uw beoordeling kon niet worden verwerkt. Probeer het nogmaals of vraag uw sectievoorzitter.
    </div>';
    }
    ?>
</div>
</div>

<div class="row">
    <div class="col-md-3">
        <?php echo $menu; ?>
    </div>
    <div class="col-md-9">
        <h1 class="page_title"><?php echo $page_title; ?></h1>
        <h3 class="page_title"><?php echo $page_subtitle; ?></h3>
        <div class="row">
            <div class="col-md-2"><strong>Datum</strong></div>
            <div class="col-md-4"><?php echo $submission_date; ?></div>
        </div>
        <div class="row">
            <div class="col-md-2"><strong>Bestand</strong></div>
            <div class="col-md-4"><a download href="/assets/submissions/<?php echo $submission_file; ?>"><?php echo $submission_originalfile; ?></a></div>
        </div>
        <div class="row">
            <div class="col-md-2"><strong>Aantal pogingen</strong></div>
            <div class="col-md-4"><?php echo $submission_count; ?></div>
        </div>


        <div class="row spacer" style="height: 20px;"></div>

        <ul class="nav nav-tabs">
            <li role="presentation" class="active"><a href="#beoordelen" aria-controls="beoordelen" role="tab" data-toggle="tab" aria-expanded="true">Lezen en beoordelen</a></li>
            <li role="presentation" class=""><a href="#beoordelingslijst" aria-controls="beoordelingslijst" role="tab" data-toggle="tab" aria-expanded="false">Beoordelingslijst</a></li>
            <?php if($questionnaire): ?>
            <li role="presentation" class=""><a href="<?=$this->e($questionnaire)?>?student_id=<?=$this->e($student_id)?>&submission_id=<?=$this->e($submission_id)?>&text_length=<?=$this->e($text_length)?>" target="_blank" aria-controls="beoordelingslijst" role="tab" data-toggle="" aria-expanded="false">Qualtrics <i class="glyphicon glyphicon-new-window"></i></a></li>
            <?php endif; ?>
        </ul>

        <div class="tab-content">
            <div id="beoordelen" class="tab-pane active">
                <div class="row spacer" style="height: 6px;"></div>
                <div class="row">
                    <div class="col-md-6">
                        <h4>Tekst</h4>
                        <div class="form-group">
                            <textarea readonly class="form-control" id="text" name="text" rows="40"><?php echo $text; ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4>Beoordeling</h4>
                        <form class="form-horizontal" method="post" action="grade/">
                            <input name="class_id"type="hidden" value="<?php echo $class_id; ?>">
                            <input name="assignment_id"type="hidden" value="<?php echo $assignment_id; ?>">
                            <input name="submission_id"type="hidden" value="<?php echo $submission_id; ?>">
                            <fieldset>
                                <div class="form-group">
                                    <div class="col-md-6">
                                        <p class="form-control-static">Score</p>
                                        <input name="grading_name[]" type="hidden" placeholder="Type beoordeling" class="form-control input-md" value="Score">
                                    </div>
                                    <div class="col-md-4">
                                        <input value="<?php echo $current_grades['Score']; ?>" name="grading_grade[]" type="number" placeholder="50" min="0" max="150" step="1" class="form-control input-md">
                                    </div>
                                    <!--<div class="col-xs-2">
                                        <button id="add_button" type="button" onclick="addRow()" class="btn btn-default"><i class="glyphicon glyphicon-plus"></i></button>
                                    </div>-->
                                </div>
                                <div id="content">
                                </div>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <p class="form-control-static">Opmerkingen</p>
                                        <textarea class="form-control" name="grade_Opmerkingen" rows="4" cols="30"><?php echo $current_grades['Notes']; ?></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-0 control-label" for="singlebutton"></label>
                                    <div class="col-md-12">
                                        <button id="singlebutton" name="singlebutton" class="btn btn-primary">Beoordelen</button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
            <?php if ($user_type == 2) { ?>
            <div id="beoordelingslijst" class="tab-pane">
                <div class="row spacer" style="height: 6px;"></div>
                    <?php echo $form; ?>
            </div>
            <?php } ?>

        </div>

        <div class="row" style="height: 20px;"></div>
    </div>
</div>
