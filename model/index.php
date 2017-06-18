<?php

function generateTable($bp, $columns, $data, $options = null, $format = "", $classes = "class=responsive hover"){
    $table = $bp->table->open($classes);
    $table .= $bp->table->head();
    foreach ($columns as $column){
        $table .= $bp->table->cell('', $column[0]);
    }

    if (isset($options)){
        foreach ($options as $option){
            $table .= $bp->table->cell('', $option[2]);
        }
    }

    foreach ($data as $item) {
        $table .= $bp->table->row();

        foreach ($columns as $column) {
            if(empty($format)){
                $table .= $bp->table->cell('', $item[$column[1]]);
            } else {
                $table .= $bp->table->cell('', sprintf($format,
                    $item['id'],
                    $item[$column[1]]
                ));
            }

        }

        if (isset($options)) {
            foreach ($options as $option) {
                $table .= $bp->table->cell('', sprintf($option[0], $item["id"], $item["id"]
                ));
            }
        }
    }

    $table .= $bp->table->close();
    return $table;
}

function generateMenu($bp, $active, $type){
    $item = [
        "Inzendingen" => "<i class='glyphicon glyphicon-pencil' aria-hidden='true'></i>&nbsp;&nbsp;Inzendingen",
        "Leerlingen" => "<i class='glyphicon glyphicon-th-list' aria-hidden='true'></i>&nbsp;&nbsp;Leerlingen",
        "Beoordelen" => "<i class='glyphicon glyphicon-pencil' aria-hidden='true'></i>&nbsp;&nbsp;Beoordelen",
        "Status" => "<i class='glyphicon glyphicon-stats' aria-hidden=true'></i>&nbsp;&nbsp;Status",
        "Administratie" => "<i class='glyphicon glyphicon-cog' aria-hidden='true'></i>&nbsp;&nbsp;Administratie",
        "Mijn account" => "<i class='glyphicon glyphicon-user' aria-hidden='true'></i>&nbsp;&nbsp;Mijn account",
    ];

    if ($type == 0) {
        $menu_panel = '<div class="panel panel-default">
                           <div class="panel-heading menu-heading">
                                <h4>Menu</h4>
                           </div>
                           <div class="panel-body">%s</div>
                        </div>';

        $menu_options = [$item["Inzendingen"] => "/staff/submissions/", $item["Leerlingen"] => "/staff/classes/", $item["Mijn account"] => "/staff/account/"];
    }
    elseif ($type == 1) {
        $menu_panel = '<div class="panel panel-default">
                           <div class="panel-heading menu-heading">
                                <h4>Menu</h4>
                           </div>
                           <div class="panel-body">%s</div>
                        </div>';

        $menu_options = [$item["Beoordelen"] => "/staff/review/", $item["Mijn account"] => "/staff/account/"];
    }
    elseif ($type == 2) {
        $menu_panel = '<div class="panel panel-default">
                           <div class="panel-heading menu-heading">
                                <h4>Menu</h4>
                           </div>
                           <div class="panel-body">%s</div>
                        </div>';

        $menu_options = [
            $item["Beoordelen"] => "/staff/review/",
            $item["Status"] => "/staff/status/",
            $item["Administratie"] => "/staff/administration/",
            $item["Mijn account"] => "/staff/account/",
        ];
    }
    return sprintf($menu_panel, $bp->pills($menu_options, $active));
}

function generateBreadcrumbs($bp, $path){
    return $bp->breadcrumbs($path);
}

function generateTabs($bp, $tabsarray, $active = 'Info'){
    return $bp->tabs($tabsarray, array(
        'active' => $active,
        'toggle' => "tab",
    ));
}

function getRedirect($url, $statusCode = 303) {
    header('Location: ' . $url, true, $statusCode);
    die();
}

function generateOptions($array){
    $option = "<option value='%s'>%s</option>";
    $options = "";

    foreach ($array as $key => $value) {
        $options .= sprintf($option, $value["id"], $value["name"]) . "\n";
    }

    return $options;
}