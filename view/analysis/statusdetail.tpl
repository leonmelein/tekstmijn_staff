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
        <?php echo $tabs; ?>
        <div class="tab-content">
            <div id="status" class="tab-pane active">
                <h3>Status</h3>
                <h4 class="page_title"><?php echo $page_subtitle;?></h4>
                <?php echo $status_tbl_detail; ?>
            </div>
            <div id="statistics" class="tab-pane">
                <h3>Statistiek</h3>
                <?php print_r($analysis_tbl); ?>
            </div>
        </div>
    </div>
</div>
