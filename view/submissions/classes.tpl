<?php $this->layout('main_layout', ['title' => $title, 'pageJS' => $page_js]); ?>
<div class="row">
    <div class="col-md-12">
        <?php echo $breadcrumbs; ?>
        <?php if($_GET["download_generated"] == "false"): ?>
            <div class="alert alert-danger alert-dismissable" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Sluiten"><span aria-hidden="true">&times;</span></button>
                <b>Oeps.</b> We konden helaas het pakket niet voor u genereren. Probeer het opnieuw of vraag uw sectievoorzitter.
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <?php echo $menu; ?>
    </div>
    <div class="col-md-9">
        <h1 class="page_title"><?php echo $page_title; ?></h1>
        <h3 class="page_title"><?php echo $page_subtitle; ?></h3>
        <?php echo $table; ?>
    </div>
</div>
