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
        <a class="btn btn-primary pull-right" href="../delete/"><i class="glyphicon glyphicon-remove"></i> Verwijder beoordelaar</a>

        <form class="form-horizontal" method="post" action="../save/">
            <fieldset>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="firstname">Voornaam</label>
                    <div class="col-md-4">
                        <input id="firstname" name="firstname" type="text" placeholder="" class="form-control input-md" value="<?=$this->escape($personnelmember['firstname'])?>" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="prefix">Tussenvoegsel</label>
                    <div class="col-md-4">
                        <input id="prefix" name="prefix" type="text" placeholder="" class="form-control input-md" value="<?=$this->escape($personnelmember['prefix'])?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="lastname">Achternaam</label>
                    <div class="col-md-4">
                        <input id="lastname" name="lastname" type="text" placeholder="" class="form-control input-md" value="<?=$this->escape($personnelmember['lastname'])?>" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="email">Emailadres</label>
                    <div class="col-md-4">
                        <input id="email" name="email" type="email" placeholder="" class="form-control input-md" value="<?=$this->escape($personnelmember['email'])?>" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="type">Type beoordelaar</label>
                    <div class="col-md-4">
                        <select id="type" name="type" class="form-control">
                            <option selected value="<?=$this->e($personnelmember['type'])?>">
                                <?php if ($personnelmember['type'] == 1) { ?>Beoordelaar<?php } ?>
                                <?php if ($personnelmember['type'] == 2) { ?>Beheerder<?php } ?>
                            </option>
                            <option disabled>──────────</option>
                            <option value="1">Beoordelaar</option>
                            <option value="2">Beheerder</option>
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