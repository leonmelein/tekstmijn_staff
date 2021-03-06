<?php $this->layout('main_layout', ['title' => $title, 'pageJS' => $page_js]); ?>
<div class="row">
    <div class="col-md-12">
        <?php echo $breadcrumbs; ?>
        <?php if($_GET["success"] == "true") { ?>
        <div class="alert alert-success alert-dismissable" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Sluiten"><span aria-hidden="true">&times;</span></button>
            <strong>Gelukt.</strong> De opdracht is succesvol opgeslagen.
        </div>
        <?php } else if($_GET["success"] == "false") { ?>
        <div class="alert alert-danger alert-dismissable" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Sluiten"><span aria-hidden="true">&times;</span></button>
            <strong>Oeps.</strong> Selecteer minimaal 3 beoordelaars.
        </div>
        <?php } ?>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <?php echo $menu; ?>
    </div>
    <div class="col-md-9">
        <h1 class="page_title"><?php echo $page_title; ?></h1>
        <form class="form-horizontal" method="post" action="../save/">
            <fieldset>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="titel">Titel</label>
                    <div class="col-md-4">
                        <input id="titel" name="titel" type="text" placeholder="Opdracht" class="form-control input-md" required="" value="<?=$this->e($assignment['title'])?>">
                    </div>
                </div>

                <!-- Select Basic -->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="class_id">Klas</label>
                    <div class="col-md-4">
                        <select id="class_id" name="class_id[]" class="form-control" multiple="multiple">
                            <?php echo $classes; ?>
                        </select>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-md-4 control-label" for="start_date">Begindatum en -tijd</label>
                    <div class='col-md-4 input-group date' id='datetimepicker2'>
                        <input type='text' class="form-control input-md datetimepicker" name="start_date" id="start_date" value="<?=$this->e($assignment['start_date'])?>" required />
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="end_date">Einddatum en -tijd</label>
                    <div class='col-md-4 input-group date' id='datetimepicker3'>
                        <input type='text' class="form-control input-md datetimepicker" name="end_date" id="end_date" value="<?=$this->e($assignment['end_date'])?>" required />
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                </div>

                <!-- Select Multiple -->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="reviewers">Beoordelaars</label>
                    <div class="col-md-4">
                        <select id="reviewers" name="reviewers[]" class="form-control" multiple="multiple" min="3">
                            <?php echo $reviewers; ?>
                        </select>
                        <span class="help-block">Selecteer minimaal 3 beoordelaars</span>
                    </div>
                </div>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="review_list">Beoordelingslijst</label>
                    <div class="col-md-4">
                        <input id="review_list" name="review_list" type="text" placeholder="rug.eu.qualtrics.com" class="form-control input-md">
                        <span class="help-block">Plak hier de Qualtrics URL</span>
                    </div>
                </div>

                <!-- Button -->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="save"></label>
                    <div class="col-md-4">
                        <button id="save" name="save" class="btn btn-primary">Opslaan</button>
                    </div>
                </div>

            </fieldset>
        </form>

    </div>
</div>
