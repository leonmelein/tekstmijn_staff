<?php $this->layout('main_layout', ['title' => $title, 'pageJS' => $page_js]); ?>
<div class="row">
    <div class="col-md-12">
        <?php echo $breadcrumbs; ?>
        <?php
            if($_GET["success"] == "true") {
                echo '<div class="alert alert-success alert-dismissable" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Sluiten"><span aria-hidden="true">&times;</span></button>
                <strong>Gelukt.</strong> Uw beoordeling is opgeslagen.
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
        <?php echo $tabs; ?>
        <div class="tab-content">
            <div id="deinzending" class="tab-pane active">
                <br>
                <div class="row">
                    <div class="col-md-2"><strong>Datum</strong></div>
                    <div class="col-md-4"><?php echo $submission_date; ?></div>
                </div>
                <div class="row">
                    <div class="col-md-2"><strong>Bestand</strong></div>
                    <div class="col-md-4"><a href="/assets/submissions/<?php echo $submission_file; ?>" target="_blank"><?php echo $submission_originalfile; ?></a></div>
                </div>
                <div class="row">
                    <div class="col-md-2"><strong>Aantal pogingen</strong></div>
                    <div class="col-md-4"><?php echo $submission_count; ?></div>
                </div>
            </div>
            <div id="beoordelen" class="tab-pane">
                <br>
                <form class="form-horizontal" method="post" action="grade/">
                    <input name="class_id"type="hidden" value="<?php echo $class_id; ?>">
                    <input name="assignment_id"type="hidden" value="<?php echo $assignment_id; ?>">
                    <input name="submission_id"type="hidden" value="<?php echo $submission_id; ?>">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Type beoordeling</label>
                            <label class="col-md-2 control-label">Cijfer</label>
                        </div>
                        <div class="form-group">
                            <div class="col-md-3">
                                <input name="grading_name[]" type="text" placeholder="Stijl" class="form-control input-md">
                            </div>
                            <div class="col-md-2">
                                <input name="grading_grade[]" type="number" placeholder="8,0" min="1.0" max="10.0" step="0.1" class="form-control input-md">
                            </div>
                            <div class="col-xs-1">
                                <button id="add_button" type="button" onclick="addRow()" class="btn btn-default"><i class="glyphicon glyphicon-plus"></i></button>
                            </div>
                        </div>
                        <div id="content">
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="singlebutton"></label>
                            <div class="col-md-2">
                                <button id="singlebutton" name="singlebutton" class="btn btn-primary">Beoordelen</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>
