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
        <form class="form-horizontal" method="post" action="../add/">
            <fieldset>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="studentid">Studentnummer</label>
                    <div class="col-md-4">
                        <input id="studentid" name="studentid" type="text" placeholder="" class="form-control input-md" value="<?=$this->escape($student['id'])?>" required="">
                    </div>
                </div>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="firstname">Voornaam</label>
                    <div class="col-md-4">
                        <input id="firstname" name="firstname" type="text" placeholder="" class="form-control input-md" value="<?=$this->escape($student['firstname'])?>" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="prefix">Tussenvoegsel</label>
                    <div class="col-md-4">
                        <input id="prefix" name="prefix" type="text" placeholder="" class="form-control input-md" value="<?=$this->escape($student['prefix'])?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="lastname">Achternaam</label>
                    <div class="col-md-4">
                        <input id="lastname" name="lastname" type="text" placeholder="" class="form-control input-md" value="<?=$this->escape($student['lastname'])?>" required="">
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-md-4 control-label" for="birthday">Geboortedatum</label>
                    <div class='col-md-4 input-group date' id='datetimepicker2'>
                        <input type='text' class="form-control input-md" name="birthday" id="birthday" value="<?=$this->escape($student['birthday'])?>" required />
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="class">Klas</label>
                    <div class="col-md-4">
                        <select id="class" name="class" class="form-control">
                            <?php if (isset($student['class_id'])): ?>
                                <option selected value="<?=$this->escape($student['class_id'])?>"><?=$this->escape($student['name'])?></option>
                                <option disabled>──────────</option>
                            <?php endif; ?>
                            <?php echo $classes; ?>
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