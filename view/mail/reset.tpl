<?php $this->layout('mail::base') ?>
<!-- START CENTERED WHITE CONTAINER -->
<span class="preheader">U kunt het wachtwoord voor Tekstmijn met de bijgevoegde link resetten.</span>
<table class="main">

    <!-- START MAIN CONTENT AREA -->
    <tr>
        <td class="wrapper">
            <table border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <img src="https://tekstmijn.nl/mailheader.png"/>
                </tr>
                <tr style="height: 20px;"> </tr>
                <tr>
                    <td>
                        <p>Beste <?php echo htmlentities($user['name']); ?>, <br></p>
                        <p>Er is onlangs verzocht om uw wachtwoord opnieuw in te stellen. Dit kunt u doen via de onderstaande link:</p>
                        <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                            <tbody>
                            <tr>
                                <td align="left">
                                    <table border="0" cellpadding="0" cellspacing="0" align="center">
                                        <tbody>
                                        <tr>
                                            <td> <a href="https://tekstmijn.nl/staff/reset_password/?token=<?=$this->e($user['setuptoken'])?>" target="_blank">Wachtwoord opnieuw instellen</a> </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <p>Heeft u zelf niet verzocht om een wijziging? Dan kunt u deze mail gerust negeren en gebruik blijven maken van uw bestaande gebruikersnaam en wachtwoord.</p>
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
                <span class="apple-link">Uw ontvangt deze eenmalige mail omdat is verzocht uw wachtwoord opnieuw in te stellen.</span>
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