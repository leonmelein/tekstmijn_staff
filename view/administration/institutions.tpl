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
        <div class="dropdown pull-right">
            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                <i class="glyphicon glyphicon-plus"></i> Nieuwe onderwijsinstelling
                <span class="caret"></span></button>
            <ul class="dropdown-menu">
                <li><a href="school/new">School</a></li>
                <li><a href="university/new">Universiteit</a></li>
            </ul>
        </div>
        <?php echo $tabs; ?>
        <div class="tab-content">
            <div id="schools" class="tab-pane active">
                <br>
                <?php echo $tbl_schools; ?>
            </div>
            <div id="universities" class="tab-pane">
                <br>
                <?php echo $tbl_universities; ?>
            </div>
        </div>
    </div>
</div>