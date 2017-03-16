<?php $this->layout('main_layout', ['title' => $title, 'pageJS' => $page_js]); ?>
<div class="row">
    <div class="col-md-12">
        <?php echo $breadcrumbs; ?>
        <?php if($_GET["password_changed"] == "true"): ?>
            <div class="alert alert-success alert-dismissable" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Sluiten"><span aria-hidden="true">&times;</span></button>
                <strong>Gewijzigd.</strong> Uw wachtwoord is gewijzigd.
            </div>
        <?php endif; ?>

        <?php if($_GET["password_changed"] == "false"): ?>
            <div class="alert alert-danger alert-dismissable" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Sluiten"><span aria-hidden="true">&times;</span></button>
                <b>Wachtwoord niet gewijzigd.</b> We konden uw wachtwoord niet wijzigen. Probeer het opnieuw of vraag uw sectievoorzitter.
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <?php echo $menu; ?>
    </div>
    <div class="col-md-9">
        <h1 class="page_title"><?php echo $page_title; ?></h1>
        <form class="form-horizontal" id="register" method="post" action="/staff/account/">
            <fieldset>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="name">Naam</label>
                    <div class="col-md-4">
                        <p class="form-control-static"><?=$this->e($name)?></p>
                    </div>
                </div>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="name">E-mailadres</label>
                    <div class="col-md-4">
                        <p class="form-control-static"><?=$this->e($email)?></p>
                        <input type="hidden" name="username" value="<?=$this->e($email)?>"/>
                    </div>
                </div>

                <!-- Password input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="password">Wachtwoord</label>
                    <div class="col-md-4">
                        <input id="password" name="password" type="password" placeholder="Wachtwoord" class="form-control input-md" required="">
                    </div>
                </div>

                <!-- Password input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="password_confirm"></label>
                    <div class="col-md-4">
                        <input id="password_confirm" name="password_confirm" type="password" placeholder="Bevestig wachtwoord" class="form-control input-md" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="submit"></label>
                    <div class="col-md-4">
                        <button id="submit" name="submit" type="submit" class="btn btn-primary" style="width: 100%">Wachtwoord wijzigen</button>
                    </div>
                </div>

            </fieldset>
        </form>
    </div>
</div>
