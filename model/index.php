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
    if ($type == 0) {
        $menu_panel = '<div class="panel panel-default">
                           <div class="panel-heading menu-heading">
                                <h4>Menu</h4>
                           </div>
                           <div class="panel-body">%s</div>
                        </div>';

        $menu_options = ["Inzendingen" => "/staff/submissions/", "Leerlingen" => "/staff/classes/", "Mijn account" => "/staff/account/"];
    }
    elseif ($type == 1) {
        $menu_panel = '<div class="panel panel-default">
                           <div class="panel-heading menu-heading">
                                <h4>Menu</h4>
                           </div>
                           <div class="panel-body">%s</div>
                        </div>';

        $menu_options = ["Beoordelen" => "/staff/review/", "Mijn account" => "/staff/account/"];
    }
    elseif ($type == 2) {
        $menu_panel = '<div class="panel panel-default">
                           <div class="panel-heading menu-heading">
                                <h4>Menu</h4>
                           </div>
                           <div class="panel-body">%s</div>
                        </div>';

        $menu_options = [
            "<i class='glyphicon glyphicon-pencil' aria-hidden=true'></i>&nbsp;&nbsp;Beoordelen" => "/staff/review/",
            "<i class='glyphicon glyphicon-stats' aria-hidden=true'></i>&nbsp;&nbsp;Status" => "/staff/status/",
            "<i class='glyphicon glyphicon-cog' aria-hidden=true'></i>&nbsp;&nbsp;Administratie" => "/staff/administration/",
            "<i class='glyphicon glyphicon-user' aria-hidden=true'></i>&nbsp;&nbsp;Mijn account" => "/staff/account/",
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