<?php class mail extends model {
    function newuser($user = null){
        if ($user === null) {
            $user = ["name" => "Léon Melein", "token" => "12345"];
        }
        echo $this->templates->render("mail::newuser", ["user" => $user]);
    }

    function newpassword($user = null){
        if ($user === null) {
            $user = ["name" => "Léon Melein", "token" => "12345"];
        }
        echo $this->templates->render("mail::passwordreset", ["user" => $user]);
    }

    function sendMail($to, $subject, $template){
        // Password forgotten
        $mail = new PHPMailer;
        $mail->setFrom('info@tekstmijn.nl', 'Tekstmijn');
        $mail->addAddress($to);
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $info = $this->getUserInfo($_POST['username']);
        $message = $this->templates->render($template, ["user" => $info]);

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