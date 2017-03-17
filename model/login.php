<?php

function check_login($database, $username, $password){
    $quoted_username = $database->quote($username);
    $query = "SELECT password FROM staff WHERE email = $quoted_username";
    $user = $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];

    return hash_equals($user['password'], crypt($password, $user['password']));
}

function getUserInfo($database, $username){
    $quoted_username = $database->quote($username);
    $query = "SELECT id, CONCAT_WS(' ', firstname, prefix, lastname) as name, setuptoken, type FROM staff WHERE email = $quoted_username";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
}

function getRegistrationInfo($database, $token){
    $quoted_token = $database->quote($token);
    $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) as name, email 
              FROM staff
              WHERE setuptoken = $quoted_token
              AND password IS NULL";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
}

function getResetInfo($database, $token){
    $quoted_token = $database->quote($token);
    $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) as name, email 
              FROM staff
              WHERE setuptoken = $quoted_token";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
}

function hash_password($password){
    $cost = 10;
    $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), "+", ".");
    $salt = sprintf("$2a$%02d$", $cost) . $salt;
    $hash = crypt($password, $salt);
    return $hash;
}

function set_initial_password($database, $username, $password){
    $rows_affected = 0;

    if (strlen($password) > 0){
        $rows_affected = $database->update("staff",
            ["password" => hash_password($password), "setuptoken" => null],
            ["AND" =>
                ["email" => $username, "password" => null]
            ]
        );
    }


    return $rows_affected;
}

function change_password($database, $username, $password){
    $rows_affected = 0;

    if (strlen($password) > 0){
        $rows_affected = $database->update("staff",
            ["password" => hash_password($password),
            "setuptoken" => null],
            ["email" => $username]
        );
    }


    return $rows_affected;
}

function set_setup_token($database, $username){
    $rows_affected = $database->update("staff",
        ["#setuptoken" => "UUID()"],
        ["email" => $username]
    );

    return $rows_affected;
}

function get_setup_token($database, $username){
    return $database->select("staff", ["setuptoken"], ["email" => $username])[0]['setuptoken'];
}

function reset_password($database, $username, $password){
    $rows_affected = 0;

    if (strlen($password) > 0){
        $rows_affected = $database->update("staff",
            ["password" => hash_password($password), "setuptoken" => null],
            ["email" => $username]
        );
    }


    return $rows_affected;
}

