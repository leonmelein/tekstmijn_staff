<?php $debug = false;
    /**
     * CONTROLLER
     */

    // Third party libraries
    require("vendor/autoload.php");

    // Debugging functionality
    if ($debug) {
        error_reporting(E_ALL);
    }

    // Main model
    require("model/model.php");

    // Page models
    require("model/classroom.php");
    require("model/auth.php");
    require("model/analysis.php");
    require("model/account.php");
    require("model/submissions.php");
    require("model/grading.php");
    require("model/review.php");
    require("model/assignment.php");
    require("model/admin.php");
    require("model/institution.php");
    require("model/classes.php");
    require("model/students.php");
    require("model/personnel.php");
    require("model/reviewers.php");
    require("model/questionnaires.php");

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

    // Initiate router
    $router = new \Bramus\Router\Router();

    /*
     * Page routers
     *
     * Provides routes to the individual parts of the system
     */

    /**
     * Authentication
     *
     * Handles authentication of users.
     */
    // Pre-routing checks
    $router->before('GET|POST', '/account/', 'auth@checkLogin');
    $router->before('GET|POST', '/classes/', 'auth@checkLogin');
    $router->before('GET|POST', '/classes/.*', 'auth@checkLogin');
    $router->before('GET|POST', '/submissions/', 'auth@checkLogin');
    $router->before('GET|POST', '/submissions/.*', 'auth@checkLogin');
    $router->before('GET|POST', '/status/', 'auth@checkLogin');
    $router->before('GET|POST', '/status/.*', 'auth@checkLogin');
    $router->before('GET|POST', '/review/','auth@checkLogin');
    $router->before('GET|POST', '/review/.*', 'auth@checkLogin');
    $router->before('GET|POST', '/assignment/', 'auth@checkLogin');
    $router->before('GET|POST', '/assignment/.*', 'auth@checkLogin');
    $router->before('GET|POST', '/questionnaire/', 'auth@checkLogin');
    $router->before('GET|POST', '/questionnaire/.*', 'auth@checkLogin');
    $router->before('GET|POST', '/administration/', 'auth@checkLogin');
    $router->before('GET|POST', '/administration/.*', 'auth@checkLogin');
    $router->before('GET', '/register/', 'auth@checkToken');

    // User-facing login-logout routes
    $router->get("/", "auth@homepage");
    $router->mount('/login', function() use ($router){
        $router->get("/", "auth@loginpage");

        $router->post("/", 'auth@login');
    });
    $router->get('/logout', "auth@logout");

    /**
     * Account
     *
     * Handles user account actions like registration and password resets.
     */
    $router->mount('/account', function() use ($router){
        $router->get("/", "account@showAccount");
        $router->post("/", "account@updateAccount");
    });
    $router->mount('/register', function() use ($router){
        $router->get("/", "account@startRegistration");
        $router->post("/", "account@completeRegistration");
    });
    $router->mount('/forgot', function () use ($router){
        $router->get('/', "auth@requestReset");
        $router->post('/', "auth@sendResetLink");
    });
    $router->mount('/reset_password', function() use ($router){
        $router->get("/", "account@startPasswordReset");
        $router->post("/", "account@completePasswordReset");
    });

    // Resetting student passwords from staff interface by teachers and administrators
    $router->get("/reset/(\d+)/", "classroom@resetStudentPwd");

    /**
     * Classrooms
     *
     * Displays a list of classes and their respective students to teachers.
     */
    $router->mount('/classes', function() use ($router){
        $router->get("/", "classroom@teacherOverview");
        $router->get("/(\d+)", "classroom@individualClass");
    });

    /**
     * Submissions
     *
     * Displays submissions for assignments to teachers and enables grading.
     */
    $router->mount('/submissions', function() use ($router){
        $router->get("/", "submissions@overview");
        $router->get("/(\d+)", "submissions@assignmentOverview");
        $router->get("/(\d+)/([a-z0-9_-]+)", "submissions@assignmentSubmissions");
        $router->get("/(\d+)/([a-z0-9_-]+)/(\d+)", "submissions@individualSubmission");
        $router->post("/(.*)/grade", "grading@setIndividualGrade");
    });

    /**
     * Review
     *
     * Displays submissions for assignments to reviewers and administrators, enabling grading and element scoring.
     */
    $router->mount('/review', function() use ($router){
        $router->get("/", "review@overview");
        $router->get("/([a-z0-9_-]+)", "review@assignment");
        $router->get("/([a-z0-9_-]+)/download", "review@downloadSubmissions");
        $router->get("/([a-z0-9_-]+)/(\d+)", "review@submission");
        $router->post("/(.*)/saveques", "review@questionnaire");
        $router->post("/(.*)/grade", "grading@setIndividualGrade");
    });

    /**
     * Analysis
     *
     * Provides insight into the amount of grades entered per assignment and per reviewer and provides exports of
     * grades, element scores and texts for further analysis.
     */
    $router->mount("/analysis", function() use ($router){
        $router->get("/", 'analysis@overview');
        $router->get("/status/(.*)", 'analysis@generateStatusDetail');
        $router->get("/gradings/(.*)", 'analysis@downloadGradings');
        $router->get("/elementscores/(.*)", 'analysis@downloadElementScores');
        $router->get("/texts/(.*)", 'analysis@downloadTexts');
    });

    /**
     * Assignments
     *
     * Enables creation and modification of student assignments.
     */
    $router->mount('/assignment', function() use ($router){
        $router->get("/", "assignment@overview");
        $router->get("/new", "assignment@newAssignment");
        $router->post("/save", "assignment@addAssignment");
        $router->get("/([a-z0-9_-]+)", "assignment@individualAssignment");
        $router->get("/([a-z0-9_-]+)/download", "assignment@downloadSubmissions");
        $router->get("/([a-z0-9_-]+)/edit", "assignment@editAssignment");
        $router->get("/([a-z0-9_-]+)/delete", "assignment@deleteAssignment");
        $router->post("/([a-z0-9_-]+)/save", "assignment@updateAssignment");
    });

    /**
     * Questionnaires
     *
     * Enables creation and modification of student questionnaires.
     */
    $router->mount('/questionnaire', function () use ($router) {
        $router->get('/', "questionnaires@overview");
        $router->get('/new', "questionnaires@newQuestionnaire");
        $router->post('/save', "questionnaires@addQuestionnaire");
        $router->get('/(\d+)', "questionnaires@editQuestionnaire");
        $router->get('/(\d+)/delete', "questionnaires@deleteQuestionnaire");
        $router->post('/(\d+)/save', "questionnaires@updateQuestionnaire");
    });

    /**
     * Administration
     *
     * Provides tools to manage institutions and their personnel; classes and their students.
     */
    $router->mount('/administration', function() use ($router) {

        /*
         * Display a list of all institutions, seperated in schools and universities (providing reviewers)
         */
        $router->get("/", "institution@overview");

        /*
         * Manage institutions
         */
        $router->mount('/institution', function() use ($router) {
            /*
             * Creating new institutions
             */
            $router->get("/new", "institution@newInstitution");
            $router->post("/add", "institution@saveInstitution");

            /*
             * Managing settings and data of existing institutions
             */
            $router->mount('/([0-9a-zA-Z]+)', function() use ($router) {
                /*
                 * Manage institution
                 */
                $router->get("/edit", "institution@editInstitution");
                $router->post("/save", "institution@saveUpdatedInstitution");
                $router->get("/delete", "institution@delInstitution");

                /*
                 * Manage classes
                 */
                $router->mount('/classes', function() use($router){
                    $router->get("/", "classes@overview");
                    $router->get("/new", "classes@newClass");
                    $router->post("/add", "classes@saveClass");
                    $router->get("/([0-9a-zA-Z]+)", "classes@individualClass");
                    $router->get("/([0-9a-zA-Z]+)/edit", "classes@editClass");
                    $router->get("/([0-9a-zA-Z]+)/delete", "classes@delClass");
                    $router->post("/([0-9a-zA-Z]+)/save", "classes@saveUpdatedClass");
                });

                /*
                 * Manage students
                 */
                $router->mount('/students', function() use ($router){
                    $router->get("/", "students@overview");
                    $router->get("/new", "students@newStudent");
                    $router->post("/add", "students@saveStudent");
                    $router->get("/([0-9a-zA-Z]+)/edit", "students@editStudent");
                    $router->post("/([0-9a-zA-Z]+)/save", "students@saveUpdatedStudent");
                    $router->get("/([0-9a-zA-Z]+)/delete", "students@delStudent");
                });


                /*
                 * Manage personnel
                 */
                $router->mount('/personnel', function() use ($router) {
                    $router->get("/", "personnel@overview");
                    $router->get("/([0-9a-zA-Z]+)/edit", "personnel@editPersonnelMember");
                    $router->post("/([0-9a-zA-Z]+)/save", "personnel@updatePersonnel");
                    $router->get("/([0-9a-zA-Z]+)/delete","personnel@deletePersonnel");
                    $router->get("/new", "personnel@newPersonnelMember");
                    $router->post("/add", "personnel@savePersonnel");
                });


                /*
                 * Manage reviewers
                 */
                $router->mount('/reviewers', function() use ($router) {
                    $router->get('/', 'reviewers@overview');
                    $router->get("/([0-9a-zA-Z]+)/edit", "reviewers@editReviewer");
                    $router->post("/([0-9a-zA-Z]+)/save", "reviewers@updateReviewer");
                    $router->get("/([0-9a-zA-Z]+)/delete", "reviewers@deleteReviewer");
                    $router->get("/new", "reviewers@newReviewer");
                    $router->post("/add", "reviewers@saveReviewer");
                });

            });
        });
    });

    $router->run();