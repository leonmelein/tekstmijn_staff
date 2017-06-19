<?php

/**
 * Created by PhpStorm.
 * User: leon
 * Date: 19-06-17
 * Time: 00:05
 */
class auth extends model {

    /*
     * Routing functions
     */

    /**
     * Handles user login and password reset requests
     */
    public function login(){
        $type_redir = [
            0 => "../submissions/",
            1 => "../review/",
            2 => "../review/"
        ];

        if ($_POST['password_forgotten'] == 1) {
            $this->send_reset_link();
        } else {
            // Regular login
            if($this->check_login($_POST['username'], $_POST['password'])){
                $info = $this->getUserInfo($_POST['username']);

                $this->get_session();
                $_SESSION['staff_id'] = $info["id"];
                $_SESSION['type'] = $info["type"];
                $_SESSION["staff_email"] = $_POST["username"];
                $_SESSION['staff_name'] = $info["name"];
                $this->redirect($type_redir[$info['type']]);

            } else {
                $this->redirect("/staff/login/?failed=true");
            }
        }
    }

    /**
     * Handles user logout requests
     */
    public function logout(){
        $this->get_session();
        session_destroy();
        $this->redirect("/staff/login/?logged_out=true");
    }

    /**
     * Loads the user's info on first registration
     */
    public function startRegistration(){
        $registration = $this->getRegistrationInfo($_GET["token"]);

        if($registration){
            echo $this->templates->render("login::register",
                ["title" => "Tekstmijn | Registreren",
                    "name" => $registration["name"],
                    "email" => $registration["email"],
                    "page_js" => "../vendor/application/register_validate.js"
                ]
            );
        } else {
            $this->redirect("/staff/login/?failed_registration=true");
        }
    }

    /**
     * Sets the user's password on first registration
     */
    public function completeRegistration(){
        if($this->set_initial_password($_POST["username"], $_POST["password"])){
            $this->redirect("/staff/login/?registration=true");
        } else {
            $this->redirect("/staff/register/?failed=true");
        }
    }

    /**
     * Loads the users info on password reset
     */
    public function startPasswordReset(){
        $registration = $this->getResetInfo($_GET["token"]);

        if($registration){
            echo $this->templates->render("login::reset",
                ["title" => "Tekstmijn | Registreren",
                    "name" => $registration["name"],
                    "email" => $registration["email"],
                    "page_js" => "../vendor/application/register_validate.js"
                ]
            );
        } else {
            $this->redirect("/staff/login/?pwd_reset=false");
        }
    }

    /**
     * Resets the users password on reset
     */
    public function completePasswordReset(){
        if($this->change_password($_POST["username"], $_POST["password"])){
            $this->redirect("/staff/login/?pwd_reset=true");
        } else {
            $this->redirect("/staff/reset_password/?failed=true");
        }
    }

    /**
     * Checks if a user is (still) logged in
     */
    public function checkLogin(){
        $this->get_session();
        if (!isset($_SESSION['staff_id'])) {
            $this->redirect("/staff/login");
            exit();
        }
    }

    /**
     * Checks if a setup token is present on first registration
     */
    public function checkToken(){
        if (!isset($_GET["token"])) {
            $this->redirect("/staff/login?failed_registration=true");
            exit();
        }
    }

    /*
     * Supporting functions
     */

    /**
     * Check if user's credentials are valid
     *
     * @param $username string containing the username
     * @param $password string containing the password
     * @return bool boolean indicating if the credentials are valid
     */
    private function check_login($username, $password){
        $retrievedPassword = $this->database->get("staff", "password", ["email" => $username]);
        return hash_equals($retrievedPassword, crypt($password, $retrievedPassword));
    }

    /**
     * Retrieve a user's information
     *
     * @param $username string containing the username
     * @return mixed Array containing the user's full name and type
     */
    private function getUserInfo($username){
        $quoted_username = $this->database->quote($username);
        $query = "SELECT id, CONCAT_WS(' ', firstname, prefix, lastname) as name, setuptoken, type FROM staff WHERE email = $quoted_username";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    /**
     * Retrieves a user's information for use during registration
     *
     * Checks if a user is eligble to register and if so, retrieves its information
     *
     * @param $token string containing the setup token
     * @return mixed Array containing the user's full name and email address
     */
    function getRegistrationInfo($token){
        $quoted_token = $this->database->quote($token);
        $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) as name, email 
              FROM staff
              WHERE setuptoken = $quoted_token
              AND password IS NULL";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    /**
     *
     * @param $token
     * @return mixed
     */
    function getResetInfo($token){
        $quoted_token = $this->database->quote($token);
        $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) as name, email 
              FROM staff
              WHERE setuptoken = $quoted_token";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    /**
     * @param $password
     * @return string
     */
    function hash_password($password){
        $cost = 10;
        $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), "+", ".");
        $salt = sprintf("$2a$%02d$", $cost) . $salt;
        $hash = crypt($password, $salt);
        return $hash;
    }

    /**
     * @param $username
     * @param $password
     * @return bool|int
     */
    function set_initial_password($username, $password){
        $rows_affected = 0;

        if (strlen($password) > 0){
            $rows_affected = $this->database->update("staff",
                ["password" => $this->hash_password($password), "setuptoken" => null],
                ["AND" =>
                    ["email" => $username, "password" => null]
                ]
            );
        }


        return $rows_affected;
    }

    /**
     * @param $username
     * @param $password
     * @return int
     */
    function change_password($username, $password){
        $rows_affected = 0;

        if (strlen($password) > 0){
            $rows_affected = $this->database->update("staff",
                ["password" => $this->hash_password($password),
                    "setuptoken" => null],
                ["email" => $username]
            );
        }


        return $rows_affected;
    }

    /**
     * @param $username
     * @return bool|int
     */
    function set_setup_token($username){
        return $this->database->update("staff",
            ["#setuptoken" => "UUID()"],
            ["email" => $username]
        );
    }

    /**
     * @param $username
     * @return mixed
     */
    function get_setup_token($username){
        return $this->database->select("staff", ["setuptoken"], ["email" => $username])[0]['setuptoken'];
    }

    /**
     * @param $username
     * @param $password
     * @return bool|int
     */
    function reset_password($username, $password){
        $rows_affected = 0;

        if (strlen($password) > 0){
            $rows_affected = $this->database->update("staff",
                ["password" => hash_password($password), "setuptoken" => null],
                ["email" => $username]
            );
        }


        return $rows_affected;
    }

    /**
     *
     */
    function send_reset_link(){
        $sanitized_email = filter_var($_POST['username'], FILTER_SANITIZE_EMAIL);
        if (filter_var($sanitized_email, FILTER_VALIDATE_EMAIL)) {
            $this->set_setup_token($sanitized_email);

            // Password forgotten
            $mail = new PHPMailer;
            $mail->setFrom('info@tekstmijn.nl', 'Project Tekstmijn');
            $mail->addAddress($sanitized_email);
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Project Tekstmijn - Wachtwoord wijzigen';
            $token = $this->get_setup_token($sanitized_email);
            // Retrieve the email template required
            $message = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/mail/reset.html');
            // Replace the % with the actual information
            $info = $this->getUserInfo($_POST['username']);
            $message = str_replace('%name%', $info['name'], $message);
            $message = str_replace('%link%', $token, $message);


            $mail->Body    = $message;
            $mail->AltBody = 'Zet HTML aan in uw e-mailclient.';

            if(!$mail->send()) {
                $this->redirect("/staff/login/?reset=false");
            } else {
                echo $mail->ErrorInfo;
                $this->redirect("/staff/login/?reset=true");
            }
        }
    }

}