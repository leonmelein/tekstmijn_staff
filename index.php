<?php
    /**
     * CONTROLLER
     */
    require("vendor/autoload.php");
    require("model/index.php");
    require("model/login.php");
    require("model/students.php");
    require("model/submissions.php");
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
        $templates->addFolder("submissions", "view/submissions");
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
        $info = getUserInfo($db, $_POST['username']);

        if ($_POST['password_forgotten'] == 1) {
            $sanitized_email = filter_var($_POST['username'], FILTER_SANITIZE_EMAIL);
            if (filter_var($sanitized_email, FILTER_VALIDATE_EMAIL)) {
                set_setup_token($db, $sanitized_email);

                // Password forgotten
                $mail = new PHPMailer;
                $mail->setFrom('hofstad@thesociallions.nl', 'Project Hofstad');
                $mail->addAddress($sanitized_email);
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = 'Project Hofstad - Wachtwoord wijzigen';
                $token = get_setup_token($db, $sanitized_email);
                // Retrieve the email template required
                $message = file_get_contents('/volume1/hofstad/staff/assets/mail/reset.html');
                // Replace the % with the actual information
                $message = str_replace('%name%', $info['name'], $message);
                $message = str_replace('%link%', $token, $message);


                $mail->Body    = $message;
                $mail->AltBody = 'Zet HTML aan in uw e-mailclient.';

                if(!$mail->send()) {
                    getRedirect("/staff/login/?reset=false");
                } else {
                    echo $mail->ErrorInfo;
                    getRedirect("/staff/login/?reset=true");
                }
            }

        } else {
            // Regular login
            if(check_login($db, $_POST['username'], $_POST['password'])){
                session_start("staff");
                $_SESSION['staff_id'] = $info["id"];
                $_SESSION["staff_email"] = $_POST["username"];
                $_SESSION['staff_name'] = $info["name"];
                getRedirect("../classes/");
            } else {
                getRedirect("/staff/login/?failed=true");
            }
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

    $router->get("submissions/", function (){
        session_start("staff");
        $bp = getBootstrap();
        $db = getDatabase();

        $menu = generateMenu($bp, ["active" => "Inzendingen", "align" => "stacked"]);
        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["staff_name"] => "../account/", "Inzendingen" => "#"]);

        $classes = getSubmissionsForStaff($db, $_SESSION["staff_id"]);
        $columns = [
            ["Jaar", "year"],
            ["Niveau", "level"],
            ["Klas", "class"]
        ];
        $table = generateTable($bp, $columns, $classes, null, '<a href="%s/">%s</a>');
        echo getTemplates()->render("submissions::index", ["title" => "Hofstad | Klassen",
            "page_title" => "Inzendingen", "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table" => $table]);
    });

    $router->get("submissions/(\d+)/", function ($class_id){
        session_start("staff");
        $bp = getBootstrap();
        $db = getDatabase();

        $class = getClassName($db, $class_id);
        $menu = generateMenu($bp, ["active" => "Inzendingen", "align" => "stacked"]);
        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["staff_name"] => "/staff/account/", "Inzendingen" => "/staff/submissions/",
            sprintf("Klas %s", $class) => "#"]);

        $students =  getAssignmentsforClass($db, $class_id);
        $columns = [
            ["Titel", "title"],
            ["Status", "status"],
            ["Startdatum", "start_date"],
            ["Uiterste inleverdatum", "end_date"],
        ];

        $table = generateTable($bp, $columns, $students, null, '<a href="%s/">%s</a>');
        echo getTemplates()->render("submissions::classes", ["title" => "Hofstad | Inzendingen",
            "page_title" => "Inzendingen", "page_subtitle" => sprintf("Klas %s", $class),  "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table" => $table]);
    });

    $router->get("submissions/(\d+)/([a-z0-9_-]+)/", function ($class_id, $assignment_id){
        session_start("staff");
        $bp = getBootstrap();
        $db = getDatabase();

        $title = getAssignmentName($db, $assignment_id);
        $class = getClassName($db, $class_id);
        $tabs = generateTabs($bp, ["Ingeleverd" => "#ingeleverd", "Te laat" => "#telaat", "Niet ingeleverd" => "#nietingeleverd"], 'Ingeleverd');
        $menu = generateMenu($bp, ["active" => "Inzendingen", "align" => "stacked"]);
        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["staff_name"] => "/staff/account/", "Inzendingen" => "/staff/submissions/", sprintf("Klas %s", $class) => "/staff/submissions/$class_id",
            $title => "#"]);

        $students =  getSubmissionsForAssignment($db, $class_id, $assignment_id);
        $columns = [
            ["Leerlingnummer", "student_id"],
            ["Naam", "name"],
            ["Inleverdatum", "submission_date"],
            ["Aantal pogingen", "submission_count"],
        ];

        $table_ingeleverd = generateTable($bp, $columns, $students, null, '<a href="%s/">%s</a>');

        $students = getSubmissionsForAssignmentToLate($db, $class_id, $assignment_id);
        $table_telaat = generateTable($bp, $columns, $students, null, '<a href="%s/">%s</a>');

        $students = getSubmissionsForAssignmentNoShow($db, $class_id, $assignment_id);
        $columns = [
            ["Leerlingnummer", "id"],
            ["Naam", "name"],
        ];
        $table_nietingeleverd = generateTable($bp, $columns, $students);
        echo getTemplates()->render("submissions::submissions", ["title" => "Hofstad | Inzendingen",
            "page_title" => "Inzendingen", "page_subtitle" => $title, "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table_ingeleverd" => $table_ingeleverd, "table_telaat" => $table_telaat, "table_nietingeleverd" => $table_nietingeleverd, "tabs" => $tabs]);
    });

    $router->get("submissions/(\d+)/([a-z0-9_-]+)/(\d+)", function ($class_id, $assignment_id, $submission_id) {
        session_start("staff");
        $bp = getBootstrap();
        $db = getDatabase();

        $title = "Inzendingen";
        $assignment_name = getAssignmentName($db, $assignment_id);
        $student_name = getStudentName($db, $submission_id);
        $subtitle = sprintf("%s : %s", $assignment_name, $student_name);
        $class = getClassName($db, $class_id);
        $tabs = generateTabs($bp, ["De inzending" => "#deinzending", "Beoordelen" => "#beoordelen"], 'De inzending');
        $menu = generateMenu($bp, ["active" => "Inzendingen", "align" => "stacked"]);
        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["staff_name"] => "/staff/account/", "Inzendingen" => "/staff/submissions/", sprintf("Klas %s", $class) => "/staff/submissions/$class_id", $assignment_name => "/staff/submissions/$class_id/$assignment_id", $title => "#"]);

        $submission_info = getSubmissionInfo($db, $submission_id);
        $page_js = "/staff/vendor/application/add_field.js";

        echo getTemplates()->render("submissions::grading", ["title" => "Hofstad | Inzendingen",
            "page_title" => $title, "page_subtitle" => $subtitle, "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "tabs" => $tabs, "page_js" => $page_js,
            "submission_date" => $submission_info["submission_date"],
            "submission_file" => $submission_info["submission_file"],
            "submission_count" => $submission_info["submission_count"],
            "submission_originalfile" => $submission_info["submission_originalfile"],
        ]);
    });

    $router->post("/submissions/(.*)/grade", function () {
        $grading = $_POST;
        print_r($grading);
    });

    $router->get("/reset_password/", function (){
        $db = getDatabase();
        $registration = getResetInfo($db, $_GET["token"]);

        if($registration){
            echo getTemplates()->render("login::reset",
                ["title" => "Hofstad | Registreren",
                    "name" => $registration["name"],
                    "email" => $registration["email"],
                    "page_js" => "../vendor/application/register_validate.js"
                ]
            );
        } else {
            getRedirect("/staff/login/?pwd_reset=false");
        }

    });

    $router->post("/reset_password/", function(){
        $db = getDatabase();
        if(change_password($db, $_POST["username"], $_POST["password"])){
            getRedirect("/staff/login/?pwd_reset=true");
        } else {
            getRedirect("/staff/reset_password/?failed=true");
        }
    });

    $router->run();