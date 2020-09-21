<?php
/**
 * Auth
 *
 * Handles authentication of users.
 */
class auth extends model {

    /*
     * Routing functions
     */

    /**
     * Redirects homepage to login page.
     */
    public function homepage(){
        $this->redirect("/staff/login");
    }

    /**
     * Renders login page.
     */
    public function loginpage(){
        echo $this->templates->render("login::login", ["title" => "Tekstmijn | Inloggen"]);
    }

    /**
     * Renders password reset request page.
     */
    public function requestReset(){
        echo $this->templates->render("login::request_reset", ["title" => "Tekstmijn | Wachtwoord vergeten?"]);
    }

    public function sendResetLink(){
        $this->set_setup_token($_POST['username']);
        $this->send_reset_link();
    }

    /**
     * Handles user login and password reset requests.
     */
    public function login(){
        $type_redir = [
            0 => "../submissions/",
            1 => "../review/",
            2 => "../review/"
        ];

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

    /**
     * Handles user logout requests.
     */
    public function logout(){
        $this->get_session();
        session_destroy();
        $this->redirect("/staff/login/?logged_out=true");
    }

    /**
     * Checks if a user is (still) logged in.
     */
    public function checkLogin(){
        $this->get_session();
        if (!isset($_SESSION['staff_id'])) {
            $this->redirect("/staff/login");
            exit();
        }
    }

    /**
     * Checks if a setup token is present on first registration.
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
     * Check if user's credentials are valid.
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
     * Retrieves a user's information for use during registration.
     *
     * Checks if a user is eligible to register and if so, retrieves its information
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
     * Retrieved relevant user info for a valid setup token.
     *
     * @param $token string containing the reset token
     * @return array containing the user's name and username or False if the token is invalid
     */
    function getResetInfo($token){
        $quoted_token = $this->database->quote($token);
        $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) as name, email 
              FROM staff
              WHERE setuptoken = $quoted_token";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    /**
     * Hashes the password with salt on first registration to enable safe storage and login.
     *
     * @param $password String containing the chosen password
     * @return string containing the hashed, salted password for storage in the user database
     */
    function hash_password($password){
        $cost = 10;
        $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), "+", ".");
        $salt = sprintf("$2a$%02d$", $cost) . $salt;
        $hash = crypt($password, $salt);
        return $hash;
    }

    /**
     * Sets the password for a user upon first registration.
     *
     * @param $username String containing the username
     * @param $password String containing the password
     * @return bool|int indicating if the operation succeeded
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
     * Changes password upon user's request.
     *
     * @param $username string containing the username
     * @param $password string containing the password
     * @return int indicating if the operation succeeded
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
     * Inserts a setup token for either a new user or a user that has requested a password reset.
     *
     * @param $username string containing the username
     * @return bool|int indicating if the operation succeeded
     */
    function set_setup_token($username){
        return $this->database->update("staff",
            ["#setuptoken" => "UUID()"],
            ["email" => $username]
        );
    }

    /**
     * Retrieves the setup token for a given user to check for validity.
     *
     * @param $username
     * @return mixed
     */
    function get_setup_token($username){
        return $this->database->select("staff", ["setuptoken"], ["email" => $username])[0]['setuptoken'];
    }

    /**
     * Resets the password after a valid reset request.
     *
     * @param $username string containing the username
     * @param $password string containing the password
     * @return bool|int
     */
    function reset_password($username, $password){
        $rows_affected = 0;

        if (strlen($password) > 0){
            $rows_affected = $this->database->update("staff",
                ["password" => $this->hash_password($password), "setuptoken" => null],
                ["email" => $username]
            );
        }


        return $rows_affected;
    }

    /**
     * Sends out the password reset token to the user's email address.
     */
    function send_reset_link(){
        $sanitized_email = filter_var($_POST['username'], FILTER_SANITIZE_EMAIL);
        if (filter_var($sanitized_email, FILTER_VALIDATE_EMAIL)) {
            $result = $this->mail($sanitized_email, "Tekstmijn - Wachtwoord wijzigen", "mail::reset");
            if(!$result) {
                $this->redirect("/staff/forgot/?reset=false");
            } else {
                $this->redirect("/staff/forgot/?reset=true");
            }
        } else {
            $this->redirect("/staff/forgot/?reset=false");
        }
    }

}