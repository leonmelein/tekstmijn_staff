<?php $this->layout('main_layout', ['title' => $title, 'pageJS' => $page_js]); ?>
<div class="row">
    <div class="col-md-12">
        <?php echo $breadcrumbs; ?>
        <?php if($_GET["download_generated"] == "true"): ?>
            <div class="alert alert-success alert-dismissable" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Sluiten"><span aria-hidden="true">&times;</span></button>
                <strong>Gedownload.</strong> Uw beoordelingspakket staat voor u klaar.
            </div>
        <?php endif; ?>

        <?php if($_GET["password_changed"] == "false"): ?>
            <div class="alert alert-danger alert-dismissable" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Sluiten"><span aria-hidden="true">&times;</span></button>
                <b>Oeps.</b> We konden helaas het pakket niet voor u genereren. Probeer het opnieuw of vraag uw sectievoorzitter.
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
        <h3 class="page_title"><?php echo $page_subtitle; ?></h3>
        <?php echo $tabs; ?>
        <div class="tab-content">
            <div id="ingeleverd" class="tab-pane active">
                <?php echo $table_ingeleverd; ?>
            </div>
            <div id="telaat" class="tab-pane">
                <?php echo $table_telaat; ?>
            </div>
            <div id="nietingeleverd" class="tab-pane">
                <?php echo $table_nietingeleverd; ?>
            </div>
            <div id="beoordelen" class="tab-pane">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th colspan="2">Leerlinggegevens</th>
                            <th colspan="4">Beoordelen</th>
                        </tr>
                        <tr>
                            <th>Leerlingnummer</th>
                            <th>Naam</th>
                            <th colspan="4">
                                <div class="row">
                                    <div class="col-md-6">
                                        Score
                                    </div>
                                    <div class="col-md-3 text-center">
                                        Opmerkingen
                                    </div>
                                    <div class="col-md-3 text-center">
                                        Opslaan
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $gradingtable; ?>
                    </tbody>
                </table>
                <div class="row">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <button type="button" onclick="saveAll('<?php echo $gradingarray; ?>')" class="btn btn-default"><i class="glyphicon glyphicon-floppy-open"></i> Alle beoordelingen opslaan</button>
                    </div>
                </div>
            </div>
            </br></br>
        </div>
    </div>
</div>
