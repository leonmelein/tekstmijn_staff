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
        <a class="btn btn-primary pull-right" href="../delete/"><i class="glyphicon glyphicon-remove"></i> Verwijder klas</a>

        <form class="form-horizontal" method="post" action="../save/">
            <fieldset>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="name">Naam</label>
                    <div class="col-md-4">
                        <input id="name" name="name" type="text" placeholder="" class="form-control input-md" value="<?=$this->escape($name)?>" required="">
                    </div>
                </div>

                <!-- Select Basic -->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="type">Niveau</label>
                    <div class="col-md-4">
                        <select id="type" name="type" class="form-control">
                            <option selected value="<?=$this->escape($levelid)?>"><?=$this->escape($levelname)?></option>
                            <option disabled>──────────</option>
                            <option value="1">HAVO</option>
                            <option value="5">VWO</option>
                            <option value="4">HAVO/VWO</option>
                            <option value="3">Atheneum</option>
                            <option value="2">Gymnasium</option>
                        </select>
                    </div>
                </div>

                <!-- Select Basic -->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="year">Leerjaar</label>
                    <div class="col-md-4">
                        <select id="year" name="year" class="form-control">
                            <option selected value="<?=$this->escape($year)?>">Jaar <?=$this->escape($year)?></option>
                            <option disabled>──────────</option>
                            <option value="1">Jaar 1</option>
                            <option value="2">Jaar 2</option>
                            <option value="3">Jaar 3</option>
                            <option value="4">Jaar 4</option>
                            <option value="5">Jaar 5</option>
                            <option value="6">Jaar 6</option>
                        </select>
                    </div>
                </div>

                <!-- Button -->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="opslaan"></label>
                    <div class="col-md-4">
                        <button id="opslaan" name="opslaan" class="btn btn-primary">Bijwerken</button>
                    </div>
                </div>

            </fieldset>
        </form>
    </div>
</div>