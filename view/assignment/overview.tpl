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
                <strong>Oeps.</strong> Er is iets fout gegaan, neem contact op met de systeembeheerder.
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
        <a class="btn btn-primary pull-right" href="new/"><i class="glyphicon glyphicon-plus"></i> Nieuwe opdracht</a>
        <?php echo $table;?>
    </div>
</div>
