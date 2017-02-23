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
                                    <div class="col-md-3">
                                        Spelling
                                    </div>
                                    <div class="col-md-3">
                                        Stijl
                                    </div>
                                    <div class="col-md-3">
                                        Vorm
                                    </div>
                                    <div class="col-md-3">
                                        Opmerkingen
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>131457</td>
                            <td>Reinard van Dalen</td>
                            <td colspan="4">
                                <div class="row">
                                    <div class="col-md-3">
                                        <input name="grade_Spelling" type="number" placeholder="8,0" min="1.0" max="10.0" step="0.1" class="form-control input-md">
                                    </div>
                                    <div class="col-md-3">
                                        <input name="grade_Stijl" type="number" placeholder="8,0" min="1.0" max="10.0" step="0.1" class="form-control input-md">
                                    </div>
                                    <div class="col-md-3">
                                        <input name="grade_Vorm" type="number" placeholder="8,0" min="1.0" max="10.0" step="0.1" class="form-control input-md">
                                    </div>
                                    <div class="col-md-3">
                                        <button id="add_button" type="button" onclick="addPencil()" class="btn btn-default"><i class="glyphicon glyphicon-pencil"></i></button>
                                    </div>
                                </div>
                                <div id="content">
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
