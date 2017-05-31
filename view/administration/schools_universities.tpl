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
            <div id="schools" class="tab-pane active">
                <br>
                <a class="btn btn-default" href="editschool/newSchool/" role="button">Nieuwe school</a>
                <br>
                <?php echo $tbl_schools; ?>
            </div>
            <div id="universities" class="tab-pane">
                <br>
                <a class="btn btn-default" href="editschool/newUniversity/" role="button">Nieuwe universiteit</a>
                <br>
                <?php echo $tbl_universities; ?>
            </div>
        </div>
    </div>
</div>