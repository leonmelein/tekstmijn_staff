<?php
use BootPress\Bootstrap\v3\Component as Bootstrap;

/**
 * Model base
 *
 * Provides all basic tools for building models
 */


class model
{

    public function __construct()
    {
        $db_settings = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/config/config.ini");
        $this->database = new medoo([
            'database_type' => 'mysql',
            'database_name' => $db_settings['database_name'],
            'server' => $db_settings['server'],
            'username' => $db_settings['username'],
            'password' => $db_settings['password'],
            'charset' => 'utf8',
            'command' => [
                'SET SQL_MODE=ANSI_QUOTES'
            ]
        ]);
        $this->bootstrap = new Bootstrap;

        // Template setup
        $templates = new League\Plates\Engine('view', 'tpl');
        $templates->addFolder("login", "view/login");
        $templates->addFolder("classes", "view/classes");
        $templates->addFolder("submissions", "view/submissions");
        $templates->addFolder("review", "view/review");
        $templates->addFolder("status", "view/status");
        $templates->addFolder("analysis", "view/analysis");
        $templates->addFolder("administration", "view/administration");
        $templates->addFolder("admin_classes", "view/administration/classes");
        $templates->addFolder("admin_students", "view/administration/students");
        $templates->addFolder("admin_personnel", "view/administration/personnel");
        $templates->addFolder("admin_reviewers", "view/administration/reviewers");
        $this->templates = $templates;
    }

    public function table($bp, $columns, $data, $options = null, $format = "", $classes = "class=responsive hover"){
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

    public function menu($bp, $active, $type){
        $item = [
            "Inzendingen" => "<i class='glyphicon glyphicon-pencil' aria-hidden='true'></i>&nbsp;&nbsp;Inzendingen",
            "Leerlingen" => "<i class='glyphicon glyphicon-th-list' aria-hidden='true'></i>&nbsp;&nbsp;Leerlingen",
            "Beoordelen" => "<i class='glyphicon glyphicon-pencil' aria-hidden='true'></i>&nbsp;&nbsp;Beoordelen",
            "Analyse" => "<i class='glyphicon glyphicon-stats' aria-hidden=true'></i>&nbsp;&nbsp;Analyse",
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
                $item["Analyse"] => "/staff/analysis/",
                $item["Administratie"] => "/staff/administration/",
                $item["Mijn account"] => "/staff/account/",
            ];
        }
        return sprintf($menu_panel, $bp->pills($menu_options, $active));
    }

    public function breadcrumbs($bp, $path){
        return $bp->breadcrumbs($path);
    }

    public function tabs($bp, $tabsarray, $active = 'Info'){
        return $bp->tabs($tabsarray, array(
            'active' => $active,
            'toggle' => "tab",
        ));
    }

    public function redirect($url, $statusCode = 303) {
        header('Location: ' . $url, true, $statusCode);
        die();
    }

    public function options($array){
        $option = "<option value='%s'>%s</option>";
        $options = "";

        foreach ($array as $key => $value) {
            $options .= sprintf($option, $value["id"], $value["name"]) . "\n";
        }

        return $options;
    }

    public function get_session(){
        session_start("staff");
    }

}