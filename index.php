<?php
    /**
     * CONTROLLER
     */

    // Third party libraries
    require("vendor/autoload.php");

    // Debugging functions
    require("model/debug.php");

    // Old type Models
    require("model/index.php");
    require("model/students.php");
    require("model/submission.php");
    require("model/administration.php");

    // Main model
    require("model/model.php");
    // New type Submodels
    require("model/status.php");
    require("model/classroom.php");
    require("model/auth.php");
    require("model/analysis.php");
    require("model/downloads.php");
    require("model/account.php");
    require("model/submissions.php");
    require("model/grading.php");
    require("model/review.php");

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
    $templates->addFolder("analysis", "view/analysis");
    $bp = new Bootstrap;

    // Initiate router
    $router = new \Bramus\Router\Router();

    /*
     * Page routers
     * - Provides routes to the individual parts of the system
     */

    /**
     * Authentication
     * Checking if a user is logged in or has the required
     */
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

    $router->get("/", function(){getRedirect("/staff/login");});
    $router->mount('/login', function() use ($router, $templates){
        $router->get("/", function () use ($templates){
            echo $templates->render("login::login", ["title" => "Tekstmijn | Inloggen"]);
        });

        $router->post("/", 'auth@login');
    });
    $router->get('/logout', "auth@logout");

    /**
    * Account
    */
    $router->mount('/account', function() use ($router){
    $router->get("/", "account@showAccount");
    $router->post("/", "account@updateAccount");
});
    $router->get("/register", "account@startRegistration");
    $router->post("/register", "account@completeRegistration");
    $router->get("/reset_password/", "account@startPasswordReset");
    $router->post("/reset_password/", "account@completePasswordReset");
    $router->get("/reset/(\d+)/", "classroom@resetStudentPwd");

    /**
     * Classes
     */
    $router->mount('/classes', function() use ($router){
        $router->get("/", "classroom@teacherOverview");

        $router->get("/(\d+)", "classroom@individualClass");
    });

    /**
     * Submissions
     */
    $router->mount('/submissions', function() use ($router, $db, $templates, $bp){
        $router->get("/", "submissions@overview");
        $router->get("/(\d+)", "submissions@assignmentOverview");
        $router->get("/(\d+)/([a-z0-9_-]+)", "submissions@assignmentSubmissions");
        $router->get("/(\d+)/([a-z0-9_-]+)/(\d+)", "submissions@individualSubmission");
        $router->post("/(.*)/grade", "grading@setIndividualGrade");
    });

    /**
     * Review
     */
    $router->mount('/review', function() use ($router, $db, $templates, $bp){
        $router->get("/", "review@overview");
        $router->get("/([a-z0-9_-]+)", "review@assignment");
        $router->get("/([a-z0-9_-]+)/download", "review@downloadSubmissions");
        $router->get("/([a-z0-9_-]+)/(\d+)", "review@submission");
        $router->post("/(.*)/saveques", "review@questionnaire");
        $router->post("/(.*)/grade", "grading@setIndividualGrade");
    });

    /**
     * Downloads
     *
     */
    $router->get("/download/(\d+)/([a-z0-9_-]+)", function($staffid, $assignmentid) use ($db){
        $files = getFiles($db, $staffid, $assignmentid);
        $filename_vars = getNames($db, $staffid, $assignmentid);
        chdir("tmp");
        $filename = sprintf("Beoordelingspakket - %s - %s.zip", $filename_vars['fullname'], $filename_vars['assignment_name']);
        $zip = \Comodojo\Zip\Zip::create($filename);
        foreach ($files as $file){
            $zip->add($file['file'], $file['original_file']);
        }
        $zip->close();

        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
        unlink($filename);
        exit();

    });

    /**
     * Analysis
     */
    $router->get("/analysis/", 'analysis@overview');
    $router->get("/analysis/status/(.*)", 'analysis@generateStatusDetail');
    $router->get("/analysis/beoordelingen/(.*)", 'analysis@downloadBeoordelingen');
    $router->get("/analysis/beoordelingslijsten/(.*)", 'analysis@downloadBeoordelingslijsten');

    /**
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
                            ["<a class='pull-right' id='%s' onclick='reset_student_pwd(%s, self.document);'><i class='glyphicon glyphicon-repeat'></i> Wachtwoord resetten</a>", "id", '']
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