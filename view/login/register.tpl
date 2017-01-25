<?php $this->layout('main_layout_public', ['title' => $title, 'pageJS' => $page_js]); ?>
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-8">
        <?php
        if($_GET["failed"] == "true") {
            echo '<div class="alert alert-danger alert-dismissable" role="alert">
                                   <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                   <strong>Mislukt.</strong> We konden je wachtwoord niet instellen. Probeer het opnieuw of vraag je docent.
                              </div>';
        }
        ?>
    </div>
    <div class="col-md-2"></div>
</div>

<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-8">
        <h1 class="page_title">Registreren</h1>
        <form class="form-horizontal" id="register" method="post" action="/register/">
            <fieldset>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="username">Leerlingnummer</label>
                    <div class="col-md-4">
                        <input id="username" name="username" type="text" placeholder="Leerlingnummer" class="form-control input-md" required="" autofocus>

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
                        <button id="submit" name="submit" type="submit" class="btn btn-primary" style="width: 100%">Instellen</button>
                    </div>
                </div>

            </fieldset>
        </form>
    </div>
    <div class="col-md-2"></div>
</div>
