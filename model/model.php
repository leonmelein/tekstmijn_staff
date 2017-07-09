<?php use BootPress\Bootstrap\v3\Component as Bootstrap;

/**
 * Base model providing supporting functions and access to templates, databases and libraries.
 */
class model
{
    /**
     * Model constructor.
     *
     * Sets up access to database, templates and bootstrap library.
     */
    public function __construct()
    {
        $db_settings = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/config/config.ini");
        $this->database = new medoo([
            'database_type' => 'mysql',
            'database_name' => $db_settings['database_name'],
            'server' => $db_settings['server'],
            'username' => $db_settings['username'],
            'password' => $db_settings['password'],
            'charset' => 'utf8mb4',
            'command' => [
                "SET SQL_MODE=ANSI_QUOTES; SET lc_time_names = 'nl_NL'"
            ]
        ]);
        $this->bootstrap = new Bootstrap;

        // Template setup
        $templates = new League\Plates\Engine('view', 'tpl');
        $templates->addFolder("login", "view/login");
        $templates->addFolder("classes", "view/classes");
        $templates->addFolder("mail", "view/mail");
        $templates->addFolder("submissions", "view/submissions");
        $templates->addFolder("review", "view/review");
        $templates->addFolder("analysis", "view/analysis");
        $templates->addFolder("assignment", "view/assignment");
        $templates->addFolder("questionnaire", "view/questionnaire");
        $templates->addFolder("administration", "view/administration");
        $templates->addFolder("admin_classes", "view/administration/classes");
        $templates->addFolder("admin_students", "view/administration/students");
        $templates->addFolder("admin_personnel", "view/administration/personnel");
        $templates->addFolder("admin_reviewers", "view/administration/reviewers");
        $this->templates = $templates;
    }

    /**
     * Generates a table from an array for use in templates.
     *
     * @param $bp Bootstrap instance
     * @param $columns array containing child arrays with the internal column name and the display name
     * @param $data array containing all data used to fill the table
     * @param null $options array (optional) to provide additional links for every item; these items are arrays in itself
     * @param string $format string (optional) containing the formatting for each table entry
     * @param string $classes string (optional) containing the CSS classes to be assigned to each entry
     * @param bool $external bool indicating if the given format provides an external link
     * @return string containing the finalized table
     */
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

    /**
     * Generate menu for use in templates.
     *
     * @param $bp Bootstrap instance
     * @param $active string indicating the active menu item
     * @return string containing the menu as HTML
     */
    public function menu($bp, $active, $type){
        $item = [
            "Inzendingen" => "<i class='glyphicon glyphicon-pencil' aria-hidden='true'></i>&nbsp;&nbsp;Inzendingen",
            "Leerlingen" => "<i class='glyphicon glyphicon-th-list' aria-hidden='true'></i>&nbsp;&nbsp;Leerlingen",
            "Beoordelen" => "<i class='glyphicon glyphicon-pencil' aria-hidden='true'></i>&nbsp;&nbsp;Beoordelen",
            "Analyse" => "<i class='glyphicon glyphicon-stats' aria-hidden=true'></i>&nbsp;&nbsp;Analyse",
            "Administratie" => "<i class='glyphicon glyphicon-cog' aria-hidden='true'></i>&nbsp;&nbsp;Administratie",
            "Mijn account" => "<i class='glyphicon glyphicon-user' aria-hidden='true'></i>&nbsp;&nbsp;Mijn account",
            "Opdrachten" => "<i class='glyphicon glyphicon-file' aria-hidden='true'></i>&nbsp;&nbsp;Opdrachten",
            "Vragenlijsten" => "<i class='glyphicon glyphicon-th-list' aria-hidden='true'></i>&nbsp;&nbsp;Vragenlijsten",
        ];

        if ($type == 0) {
            $menu_panel = '<div class="panel panel-default">
                           <div class="panel-heading menu-heading">
                                <h4>Menu</h4>
                           </div>
                           <div class="panel-body">%s</div>
                        </div>';

            $menu_options = [
                $item["Inzendingen"] => "/staff/submissions/",
                $item["Leerlingen"] => "/staff/classes/",
                $item["Mijn account"] => "/staff/account/"
            ];
        }
        elseif ($type == 1) {
            $menu_panel = '<div class="panel panel-default">
                           <div class="panel-heading menu-heading">
                                <h4>Menu</h4>
                           </div>
                           <div class="panel-body">%s</div>
                        </div>';

            $menu_options = [
                $item["Beoordelen"] => "/staff/review/",
                $item["Mijn account"] => "/staff/account/"
            ];
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
                $item["Opdrachten"] => "/staff/assignment/",
                $item["Vragenlijsten"] => "/staff/questionnaire/",
                $item["Analyse"] => "/staff/analysis/",
                $item["Administratie"] => "/staff/administration/",
                $item["Mijn account"] => "/staff/account/",
            ];
        }
        return sprintf($menu_panel, $bp->pills($menu_options, $active));
    }

    /**
     * Generates breadcrumbs for use in templates.
     *
     * @param $bp Bootstrap instance
     * @param $path array containing the individual breadcrumb items
     * @return mixed string containing the breadcrumbs as HTML
     */
    public function breadcrumbs($bp, $path){
        return $bp->breadcrumbs($path);
    }

    /**
     * Generate tabs for use in templates.
     *
     * @param $bp Bootstrap instance
     * @param $tabsarray array containing the individual tabs
     * @param string $active string indicating the active tab
     * @return mixed string containing the tabs as HTML
     */
    public function tabs($bp, $tabsarray, $active = 'Info'){
        return $bp->tabs($tabsarray, array(
            'active' => $active,
            'toggle' => "tab",
        ));
    }

    /**
     * Performs a browser redirect by sending out headers.
     *
     * @param $url string containing the URL to redirect to
     * @param int $statusCode Int containing the status code associated (default: 303 Found)
     */
    public function redirect($url, $statusCode = 303) {
        header('Location: ' . $url, true, $statusCode);
        die();
    }

    /**
     * Provides <option> entries for a <select> element
     *
     * @param $array array containing an associative array with id and name values for each option
     * @return string containing the <option> elements
     */
    public function options($array){
        $option = "<option value='%s'>%s</option>";
        $options = "";

        foreach ($array as $key => $value) {
            $options .= sprintf($option, $value["id"], $value["name"]) . "\n";
        }

        return $options;
    }

    /**
     * Provides <option> entries for a <select> element and sets pre-selected options
     *
     * @param $array array containing an associative array with id and name values for each option
     * @param $selected int containing the selected item's id
     * @return string containing the <option> elements
     */
    public function options_selected($array, $selected){
        $option = "<option value='%s'>%s</option>";
        $option_selected = "<option value='%s' selected>%s</option>";
        $options = "";
        foreach ($array as $key => $value) {
            if (in_array($value["id"], $selected)) {
                $options .= sprintf($option_selected, $value["id"], $value["name"]) . "\n";
            }
            else {
                $options .= sprintf($option, $value["id"], $value["name"]) . "\n";
            }
        }
        return $options;
    }

    /**
     * Starts PHP's $_SESSION
     */
    public function get_session(){
        session_start("staff");
    }

    /**
     * Debug function for pretty printing of arrays.
     *
     * @param $array array to be printed
     */
    public function pparray($array){
        echo "<pre>";
        print_r($array);
        echo "</pre>";
        exit();
    }

    /**
     * Debug function for pretty printing of strings.
     *
     * @param $string string to be printed
     */
    public function ppstring($string){
        echo "<pre>";
        echo $string;
        echo "</pre>";
        exit();
    }

    /**
     * Mailer function for sending out system notifications.
     *
     * @param $to string containing the recipient's email address
     * @param $subject string containing the email subject
     * @param $template string indicating which template should be used
     * @return bool indicating if the mail has been sent
     */
    public function mail($to, $subject, $template){
        // Password forgotten
        $mail = new PHPMailer;
        $mail->setFrom('info@tekstmijn.nl', 'Tekstmijn');
        $mail->addAddress($to);
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $info = $this->getUserInfo($to);
        $message = $this->templates->render($template, ["user" => $info]);

        $mail->Body    = $message;
        $mail->AltBody = 'Zet HTML aan in uw e-mailclient.';

        return $mail->send();
    }

    /**
     * Retrieve a user's information
     *
     * @param $username string containing the username
     * @return mixed Array containing the user's full name and type
     */
    public function getUserInfo($username){
        $quoted_username = $this->database->quote($username);
        $query = "SELECT id, CONCAT_WS(' ', firstname, prefix, lastname) as name, setuptoken, type FROM staff WHERE email = $quoted_username";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
    }
}