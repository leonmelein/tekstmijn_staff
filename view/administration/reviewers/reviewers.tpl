<?php $this->layout('main_layout', ['title' => $title, 'pageJS' => $page_js]); ?>
<div class="row">
    <div class="col-md-12">
        <?php echo $breadcrumbs; ?>
        <?php if($_GET["reviewer_update"] == "true" | $_GET["reviewer_added"] == "true"): ?>
            <div class="alert alert-success alert-dismissable" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Sluiten"><span aria-hidden="true">&times;</span></button>
                <strong>Gelukt.</strong> Uw wijzigingen zijn opgeslagen.
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <?php echo $menu; ?>
    </div>
    <div class="col-md-9">
        <h1 class="page_title"><i class="glyphicon glyphicon-user"></i> Beoordelaars</h1>
        <a class="btn btn-primary pull-right" href="new/"><i class="glyphicon glyphicon-plus"></i> Nieuwe beoordelaar</a>
        <?php echo $tbl; ?>
    </div>
</div>