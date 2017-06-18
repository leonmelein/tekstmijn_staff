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
                    <label class="col-md-4 control-label" for="firstname">Voornaam</label>
                    <div class="col-md-4">
                        <input id="firstname" name="firstname" type="text" placeholder="" class="form-control input-md" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="prefix">Tussenvoegsel</label>
                    <div class="col-md-4">
                        <input id="prefix" name="prefix" type="text" placeholder="" class="form-control input-md">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="lastname">Achternaam</label>
                    <div class="col-md-4">
                        <input id="lastname" name="lastname" type="text" placeholder="" class="form-control input-md" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="email">Emailadres</label>
                    <div class="col-md-4">
                        <input id="email" name="email" type="email" placeholder="" class="form-control input-md" required="">
                    </div>
                </div>

                <!-- Button -->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="opslaan"></label>
                    <div class="col-md-4">
                        <button id="opslaan" name="opslaan" class="btn btn-primary">Toevoegen</button>
                    </div>
                </div>

            </fieldset>
        </form>
    </div>
</div>