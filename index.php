<?php
    /**
     * CONTROLLER
     */
    require("vendor/autoload.php");
    require("model/index.php");
    use BootPress\Bootstrap\v3\Component as Bootstrap;

    function getDatabase(){
        $database = new medoo([
            'database_type' => 'mysql',
            'database_name' => 'hofstad',
            'server' => 'srv-01.reinardvandalen.nl',
            'username' => 'hofstad',
            'password' => 'LR_hdh4@26', // TODO: Move to config file?
            'charset' => 'utf8'
        ]);
        return $database;
    }

    function getTemplates(){
        $templates = new League\Plates\Engine('view', 'tpl');
        $templates->addFolder("login", "view/login");
        return $templates;
    }

    function getRedirect($url, $statusCode = 303)
    {
        header('Location: ' . $url, true, $statusCode);
        die();
    }

    function getBootstrap(){
        return new Bootstrap;
    }

    $router = new \Bramus\Router\Router();


    /*
     * Authentication
     * - Provides routes for logging on and off, as well as registering a new account.
     */

    //  Authentication check: check if each request has a user ID set in session.
    //  TODO: use tokens?
    $router->before('GET|POST', '/account/.*', function() {
        session_start("staff");
        if (!isset($_SESSION['staff_user'])) {
            getRedirect("/staff/login");
            exit();
        }
    });

    $router->get("/", function(){
            getRedirect("/staff/login");
    });

    $router->get("login/", function (){
        echo getTemplates()->render("login::login", ["title" => "Hofstad | Inloggen"]);
    });

    $router->post("login/", function (){
        $db = getDatabase();
       //if(check_login($db, $_POST['username'], $_POST['password'])){
       if(true == true){
           session_start("staff");
           $_SESSION['staff_user'] = $_POST['username'];
           $_SESSION['staff_name'] = "D. Docent";
//           $userinfo = getUserInfo($db, $_POST['username']);
//           $_SESSION['class'] = $userinfo["class"];
//           $_SESSION['name'] = $userinfo["name"];
           getRedirect("../account/");
       } else {
           getRedirect("/staff/login/?failed=true");
       }
    });

    $router->get('logout/', function (){
            session_start("staff");
            session_destroy();
            getRedirect("../login/?logged_out=true");
        });

    $router->get("register/", function (){
        echo getTemplates()->render("login::register", ["title" => "Hofstad | Registreren"]);
    });

    $router->post("register/", function(){
        $db = getDatabase();
        if(set_initial_password($db, $_POST["username"], $_POST["password"])){
            getRedirect("/login/?registration=true");
        } else {
            getRedirect("/register/?failed=true");
        }
    });

    $router->get("account/", function (){
        $bp = getBootstrap();
        session_start("staff");
        // Generate menu
        $menu = generateMenu($bp, ["active" => "Mijn account", "align" => "stacked"]);
        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["staff_name"] => "../account/", "Mijn account" => "#"]);

        echo getTemplates()->render("login::account", ["title" => "Hofstad | Mijn account",
            "page_title" => "Mijn account", "menu" => $menu, "breadcrumbs" => $breadcrumbs]);
    });


    $router->run();