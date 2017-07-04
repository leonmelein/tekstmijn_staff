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
        <a class="btn btn-primary pull-right" href="../delete/"><i class="glyphicon glyphicon-remove"></i> Verwijder <?=$this->e(($school_type ? 'universiteit' : 'school'))?></a>
        <form class="form-horizontal" method="post" action="../save/">
            <fieldset>
                <!-- Select Basic -->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="type">Type school</label>
                    <div class="col-md-4">
                        <select id="type" name="type" class="form-control">
                            <option selected value="<?=$this->e($school_type)?>">
                                <?=$this->e(($school_type ? 'Universiteit' : 'School'))?>
                            </option>
                            <option disabled>──────────</option>
                            <option value="0">School</option>
                            <option value="1">Universiteit</option>
                        </select>
                    </div>
                </div>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="name">Naam school</label>
                    <div class="col-md-4">
                        <input id="name" name="name" type="text" placeholder="Hofstad" class="form-control input-md" value="<?php echo $school_name; ?>" required="">
                    </div>
                </div>

                <!-- Button -->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="opslaan"></label>
                    <div class="col-md-4">
                        <button id="opslaan" name="opslaan" class="btn btn-primary">Opslaan</button>
                    </div>
                </div>

            </fieldset>
        </form>
    </div>
</div>