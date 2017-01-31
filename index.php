<?php
    /**
     * CONTROLLER
     */
    require("vendor/autoload.php");
    require("model/index.php");
    require("model/login.php");
    require("model/students.php");
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
        $templates->addFolder("classes", "view/classes");
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
    $router->before('GET|POST', 'account/', function() {
        session_start("staff");
        if (!isset($_SESSION['staff_id'])) {
            getRedirect("/staff/login");
            exit();
        }
    });

    // Prerouting check for initial setup
    $router->before('GET', 'register/', function() {
        if (!isset($_GET["token"])) {
            getRedirect("/staff/login?failed_registration=true");
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
       if(check_login($db, $_POST['username'], $_POST['password'])){
           session_start("staff");
           $info = getUserInfo($db, $_POST['username']);
           $_SESSION['staff_id'] = $info["id"];
           $_SESSION["staff_email"] = $_POST["username"];
           $_SESSION['staff_name'] = $info["name"];
           getRedirect("../classes/");
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
        $db = getDatabase();
        $registration = getRegistrationInfo($db, $_GET["token"]);

        if($registration){
            echo getTemplates()->render("login::register",
                                        ["title" => "Hofstad | Registreren",
                                            "name" => $registration["name"],
                                            "email" => $registration["email"],
                                            "page_js" => "../vendor/application/register_validate.js"
                                        ]
            );
        } else {
            getRedirect("/staff/login/?failed_registration=true");
        }


    });

    $router->post("register/", function(){
        $db = getDatabase();
        if(set_initial_password($db, $_POST["username"], $_POST["password"])){
            getRedirect("/staff/login/?registration=true");
        } else {
            getRedirect("/staff/register/?failed=true");
        }
    });

    $router->get("account/", function (){
        $bp = getBootstrap();

        session_start("staff");
        // Generate menu
        $menu = generateMenu($bp, ["active" => "Mijn account", "align" => "stacked"]);
        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["staff_name"] => "../account/", "Mijn account" => "#"]);

        echo getTemplates()->render("login::account", ["title" => "Hofstad | Mijn account",
            "page_title" => "Mijn account", "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "name" => $_SESSION["staff_name"], "email" => $_SESSION["staff_email"], "page_js" => "../vendor/application/register_validate.js"]);
    });

    $router->post("account/", function(){
        $db = getDatabase();
        if(change_password($db, $_POST["username"], $_POST["password"])){
            getRedirect("/staff/account/?password_changed=true");
        } else {
            getRedirect("/staff/register/?password_changed=true");
        }
    });

    $router->get("classes/", function (){
        session_start("staff");
        $bp = getBootstrap();
        $db = getDatabase();

        $menu = generateMenu($bp, ["active" => "Klassen", "align" => "stacked"]);
        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["staff_name"] => "../account/", "Klassen" => "#"]);

        $classes = getClassesForStaff($db, $_SESSION["staff_id"]);
        $columns = [
            ["Naam", "name"],
            ["Niveau", "level"],
            ["Jaar", "year"]
        ];
        $table = generateTable($bp, $columns, $classes, null, '<a href="%s/">%s</a>');
        echo getTemplates()->render("classes::index", ["title" => "Hofstad | Klassen",
            "page_title" => "Klassen", "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table" => $table]);
    });

    $router->get("classes/(\d+)/", function ($class_id){
        session_start("staff");
        $bp = getBootstrap();
        $db = getDatabase();

        $name = sprintf("Klas %s", getClassName($db, $class_id));
        $menu = generateMenu($bp, ["active" => "Klassen", "align" => "stacked"]);
        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["staff_name"] => "/staff/account/", "Klassen" => "/staff/classes/",
            $name => "#"]);

        $students =  getClassStudents($db, $class_id);
        $columns = [
            ["#", "id"],
            ["Naam", "name"]
        ];
        $options = [
            ["<a class='btn btn-primary pull-right' id='%s' onclick='reset_student_pwd(%s, self.document)'>Wachtwoord resetten</a>", "id", ""]
        ];

        $table = generateTable($bp, $columns, $students, $options);
        echo getTemplates()->render("classes::class", ["title" => "Hofstad | Klassen",
            "page_title" => $name, "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table" => $table, "page_js" => "/staff/vendor/application/reset_pwd_students.js"]);
    });

    $router->get("reset/(\d+)/", function($student_id){
       if(resetStudentPassword(getDatabase(), $student_id)){
           echo '{"status": "success"}';
       } else {
           echo '{"status": "failure"}';
       }
    });


    $router->run();