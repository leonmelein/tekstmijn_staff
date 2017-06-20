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
        <!-- <h3 class="page_title">?php //echo $page_subtitle;</h3>-->
<!--        <h3>Deze functionaliteit is momenteel nog in ontwikkeling.<br>Dank voor uw geduld!</h3>-->
        <?php print_r($status_tbl); ?>
    </div>
</div>