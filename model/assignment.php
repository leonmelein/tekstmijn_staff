<?php

/**
 * Created by PhpStorm.
 * User: leon
 * Date: 28-06-17
 * Time: 11:48
 */
class assignment extends model {
    function overview(){
        $this->get_session();
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "/staff/account/", "Opdrachten" => "#"]);
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/assignment/", "align" => "stacked"], $_SESSION['type']);

        echo $this->templates->render("status::overview", ["title" => "Tekstmijn | Opdrachten",
            "page_title" => "Opdrachten",
            "menu" => $menu, "breadcrumbs" => $breadcrumbs,
        ]);
    }
}