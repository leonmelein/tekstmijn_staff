<?php
    /**
     * CONTROLLER
     */
    require("vendor/autoload.php");
    require("model/model.php");
    require("model/index.php");
    require("model/login.php");
    require("model/students.php");
    require("model/submissions.php");
    require("model/download.php");
    require("model/review.php");
    require("model/administration.php");
    require("model/debug.php");
    require("model/newstatus.php");
    require("model/classroom.php");
    require("model/auth.php");
    use BootPress\Bootstrap\v3\Component as Bootstrap;

    // Reroute HTTP traffic to HTTPS
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        if(!headers_sent()) {
            header("Status: 301 Moved Permanently");
            header(sprintf(
                'Location: https://%s%s',
                $_SERVER['HTTP_HOST'],
                $_SERVER['REQUEST_URI']
            ));
            exit();
        }
    }

    // Database setup
    $db_settings = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/config/config.ini");
    $db = new medoo([
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

    // Template setup
    $templates = new League\Plates\Engine('view', 'tpl');
    $templates->addFolder("login", "view/login");
    $templates->addFolder("classes", "view/classes");
    $templates->addFolder("submissions", "view/submissions");
    $templates->addFolder("review", "view/review");
    $templates->addFolder("status", "view/status");
    $templates->addFolder("administration", "view/administration");
    $templates->addFolder("admin_classes", "view/administration/classes");
    $templates->addFolder("admin_students", "view/administration/students");
    $templates->addFolder("admin_personnel", "view/administration/personnel");
    $templates->addFolder("admin_reviewers", "view/administration/reviewers");
    $bp = new Bootstrap;

    // Initiate router
    $router = new \Bramus\Router\Router();

    /*
     * Authentication
     * - Provides routes for logging on and off, as well as registering a new account.
     */

    //  Authentication check: check if each request has a user ID set in session.
    //  TODO: use tokens?
    $router->before('GET|POST', '/account/', 'auth@checkLogin');
    $router->before('GET|POST', '/classes/', 'auth@checkLogin');
    $router->before('GET|POST', '/classes/.*', 'auth@checkLogin');
    $router->before('GET|POST', '/submissions/', 'auth@checkLogin');
    $router->before('GET|POST', '/submissions/.*', 'auth@checkLogin');
    $router->before('GET|POST', '/status/', 'auth@checkLogin');
    $router->before('GET|POST', '/status/.*', 'auth@checkLogin');
    $router->before('GET|POST', '/review/','auth@checkLogin');
    $router->before('GET|POST', '/review/.*', 'auth@checkLogin');
    $router->before('GET|POST', '/administration/', 'auth@checkLogin');
    $router->before('GET|POST', '/administration/.*', 'auth@checkLogin');
    $router->before('GET', '/register/', 'auth@checkToken');

    /*
     * Page routers
     * - Provides routes to the individual parts of the system
     */

    $router->get("/", function(){getRedirect("/staff/login");});

    $router->mount('/login', function() use ($router, $templates){
        $router->get("/", function () use ($templates){
            echo $templates->render("login::login", ["title" => "Tekstmijn | Inloggen"]);
        });

        $router->post("/", 'auth@login');
    });

    $router->get('/logout', "auth@logout");

    /*
    * Account
    */

    $router->get("/register", "auth@startRegistration");

    $router->post("/register", "auth@completeRegistration");

    $router->get("/reset_password/", function ()  use ($db, $templates){
        $registration = getResetInfo($db, $_GET["token"]);

        if($registration){
            echo $templates->render("login::reset",
                ["title" => "Tekstmijn | Registreren",
                    "name" => $registration["name"],
                    "email" => $registration["email"],
                    "page_js" => "../vendor/application/register_validate.js"
                ]
            );
        } else {
            getRedirect("/staff/login/?pwd_reset=false");
        }

    });

    $router->post("/reset_password/", function() use ($db) {
        if(change_password($db, $_POST["username"], $_POST["password"])){
            getRedirect("/staff/login/?pwd_reset=true");
        } else {
            getRedirect("/staff/reset_password/?failed=true");
        }
    });

    $router->get("reset/(\d+)/", function($student_id) use ($db){
        if(resetStudentPassword($db, $student_id)){
            echo '{"status": "success"}';
        } else {
            echo '{"status": "failure"}';
        }
    });

    $router->get("account/", function () use ($db, $templates, $bp){
        session_start("staff");
        // Generate menu
        $menu = generateMenu($bp, ["active" => "/staff/account/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["staff_name"] => "../account/", "Mijn account" => "#"]);

        echo $templates->render("login::account", ["title" => "Tekstmijn | Mijn account",
            "page_title" => "Mijn account", "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "name" => $_SESSION["staff_name"], "email" => $_SESSION["staff_email"], "page_js" => "../vendor/application/register_validate.js"]);
    });

    $router->post("account/", function() use ($db){
        if(change_password($db, $_POST["username"], $_POST["password"])){
            getRedirect("/staff/account/?password_changed=true");
        } else {
            getRedirect("/staff/register/?password_changed=true");
        }
    });

    /*
     * Classes
     */

    $router->get("classes/", "classroom@generateTeacherOverview");

    $router->get("classes/(\d+)/", "classroom@generateDetailOverview");

    /*
     * Submissions
     */

    $router->get("submissions/", function () use ($db, $templates, $bp){
        session_start("staff");

        $menu = generateMenu($bp, ["active" => "/staff/submissions/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["staff_name"] => "../account/", "Inzendingen" => "#"]);

        $classes = getSubmissionsForStaff($db, $_SESSION["staff_id"]);
        $columns = [
            ["Klas", "class"],
            ["Niveau", "level"],
            ["Jaar", "year"]
        ];
        $table = generateTable($bp, $columns, $classes, null, '<a href="%s/">%s</a>');
        echo $templates->render("submissions::index", ["title" => "Tekstmijn | Inzendingen",
            "page_title" => "Inzendingen", "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table" => $table]);
    });

    $router->get("submissions/(\d+)/", function ($class_id)  use ($db, $templates, $bp){
        session_start("staff");
        $class = getClassName($db, $class_id);
        $menu = generateMenu($bp, ["active" => "/staff/submissions/", "align" => "stacked"], $_SESSION['type']);
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
        echo $templates->render("submissions::classes", ["title" => "Tekstmijn | Inzendingen",
            "page_title" => "Inzendingen", "page_subtitle" => sprintf("Klas %s", $class),  "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table" => $table]);
    });

    $router->get("submissions/(\d+)/([a-z0-9_-]+)/", function ($class_id, $assignment_id)  use ($db, $templates, $bp){
        session_start("staff");

        $staff_id = $_SESSION['staff_id'];

        $title = getAssignmentName($db, $assignment_id);
        $class = getClassName($db, $class_id);
        $tabs = generateTabs($bp, ["Ingeleverd" => "#ingeleverd", "Te laat" => "#telaat", "Niet ingeleverd" => "#nietingeleverd", "Beoordelen" => "#beoordelen"], 'Ingeleverd');
        $menu = generateMenu($bp, ["active" => "/staff/submissions/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["staff_name"] => "/staff/account/", "Inzendingen" => "/staff/submissions/", sprintf("Klas %s", $class) => "/staff/submissions/$class_id",
            $title => "#"]);

        $students_ingeleverd =  getSubmissionsForAssignment($db, $class_id, $assignment_id);
        $columns = [
            ["Leerlingnummer", "personnel_id"],
            ["Naam", "name"],
            ["Inleverdatum", "submission_date"],
            ["Aantal pogingen", "submission_count"],
        ];

        $table_ingeleverd = generateTable($bp, $columns, $students_ingeleverd, null, '<a href="%s/">%s</a>');

        $students = getSubmissionsForAssignmentToLate($db, $class_id, $assignment_id);
        $table_telaat = generateTable($bp, $columns, $students, null, '<a href="%s/">%s</a>');

        $students = getSubmissionsForAssignmentNoShow($db, $class_id, $assignment_id);
        $columns = [
            ["Leerlingnummer", "id"],
            ["Naam", "name"],
        ];
        $table_nietingeleverd = generateTable($bp, $columns, $students);

        $page_js = "/staff/vendor/application/add_pencil.js";

        echo $templates->render("submissions::submissions", [
            "title" => "Tekstmijn | Inzendingen",
            "page_title" => "Inzendingen",
            "page_subtitle" => $title,
            "menu" => $menu,
            "breadcrumbs" => $breadcrumbs,
            "table_ingeleverd" => $table_ingeleverd,
            "table_telaat" => $table_telaat,
            "table_nietingeleverd" => $table_nietingeleverd,
            "students_ingeleverd" => $students_ingeleverd,
            "tabs" => $tabs,
            "page_js" => $page_js,
            "class_id" => $class_id,
            "assignment_id" => $assignment_id,
            "staff_id" => $staff_id,
            "db" => $db]);
    });

    $router->get("submissions/(\d+)/([a-z0-9_-]+)/(\d+)", function ($class_id, $assignment_id, $submission_id) use ($db, $templates, $bp) {
        session_start("staff");

        $title = "Inzending";
        $assignment_name = getAssignmentName($db, $assignment_id);
        $student_name = getStudentName($db, $submission_id);
        $subtitle = sprintf("%s : %s", $assignment_name, $student_name);
        $class = getClassName($db, $class_id);
        $menu = generateMenu($bp, ["active" => "/staff/submissions/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["staff_name"] => "/staff/account/", "Inzendingen" => "/staff/submissions/", sprintf("Klas %s", $class) => "/staff/submissions/$class_id", $assignment_name => "/staff/submissions/$class_id/$assignment_id", $title => "#"]);

        $submission_info = getSubmissionInfo($db, $submission_id);
        $page_js = "/staff/vendor/application/add_field.js";

        $staff_id = $_SESSION['staff_id'];
        $current_grades= getGrades($db, $staff_id, $submission_id, ["Score"]);

        echo $templates->render("submissions::grading", ["title" => "Tekstmijn | Inzendingen",
            "page_title" => $title, "page_subtitle" => $subtitle, "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "class_id" => $class_id,
            "assignment_id" => $assignment_id,
            "submission_id" => $submission_id,
            "page_js" => $page_js,
            "submission_date" => $submission_info["submission_date"],
            "submission_file" => $submission_info["submission_file"],
            "submission_count" => $submission_info["submission_count"],
            "submission_originalfile" => $submission_info["submission_originalfile"],
            "text" => $submission_info["text"],
            "current_grades" => $current_grades,
        ]);
    });

    $router->post("/submissions/(.*)/grade", function ()  use ($db){
        session_start("staff");
        $staff_id = $_SESSION['staff_id'];
        $submission_id = $_POST["submission_id"];
        $grading_name = $_POST["grading_name"];
        $grading_grade = $_POST["grading_grade"];
        $grading_notes = $_POST["grade_Opmerkingen"];

        $result = insertGrades($db, $staff_id, $submission_id, $grading_name, $grading_grade, $grading_notes);
        if ($result){
            getRedirect("../?success=true");
        } else {
            getRedirect("../?success=false");
        }

    });

   /*
    * Downloads
    */

    $router->get("/download/(\d+)/([a-z0-9_-]+)", function($staffid, $assignmentid) use ($db){
        $files = getFiles($db, $staffid, $assignmentid);
        $filename_vars = getNames($db, $staffid, $assignmentid);
        $filename = sprintf("download_%s_%s.zip", $filename_vars['fullname'], $filename_vars['assignment_name']);

        $zip = \Comodojo\Zip\Zip::create($filename);

        foreach ($files as $file){
            $filepath = sprintf($_SERVER['DOCUMENT_ROOT'] . '/assets/submissions/%s', $file);
            $zip->add($filepath);
        }
        $zip->close();

        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
        exit();
    });

    /*
     * Review
     */

    $router->get("review/", function () use ($db, $templates, $bp){
        session_start("staff");

        $menu = generateMenu($bp, ["active" => "/staff/review/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["staff_name"] => "/staff/account/", "Beoordelen" => "/review/"]);

        $students =  getAssignmentforBeoordelaar($db, $_SESSION['staff_id']);
        $columns = [
            ["Titel", "title"]
        ];

        $table = generateTable($bp, $columns, $students, null, '<a href="%s/">%s</a>');
        echo $templates->render("submissions::classes", ["title" => "Tekstmijn | Beoordelen",
            "page_title" => "Beoordelen", "page_subtitle" => $_SESSION["staff_name"],  "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "table" => $table]);
    });

    $router->get("review/([a-z0-9_-]+)/", function($assignmentid)  use ($db, $templates, $bp) {
        session_start("staff");
        $staff_id = $_SESSION['staff_id'];

        $title = getAssignmentName($db, $assignmentid);
        $tabs = generateTabs($bp, ["Individueel beoordelen" => "#tebeoordelen", "Beoordelen in tabel" => "#beoordelen"], 'Individueel beoordelen');
        $menu = generateMenu($bp, ["active" => "/staff/review/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["staff_name"] => "/staff/account/", "Beoordelen" => "/staff/review/", $title => "#"]);

        $students_ingeleverd =  getSubmissionsforBeoordelaar($db, $assignmentid, $staff_id);
        $columns = [
            ["Leerlingnummer", "personnel_id"],
            ["Naam", "name"],
            ["Inleverdatum", "submission_date"],
            ["Aantal pogingen", "submission_count"],
        ];
        $table_ingeleverd = generateTable($bp, $columns, $students_ingeleverd, null, '<a href="%s/">%s</a>');

        $page_js = "/staff/vendor/application/add_pencil.js";

        echo $templates->render("submissions::review", [
            "title" => "Tekstmijn | Beoordelen",
            "page_title" => "Beoordelen",
            "page_subtitle" => $title,
            "menu" => $menu,
            "breadcrumbs" => $breadcrumbs,
            "table_ingeleverd" => $table_ingeleverd,
            "students_ingeleverd" => $students_ingeleverd,
            "tabs" => $tabs,
            "page_js" => $page_js,
            "assignment_id" => $assignmentid,
            "staff_id" => $staff_id,
            "db" => $db]);
    });

    $router->get("review/([a-z0-9_-]+)/(\d+)", function ($assignment_id, $submission_id) use ($db, $templates, $bp) {
        session_start("staff");

        $assignment_name = getAssignmentName($db, $assignment_id);
        $student_name = getStudentName($db, $submission_id);
        $subtitle = sprintf("%s : %s", $assignment_name, $student_name);
//        $class = getClassName($db, $class_id);
        $menu = generateMenu($bp, ["active" => "/staff/review/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["staff_name"] => "/staff/account/", "Beoordelen" => "/staff/review/", $assignment_name => "/staff/review/$assignment_id", "Beoordeel inzending" => "#"]);

        if ($_SESSION['type'] == 1) {$tabs = generateTabs($bp, ["Lezen en beoordelen" => "#beoordelen"], 'Lezen en beoordelen');}
        elseif ($_SESSION['type'] == 2) {$tabs = generateTabs($bp, ["Lezen en beoordelen" => "#beoordelen", "Beoordelingslijst" => "#beoordelingslijst"], 'Lezen en beoordelen');}

        $submission_info = getSubmissionInfo($db, $submission_id);
        $page_js = "/staff/vendor/application/add_field.js";

        $staff_id = $_SESSION['staff_id'];
        $current_grades= getGrades($db, $staff_id, $submission_id, ["Score"]);

        echo $templates->render("review::gradingdev", ["title" => "Tekstmijn | Beoordelen",
            "page_title" => $title, "page_subtitle" => $subtitle, "menu" => $menu, "breadcrumbs" => $breadcrumbs,
            "class_id" => $class_id,
            "assignment_id" => $assignment_id,
            "submission_id" => $submission_id,
            "staff_id" => $_SESSION['staff_id'],
            "page_js" => $page_js,
            "submission_date" => $submission_info["submission_date"],
            "submission_file" => $submission_info["submission_file"],
            "submission_count" => $submission_info["submission_count"],
            "submission_originalfile" => $submission_info["submission_originalfile"],
            "text" => $submission_info["text"],
            "current_grades" => $current_grades,
            "tabs" => $tabs,
            "user_type" => $_SESSION['type'],
            "db" => $db
        ]);
    });

    $router->post("review/(.*)/saveques", function ()  use ($db) {
        session_start("staff");
        $staff_id = $_POST['staff_id'];
        $submission_id = $_POST['submission_id'];
        $reviewerlist_id = $_POST['reviewerlist_id'];
        $saved_data = $_POST;
        unset($saved_data['staff_id']);
        unset($saved_data['submission_id']);
        unset($saved_data['reviewerlist_id']);

        $result = save_questionnaire($db, $saved_data, $staff_id, $submission_id, $reviewerlist_id);
        if ($result){
            getRedirect("../?success=true");
        } else {
            getRedirect("../?success=false");
        }
    });

    $router->post("/review/(.*)/grade", function ()  use ($db) {
        session_start("staff");
        $staff_id = $_SESSION['staff_id'];
        $submission_id = $_POST["submission_id"];
        $grading_name = $_POST["grading_name"];
        $grading_grade = $_POST["grading_grade"];
        $grading_notes = $_POST["grade_Opmerkingen"];

        $result = insertGrades($db, $staff_id, $submission_id, $grading_name, $grading_grade, $grading_notes);
        if ($result){
            getRedirect("../?success=true");
        } else {
            getRedirect("../?success=false");
        }
    });

    /*
     * Status
     */

    $router->get("/status/", 'status@generateOverall');
    $router->get("/status/(.*)", 'status@generateDetail');

    /*
     * Administration
     *
     * Routes to administration pages
     *
     */
    $router->mount('/administration', function() use ($router, $db, $templates, $bp) {

        /*
         * Show all institutions
         */
        $router->get("/", function() use ($db, $templates, $bp){
            session_start("staff");
            $menu = generateMenu($bp, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
            $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["staff_name"] => "/staff/account/", "Administratie" => "#"]);
            $tabs = generateTabs($bp, ["Scholen" => "#schools", "Universiteiten" => "#universities"], 'Scholen');
            $tbl_schools_data = getSchools($db);
            $tbl_schools_columns = [
                ["School", "name"],
            ];
            $tbl_schools_options = [
                ["<a class='pull-right' href='institution/%s/edit'><i class='glyphicon glyphicon-pencil'></i> Bewerken</a>"],
                ["<a class='pull-right' href='institution/%s/classes'><i class='glyphicon glyphicon-menu-hamburger'></i> Klassen</a>"],
                ["<a class='pull-right' href='institution/%s/students'><i class='glyphicon glyphicon-education'></i> Leerlingen</a>"],
                ["<a class='pull-right' href='institution/%s/personnel'><i class='glyphicon glyphicon-user'></i> Personeel</a>"]
            ];
            $tbl_schools = generateTable($bp, $tbl_schools_columns, $tbl_schools_data, $tbl_schools_options, '<a href="institution/%s/edit">%s</a>');

            $tbl_universities_data = getUniversities($db);
            $tbl_universities_columns = [
                ["Universiteit", "name"],
            ];
            $tbl_universities_options = [
                ["<a class='pull-right' href='institution/%s/edit'><i class='glyphicon glyphicon-pencil'></i> Bewerken</a>"],
                ["<a class='pull-right' href='institution/%s/reviewers'><i class='glyphicon glyphicon-user'></i> Beoordelaars</a>"]
            ];
            $tbl_universities = generateTable($bp, $tbl_universities_columns, $tbl_universities_data, $tbl_universities_options, '<a href="institution/%s/edit">%s</a>');

            echo $templates->render("administration::institutions", [
                "title" => "Tekstmijn | Administratie",
                "page_title" => "Administratie",
                "menu" => $menu,
                "breadcrumbs" => $breadcrumbs,
                "tbl_schools" => $tbl_schools,
                "tbl_universities" => $tbl_universities,
                "tabs" => $tabs
            ]);
        });

        /*
         * Manage institutions
         */
        $router->mount('/institution', function() use ($router, $db, $templates, $bp) {
            session_start("staff");
            $breadcrumbs_base = [$_SESSION["staff_name"] => "/staff/account/", "Administratie" => "/staff/administration/"];

            /*
             * Creating new institutions
             */
            $router->get("/new", function() use ($db, $templates, $bp, $breadcrumbs_base) {
                $type = $_GET["type"];
                if ($type == "school") {
                    $school_type = 0;
                } else {
                    $school_type = 1;
                }

                $menu = generateMenu($bp, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
                $breadcrumbs = generateBreadcrumbs($bp, array_merge($breadcrumbs_base, ["Onderwijsinstelling toevoegen" => "#"]));

                echo $templates->render("administration::institutions_add", [
                    "title" => "Tekstmijn | Administratie",
                    "page_title" => "Onderwijsinstelling toevoegen",
                    "menu" => $menu,
                    "breadcrumbs" => $breadcrumbs,
                    "school_type" => $school_type
                ]);
            });

            $router->post("/add", function() use ($db){
                if (addInstitution($db, $_POST)) {
                    getRedirect("/staff/administration/?institution_update=true");
                } else {
                    getRedirect("/staff/administration/?institution_update=false");
                }
            });

            /*
             * Managing settings and data of existing institutions
             */

            $router->mount('/([0-9a-zA-Z]+)', function() use ($router, $db, $templates, $bp, $breadcrumbs_base) {
                session_start("staff");

                /*
                 * Manage institution
                 */
                $router->get("/edit", function($school_id) use ($db, $templates, $bp, $breadcrumbs_base){
                    session_start("staff");
                    $menu = generateMenu($bp, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
                    $school_name = getSchoolName($db, $school_id);
                    $school_type = getSchoolType($db, $school_id);

                    $typestring = "School";
                    if ($school_type == 1) {
                        $typestring = "Universiteit";
                    }

                    $breadcrumbs = generateBreadcrumbs($bp, array_merge($breadcrumbs_base, [sprintf("%s: %s", $typestring, $school_name) => "#"]));

                    echo $templates->render("administration::institutions_edit", [
                        "title" => "Tekstmijn | Administratie",
                        "page_title" => sprintf("%s: %s", $typestring, $school_name),
                        "menu" => $menu,
                        "breadcrumbs" => $breadcrumbs,
                        "school_id" => $school_id,
                        "school_name" => $school_name,
                        "school_type" => $school_type
                    ]);
                });

                $router->post("/save", function($school_id) use ($db) {
                    session_start("staff");

                    if (updateInstitution($db, $school_id, $_POST)) {
                        getRedirect("/staff/administration/?institution_update=true");
                    } else {
                        getRedirect("/staff/administration/?institution_update=false");
                    }
                });

                $router->get("/delete", function($school_id) use ($db) {
                    if (deleteInstitution($db, $school_id)) {
                        getRedirect("/staff/administration/?institution_update=true");
                    } else {
                        getRedirect("/staff/administration/?institution_update=false");
                    }
                });


                /*
                 * Manage classes
                 */
                $router->mount('/classes', function() use($router, $db, $templates, $bp){
                    session_start("staff");

                    $router->get("/", function($school_id) use ($db, $templates, $bp) {
                        session_start("staff");
                        $institution = getInstitution($db, $school_id);
                        $menu = generateMenu($bp, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
                        $breadcrumbs = generateBreadcrumbs($bp,
                            [
                                $_SESSION["staff_name"] => "/staff/account/",
                                "Administratie" => "/staff/administration/",
                                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                                "Klassen" => "#"
                            ]
                        );

                        $tbl_schools_data = getClasses($db, $school_id);
                        $tbl_schools_columns = [
                            ["Klas", "name"],
                            ["Jaar", "year"],
                            ["Niveau", "level"],
                        ];
                        $tbl_schools_options = [
                            ["<a class='pull-right' href='%s/edit'><i class='glyphicon glyphicon-pencil'></i> Bewerken</a>"],
                            ["<a class='pull-right' href='%s/'><i class='glyphicon glyphicon-education'></i> Leerlingen</a>"]
                        ];
                        $tbl_schools = generateTable($bp, $tbl_schools_columns, $tbl_schools_data, $tbl_schools_options, '<a href="%s/edit">%s</a>');

                        echo $templates->render("admin_classes::classes", [
                            "title" => "Tekstmijn | Administratie",
                            "page_title" => "Klassen",
                            "menu" => $menu,
                            "breadcrumbs" => $breadcrumbs,
                            "tbl_class" => $tbl_schools
                        ]);
                    });

                    $router->get("/([0-9a-zA-Z]+)", function($school_id, $class_id) use ($db, $templates, $bp){
                        session_start("staff");
                        $institution = getInstitution($db, $school_id);
                        $name = sprintf("Klas %s", getClassName($db, $class_id));
                        $menu = generateMenu($bp, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
                        $breadcrumbs = generateBreadcrumbs($bp,
                            [
                                $_SESSION["staff_name"] => "/staff/account/",
                                "Administratie" => "/staff/administration/",
                                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                                "Klassen" => "../",
                                $name => "#"
                            ]
                        );

                        $students =  getClassStudents($db, $class_id);
                        $columns = [
                            ["#", "id"],
                            ["Naam", "name"]
                        ];
                        $options = [
                            ["<a class='pull-right' href='../../students/%s/edit'><i class='glyphicon glyphicon-pencil'></i> Bewerken</a>"],
                            ["<a class='pull-right' id='%s' onclick='reset_student_pwd(%s, self.document)'><i class='glyphicon glyphicon-repeat'></i> Wachtwoord resetten</a>", "id", '']
                        ];

                        $table = generateTable($bp, $columns, $students, $options, '<a href="../../students/%s/edit">%s</a>');
                        echo $templates->render("classes::class", ["title" => "Tekstmijn | Administratie",
                            "page_title" => $name, "menu" => $menu, "breadcrumbs" => $breadcrumbs,
                            "table" => $table, "page_js" => "/staff/vendor/application/reset_pwd_students.js"]);
                    });



                    $router->get("/new", function($school_id) use ($db, $templates, $bp) {
                        session_start("staff");
                        $institution = getInstitution($db, $school_id);
                        $menu = generateMenu($bp, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
                        $breadcrumbs = generateBreadcrumbs($bp,
                            [
                                $_SESSION["staff_name"] => "/staff/account/",
                                "Administratie" => "/staff/administration/",
                                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                                "Klassen" => sprintf("/staff/administration/institution/%s/classes/", $school_id),
                                "Klas toevoegen" => "#",
                            ]
                        );

                        echo $templates->render("admin_classes::add", [
                            "title" => "Tekstmijn | Administratie",
                            "page_title" => "Klas toevoegen",
                            "menu" => $menu,
                            "breadcrumbs" => $breadcrumbs,
                        ]);
                    });

                    $router->post("/add", function($school_id) use ($db) {
                        if (addClass($db, $school_id, $_POST)) {
                            getRedirect("../?institution_update=true");
                        } else {
                            getRedirect("../?institution_update=false");
                        }
                    });

                    $router->get("/([0-9a-zA-Z]+)/edit", function($school_id, $class_id) use ($db, $templates, $bp) {
                        session_start("staff");
                        $institution = getInstitution($db, $school_id);
                        $menu = generateMenu($bp, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
                        $breadcrumbs = generateBreadcrumbs($bp,
                            [
                                $_SESSION["staff_name"] => "/staff/account/",
                                "Administratie" => "/staff/administration/",
                                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                                "Klassen" => sprintf("/staff/administration/institution/%s/classes/", $school_id),
                                "Klas bewerken" => "#",
                            ]
                        );
                        $class = getClass($db, $class_id);

                        echo $templates->render("admin_classes::edit", [
                            "title" => "Tekstmijn | Administratie",
                            "page_title" => "Klas bewerken",
                            "menu" => $menu,
                            "breadcrumbs" => $breadcrumbs,
                            "name" => $class["name"],
                            "levelid" => $class["levelid"],
                            "levelname" => $class["level"],
                            "year" => $class["year"]
                        ]);
                    });

                    $router->get("/([0-9a-zA-Z]+)/delete", function($school_id, $class_id) use ($db, $templates, $bp) {
                        if (deleteClass($db, $class_id)) {
                            getRedirect("../../?institution_update=true");
                        } else {
                            getRedirect("../../?institution_update=false");
                        }
                    });

                    $router->post("/([0-9a-zA-Z]+)/save", function($school_id, $class_id) use ($db, $templates, $bp) {
                        if (updateClass($db, $class_id, $_POST)) {
                            getRedirect("../../?institution_update=true");
                        } else {
                            getRedirect("../../?institution_update=false");
                        }
                    });
                });

                /*
                 * Manage students
                 */
                $router->mount('/students', function() use ($router, $db, $templates, $bp){
                    $router->get("/", function($school_id) use ($db, $templates, $bp) {
                        session_start("staff");

                        $institution = getInstitution($db, $school_id);
                        $menu = generateMenu($bp, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
                        $breadcrumbs = generateBreadcrumbs($bp,
                            [
                                $_SESSION["staff_name"] => "/staff/account/",
                                "Administratie" => "/staff/administration/",
                                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                                "Leerlingen" => "#"
                            ]
                        );

                        $students =  getInstitutionStudents($db, $school_id);
                        $columns = [
                            ["#", "id"],
                            ["Naam", "name"]
                        ];
                        $options = [
                            ["<a class='pull-right' href='%s/edit'><i class='glyphicon glyphicon-pencil'></i> Bewerken</a>"],
                            ["<a class='pull-right' id='%s' onclick='reset_student_pwd(%s, self.document)'><i class='glyphicon glyphicon-repeat'></i> Wachtwoord resetten</a>", "id", '']
                        ];

                        $table = generateTable($bp, $columns, $students, $options, '<a href="%s/edit">%s</a>');
                        echo $templates->render("admin_students::students", ["title" => "Tekstmijn | Administratie",
                            "page_title" => "Leerlingen", "menu" => $menu, "breadcrumbs" => $breadcrumbs,
                            "table" => $table, "page_js" => "/staff/vendor/application/reset_pwd_students.js"]);

                    });

                    $router->get("/new", function($school_id) use ($db, $templates, $bp) {
                        session_start("staff");
                        $classes = getClassList($db, $school_id);

                        $institution = getInstitution($db, $school_id);

                        $menu = generateMenu($bp, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
                        $breadcrumbs = generateBreadcrumbs($bp,
                            [
                                $_SESSION["staff_name"] => "/staff/account/",
                                "Administratie" => "/staff/administration/",
                                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                                "Leerlingen" => sprintf("/staff/administration/institution/%s/students/", $school_id),
                                "Nieuwe leerling" => "#"
                            ]
                        );

                        echo $templates->render("admin_students::add",
                            [
                                "title" => "Tekstmijn | Administratie",
                                "page_title" => "Nieuwe leerling",
                                "menu" => $menu,
                                "breadcrumbs" => $breadcrumbs,
                                "classes" => generateOptions($classes),
                                "page_js" => "/staff/vendor/application/load_date_picker.js"
                            ]
                        );
                    });

                    $router->post("/add", function($school_id) use ($db) {
                        if (addStudent($db, $_POST, $school_id)) {
                            getRedirect("../?student_added=true");
                        } else {
                            getRedirect("../?student_added=false");
                        }
                    });

                    $router->get("/([0-9a-zA-Z]+)/edit", function($school_id, $student_id) use ($db, $templates, $bp) {
                        session_start("staff");

                        $institution = getInstitution($db, $school_id);
                        $classes = getClassList($db, $school_id);
                        $student = getStudent($db, $student_id, $institution["id"]);

                        if ($student) {
                            $menu = generateMenu($bp, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
                            $breadcrumbs = generateBreadcrumbs($bp,
                                [
                                    $_SESSION["staff_name"] => "/staff/account/",
                                    "Administratie" => "/staff/administration/",
                                    sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                                    "Leerlingen" => sprintf("/staff/administration/institution/%s/students/", $school_id),
                                    sprintf("Bewerk leerling #%d: %s %s %s", $student['id'], $student['firstname'], $student['prefix'], $student['lastname']) => "#"
                                ]
                            );

                            echo $templates->render("admin_students::edit",
                                [
                                    "title" => "Tekstmijn | Administratie",
                                    "page_title" => sprintf("Leerling #%d: %s %s %s", $student['id'], $student['firstname'], $student['prefix'], $student['lastname']),
                                    "menu" => $menu,
                                    "breadcrumbs" => $breadcrumbs,
                                    "student" => $student,
                                    "classes" => generateOptions($classes),
                                    "page_js" => "/staff/vendor/application/load_date_picker.js"
                                ]
                            );
                        } else {
                            echo "U heeft geen toegang tot deze gegevens."; // TODO: better error message?
                        }

                    });

                    $router->get("/([0-9a-zA-Z]+)/delete", function($school_id, $student_id) use ($db, $templates, $bp) {
                        if (deleteStudent($db, $student_id)) {
                            getRedirect("../../?student_deletec=true");
                        } else {
                            getRedirect("../../?student_deletec=false");
                        }
                    });

                    $router->post("/([0-9a-zA-Z]+)/save", function($school_id, $student_id) use ($db) {
                        if (updateStudent($db, $student_id, $_POST)) {
                            getRedirect("../../?student_update=true");
                        } else {
                            getRedirect("../../?student_update=false");
                        }
                    });


                });


                /*
                 * Manage personnel
                 */
                $router->mount('/personnel', function() use ($router, $db, $templates, $bp) {
                    $router->get("/", function($school_id) use ($db, $templates, $bp)  {
                        session_start("staff");
                        $institution = getInstitution($db, $school_id);
                        $menu = generateMenu($bp, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
                        $breadcrumbs = generateBreadcrumbs($bp,
                            [
                                $_SESSION["staff_name"] => "/staff/account/",
                                "Administratie" => "/staff/administration/",
                                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                                "Personeel" => "#"
                            ]
                        );

                        $personnel =  getPersonnel($db, $school_id);
                        $columns = [
                            ["Naam", "fullname"],
                            ["Emailadres", "email"]
                        ];
                        $options = [
                            ["<a class='pull-right' href='%s/edit'><i class='glyphicon glyphicon-pencil'></i> Bewerken</a>"],
//                    ["<a class='pull-right' id='%s' onclick='reset_student_pwd(%s, self.document)'><i class='glyphicon glyphicon-repeat'></i> Wachtwoord resetten</a>", "id", '']
                        ];
                        $table = generateTable($bp, $columns, $personnel, $options, '<a href="%s/edit">%s</a>');

                        echo $templates->render("admin_personnel::personnel", [
                            "title" => "Tekstmijn | Administratie",
                            "menu" => $menu,
                            "page_title" => "Personeel",
                            "breadcrumbs" => $breadcrumbs,
                            "tbl" => $table,
                        ]);
                    });

                    $router->get("/([0-9a-zA-Z]+)/edit", function($school_id, $personnel_id) use ($db, $templates, $bp) {
                        session_start("staff");

                        $institution = getInstitution($db, $school_id);
                        $classes = getClassList($db, $school_id);
                        $personnelMember = getPersonnelMember($db, $personnel_id, $institution["id"]);

                        if ($personnelMember) {
                            $menu = generateMenu($bp, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
                            $breadcrumbs = generateBreadcrumbs($bp,
                                [
                                    $_SESSION["staff_name"] => "/staff/account/",
                                    "Administratie" => "/staff/administration/",
                                    sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                                    "Personeel" => sprintf("/staff/administration/institution/%s/personnel/", $school_id),
                                    sprintf("Bewerk personeelslid: %s %s %s", $personnelMember['firstname'], $personnelMember['prefix'], $personnelMember['lastname']) => "#"
                                ]
                            );

                            echo $templates->render("admin_personnel::edit",
                                [
                                    "title" => "Tekstmijn | Administratie",
                                    "page_title" => sprintf("Personeelslid: %s %s %s", $personnelMember['firstname'], $personnelMember['prefix'], $personnelMember['lastname']),
                                    "menu" => $menu,
                                    "breadcrumbs" => $breadcrumbs,
                                    "personnelmember" => $personnelMember,
                                    "classes" => generateOptions($classes),
                                    "page_js" => "/staff/vendor/application/load_date_picker.js"
                                ]
                            );
                        } else {
                            echo "U heeft geen toegang tot deze gegevens."; // TODO: better error message?
                        }

                    });

                    $router->post("/([0-9a-zA-Z]+)/save", function($school_id, $personnel_id) use ($db, $templates, $bp) {
                        if (updatePersonnelMember($db, $personnel_id, $_POST)) {
                            getRedirect("../../?personnel_update=true");
                        } else {
                            getRedirect("../../?personnel_update=false");
                        }
                    });

                    $router->get("/([0-9a-zA-Z]+)/delete", function($school_id, $personnel_id) use ($db, $templates, $bp) {
                        if (deletePersonnelMember($db, $personnel_id)) {
                            getRedirect("../../?personnel_deleted=true");
                        } else {
                            getRedirect("../../?personnel_deleted=false");
                        }
                    });

                    $router->get("/new", function($school_id) use ($db, $templates, $bp) {
                        session_start("staff");
                        $institution = getInstitution($db, $school_id);

                        $menu = generateMenu($bp, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
                        $breadcrumbs = generateBreadcrumbs($bp,
                            [
                                $_SESSION["staff_name"] => "/staff/account/",
                                "Administratie" => "/staff/administration/",
                                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                                "Personeel" => sprintf("/staff/administration/institution/%s/personnel/", $school_id),
                                "Nieuw personeelslid" => "#"
                            ]
                        );

                        echo $templates->render("admin_personnel::add",
                            [
                                "title" => "Tekstmijn | Administratie",
                                "page_title" => "Nieuwe personeelslid",
                                "menu" => $menu,
                                "breadcrumbs" => $breadcrumbs,
                                "page_js" => "/staff/vendor/application/load_date_picker.js"
                            ]
                        );
                    });

                    $router->post("/add", function($school_id) use ($db) {
                        if (addPersonnelMember($db, $_POST, $school_id)) {
                            getRedirect("../?personnel_added=true");
                        } else {
                            getRedirect("../?personnel_added=false");
                        }
                    });

                });


                /*
                 * Manage reviewers
                 */
                $router->mount('/reviewers', function() use ($router, $db, $templates, $bp) {
                    $router->get('/', function($school_id) use ($router, $db, $templates, $bp) {
                        session_start("staff");
                        $institution = getInstitution($db, $school_id);
                        $menu = generateMenu($bp, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
                        $breadcrumbs = generateBreadcrumbs($bp,
                            [
                                $_SESSION["staff_name"] => "/staff/account/",
                                "Administratie" => "/staff/administration/",
                                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                                "Beoordelaars" => "#"
                            ]
                        );

                        $personnel =  getPersonnel($db, $school_id);
                        $columns = [
                            ["Naam", "fullname"],
                            ["Emailadres", "email"]
                        ];
                        $options = [
                            ["<a class='pull-right' href='%s/edit'><i class='glyphicon glyphicon-pencil'></i> Bewerken</a>"],
//                    ["<a class='pull-right' id='%s' onclick='reset_student_pwd(%s, self.document)'><i class='glyphicon glyphicon-repeat'></i> Wachtwoord resetten</a>", "id", '']
                        ];
                        $table = generateTable($bp, $columns, $personnel, $options, '<a href="%s/edit">%s</a>');

                        echo $templates->render("admin_reviewers::reviewers", [
                            "title" => "Tekstmijn | Administratie",
                            "menu" => $menu,
                            "breadcrumbs" => $breadcrumbs,
                            "tbl" => $table,
                        ]);
                    });

                    $router->get("/([0-9a-zA-Z]+)/edit", function($school_id, $personnel_id) use ($db, $templates, $bp) {
                        session_start("staff");

                        $institution = getInstitution($db, $school_id);
                        $personnelMember = getPersonnelMember($db, $personnel_id, $institution["id"]);

                        if ($personnelMember) {
                            $menu = generateMenu($bp, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
                            $breadcrumbs = generateBreadcrumbs($bp,
                                [
                                    $_SESSION["staff_name"] => "/staff/account/",
                                    "Administratie" => "/staff/administration/",
                                    sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                                    "Beoordelaars" => sprintf("/staff/administration/institution/%s/reviewers/", $school_id),
                                    sprintf("Bewerk beoordelaar: %s %s %s", $personnelMember['firstname'], $personnelMember['prefix'], $personnelMember['lastname']) => "#"
                                ]
                            );

                            echo $templates->render("admin_reviewers::edit",
                                [
                                    "title" => "Tekstmijn | Administratie",
                                    "page_title" => sprintf("Beoordelaar: %s %s %s", $personnelMember['firstname'], $personnelMember['prefix'], $personnelMember['lastname']),
                                    "menu" => $menu,
                                    "breadcrumbs" => $breadcrumbs,
                                    "personnelmember" => $personnelMember,
                                    "page_js" => "/staff/vendor/application/load_date_picker.js"
                                ]
                            );
                        } else {
                            echo "U heeft geen toegang tot deze gegevens."; // TODO: better error message?
                        }

                    });

                    $router->post("/([0-9a-zA-Z]+)/save", function($school_id, $personnel_id) use ($db, $templates, $bp) {
                        if (updatePersonnelMember($db, $personnel_id, $_POST)) {
                            getRedirect("../../?reviewer_update=true");
                        } else {
                            getRedirect("../../?reviewer_update=false");
                        }
                    });

                    $router->get("/([0-9a-zA-Z]+)/delete", function($school_id, $personnel_id) use ($db, $templates, $bp) {
                        if (deletePersonnelMember($db, $personnel_id)) {
                            getRedirect("../../?reviewer_deleted=true");
                        } else {
                            getRedirect("../../?reviewer_deleted=false");
                        }
                    });

                    $router->get("/new", function($school_id) use ($db, $templates, $bp) {
                        session_start("staff");
                        $institution = getInstitution($db, $school_id);

                        $menu = generateMenu($bp, ["active" => "/staff/administration/", "align" => "stacked"], $_SESSION['type']);
                        $breadcrumbs = generateBreadcrumbs($bp,
                            [
                                $_SESSION["staff_name"] => "/staff/account/",
                                "Administratie" => "/staff/administration/",
                                sprintf("%s: %s", $institution['type'], $institution['name']) => sprintf("/staff/administration/institution/%s/edit", $school_id),
                                "Beoordelaars" => sprintf("/staff/administration/institution/%s/personnel/", $school_id),
                                "Nieuwe beoordelaar" => "#"
                            ]
                        );

                        echo $templates->render("admin_reviewers::add",
                            [
                                "title" => "Tekstmijn | Administratie",
                                "page_title" => "Nieuwe beoordelaar",
                                "menu" => $menu,
                                "breadcrumbs" => $breadcrumbs,
                                "page_js" => "/staff/vendor/application/load_date_picker.js"
                            ]
                        );
                    });

                    $router->post("/add", function($school_id) use ($db) {
                        if (addPersonnelMember($db, $_POST, $school_id)) {
                            getRedirect("../?reviewer_added=true");
                        } else {
                            getRedirect("../?reviewer_added=false");
                        }
                    });
                });



            });


        });



    });

    $router->run();