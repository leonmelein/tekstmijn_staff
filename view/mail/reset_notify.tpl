<?php $this->layout('mail::base') ?>
<!-- START CENTERED WHITE CONTAINER -->
<span class="preheader">Het wachtwoord voor uw Tekstmijn account is opnieuw ingesteld.</span>
<table class="main">

    <!-- START MAIN CONTENT AREA -->
    <tr>
        <td class="wrapper">
            <table border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <img src="https://tekstmijn.nl/staff/assets/img/mailheader.png"/>
                </tr>
                <tr style="height: 20px;"> </tr>
                <tr>
                    <td>
                        <p>Beste <?php echo htmlentities($user['name']); ?>, <br></p>
                        <p>Zojuist is uw wachtwoord voor Tekstmijn opnieuw ingesteld. Als u hier zelf om had verzocht, dan kunt u dit bericht negeren.</p>
                        <p>Heeft u niet zelf verzocht om een wijziging? Neem dan direct contact met ons op via <a href="mailto:info@tekstmijn.nl">info@tekstmijn.nl</a>.</p>
                        <p>Met vriendelijke groet,</p>
                        <p>Het <span class="brand">Tekstmijn</span> Team<br>
                            <a href="mailto:info@tekstmijn.nl">info@tekstmijn.nl</a><br>
                            <a href="https://tekstmijn.nl/staff">tekstmijn.nl/staff</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- END MAIN CONTENT AREA -->
</table>
<!-- START FOOTER -->
<div class="footer">
    <table border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td class="content-block">
                <span class="apple-link">Uw ontvangt deze notificatie omdat uw wachtwoord opnieuw is ingesteld.</span>
            </td>
        </tr>
        <tr>
            <td class="content-block powered-by">
                Tekstmijn is een project van het Center for Communication and Language, Rijksuniversiteit Groningen.
            </td>
        </tr>
    </table>
</div>
<!-- END FOOTER -->