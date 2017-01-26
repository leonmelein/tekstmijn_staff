<?php $this->layout('main_layout', ['title' => $title]); ?>
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
        <form class="form-horizontal" id="register" method="post" action="/staff/register/">
            <fieldset>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="name">Naam</label>
                    <div class="col-md-4">
                        <p class="form-control-static">De Docent</p>
                    </div>
                </div>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="name">E-mailadres</label>
                    <div class="col-md-4">
                        <p class="form-control-static">de.docent@school.nl</p>
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
