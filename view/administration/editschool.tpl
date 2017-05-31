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
        <form class="form-horizontal">
            <fieldset>
                <!-- Select Basic -->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="selectbasic">Type school</label>
                    <div class="col-md-4">
                        <?php if ($school_type == "0"): ?>
                        <select id="selectbasic" name="selectbasic" class="form-control">
                            <option value="0" selected>School</option>
                            <option value="1">Universiteit</option>
                        </select>
                        <?php endif; ?>
                        <?php if ($school_type == "1"): ?>
                        <select id="selectbasic" name="selectbasic" class="form-control">
                            <option value="0">School</option>
                            <option value="1" selected>Universiteit</option>
                        </select>
                        <?php endif; ?>
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
                    <label class="col-md-4 control-label" for="opslaan">Opslaan</label>
                    <div class="col-md-4">
                        <button id="opslaan" name="opslaan" class="btn btn-primary">Opslaan</button>
                    </div>
                </div>

            </fieldset>
        </form>
    </div>
</div>