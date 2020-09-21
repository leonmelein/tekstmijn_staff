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
        <form class="form-horizontal" method="post" action="../save/">
            <fieldset>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="title">Titel</label>
                    <div class="col-md-4">
                        <input id="title" name="title" type="text" placeholder="Vragenlijst" class="form-control input-md" required="" value="<?=$this->e($assignment['title'])?>">
                    </div>
                </div>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="qualtrics_url">Vragenlijst</label>
                    <div class="col-md-4">
                        <input id="qualtrics_url" name="qualtrics_url" type="text" placeholder="rug.eu.qualtrics.com/jfe/..." class="form-control input-md" required="">
                        <span class="help-block">Plak hier de Qualtrics URL</span>
                    </div>
                </div>

                <!-- Select Basic -->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="class_id">School</label>
                    <div class="col-md-4">
                        <select id="class_id" name="school_id" class="form-control">
                            <?php echo $options; ?>
                        </select>
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
