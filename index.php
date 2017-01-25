<?php
    /**
     * CONTROLLER
     */
    require("vendor/autoload.php");
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
    $router->before('GET|POST', '/assignment/.*', function() {
        session_start();
        if (!isset($_SESSION['user'])) {
            getRedirect("/login");
            exit();
        }
    });

    $router->get("/", function(){
            getRedirect("/login");
    });

    $router->get("/login/", function (){
        echo getTemplates()->render("login::login", ["title" => "Hofstad | Inloggen"]);
    });

    $router->post("/login/", function (){
        $db = getDatabase();
       if(check_login($db, $_POST['username'], $_POST['password'])){
           session_start();
           $_SESSION['user'] = $_POST['username'];
           $userinfo = getUserInfo($db, $_POST['username']);
           $_SESSION['class'] = $userinfo["class"];
           $_SESSION['name'] = $userinfo["name"];
           getRedirect("/assignment/");
       } else {
           getRedirect("/login/?failed=true");
       }
    });

    $router->get('/logout/', function (){
            session_start();
            session_destroy();
            getRedirect("/login/?logged_out=true");
        });

    $router->get("/register/", function (){
        echo getTemplates()->render("login::register", ["title" => "Hofstad | Registreren"]);
    });

    $router->post("/register/", function(){
        $db = getDatabase();
        if(set_initial_password($db, $_POST["username"], $_POST["password"])){
            getRedirect("/login/?registration=true");
        } else {
            getRedirect("/register/?failed=true");
        }
    });

    $router->run();