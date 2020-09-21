<?php $this->layout('mail::base') ?>
<!-- START CENTERED WHITE CONTAINER -->
<span class="preheader">Uw account voor Tekstmijn is klaar voor gebruik.</span>
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
                        <p>Van harte welkom bij Tekstmijn! Via deze e-mail kunt u uw account voor het Tekstmijnsysteem activeren. Door op onderstaande link te klikken kunt u uw account activeren door een wachtwoord in te stellen.</p>
                        <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                            <tbody>
                            <tr>
                                <td align="left">
                                    <table border="0" cellpadding="0" cellspacing="0" align="center">
                                        <tbody>
                                        <tr>
                                            <td> <a href="https://tekstmijn.nl/staff/register/?token=<?=$this->e($user['setuptoken'])?>" target="_blank">Account activeren</a> </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <p>U kunt de website van Tekstmijn altijd bezoeken door te surfen naar tekstmijn.nl/staff.</p>
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
                <span class="apple-link">Uw ontvangt deze eenmalige mail omdat u toegang is verleend tot het Tekstmijnsysteem.</span>
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