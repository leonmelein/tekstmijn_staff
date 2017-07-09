<?php
/**
 * Account
 *
 * Enabling account actions like password changes and resets.
 */
class account extends model {

    /*
     * Page functions
     */

    /**
     * Displays the account page, enabling users to change their password.
     */
    public function showAccount(){
        $this->get_session();

        // Generate navigational items
        $menu = $this->menu($this->bootstrap, ["active" => "/staff/account/", "align" => "stacked"], $_SESSION['type']);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["staff_name"] => "../account/", "Mijn account" => "#"]);

        echo $this->templates->render("login::account",
            [
                "title" => "Tekstmijn | Mijn account",
                "page_title" => "Mijn account", "menu" => $menu, "breadcrumbs" => $breadcrumbs,
                "name" => $_SESSION["staff_name"], "email" => $_SESSION["staff_email"], "type" => $_SESSION["type"],
                "page_js" => "../vendor/application/register_validate.js"
            ]
        );
    }

    /**
     * Change password according to the user's request.
     */
    public function updateAccount(){
        if($this->change_password($_POST["username"], $_POST["password"])){
            $this->redirect("/staff/account/?password_changed=true");
        } else {
            $this->redirect("/staff/register/?password_changed=true");
        }
    }

    /*
     * Supporting functions
     */

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
        $redir = "/staff/login/?pwd_reset=true";
        $redir_negative = "/staff/reset_password/?failed=true";

        if($this->change_password($_POST["username"], $_POST["password"])){
            $sanitized_email = filter_var($_POST['username'], FILTER_SANITIZE_EMAIL);
            if (filter_var($sanitized_email, FILTER_VALIDATE_EMAIL)) {
                $result = $this->mail(
                    $sanitized_email,
                    "Tekstmijn - Wachtwoord opnieuw ingesteld",
                    "mail::reset_notify"
                );

                if(!$result) {
                    $this->redirect($redir_negative);
                } else {
                    $this->redirect($redir);
                }
            }
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
     * Checks token and loads user info needed for password reset.
     *
     * @param $token string containing the users reset token
     * @return mixed False when the token is invalid or an array containing the user's name and email address if it is valid
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
     * @return bool|int 1 if the password was succesfully set or False if it failed
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
     * Changes the password for a user in the database upon user's request.
     *
     * @param $username string containing the username
     * @param $password string containing the password
     * @return int indicating if the password change succeeded
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
     * Creates a setup token for use in first registration or password reset.
     *
     * @param $username string containing the username
     * @return bool|int False if the user does not exist, 0 if the operation failed or 1 if the operation succeeded
     */
    function set_setup_token($username){
        return $this->database->update("staff",
            ["#setuptoken" => "UUID()"],
            ["email" => $username]
        );
    }

    /**
     * Retrieves the setup token for a given user.
     *
     * @param $username string containing the username
     * @return mixed string containing the setup token if there is one, or False when there is none.
     */
    function get_setup_token($username){
        return $this->database->select("staff", ["setuptoken"], ["email" => $username])[0]['setuptoken'];
    }

    /**
     * Performs the password reset after the reset link has been clicked and the form has been filled in.
     *
     * @param $username string containing the username
     * @param $password string containing the new password
     * @return bool|int False if the user does not exist, 0 if the operation failed or 1 if the operation succeeded
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

}