<?php
/* TODO
 *
 * sql,
 */

function chrono() {
    $temps = explode(' ', microtime());
    return $temps[0] + $temps[1];
}
$debut = chrono();
session_start();
require_once('fn/register.php');

if(isset($_POST['run']) && isset($_POST['token'])) {
    foreach($_POST as $key => $value) {
        $_POST[$key] = htmlentities($value, ENT_QUOTES);
    }

    if($_POST['token'] == $_SESSION['form_token']) {
        if(isMailValid($_POST['email'])) {
            $res = "Ok !";
        }
    } else {
        $res = "BAD TOKEN";
    }
}

$token = sha1(uniqid());
$_SESSION['form_token'] = $token;

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE9">
    <meta name="msapplication-config" content="none" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css" />
    <link rel="icon" href="favicon.ico">
    <link rel="shortcut icon" type="img/png" href="content/img/favicon.png">
    <title>Kubithon</title>
    <script src="js/javascript.js"></script>
</head>

<body onload="Catch()">
    <section></section>
    <section id="content">
        <div id="c_summary" class="col">
            <img src="content/img/favicon.png" alt="Logo" id="logo" />
            <h1>Kubithon</h1>
            <div><i>Kubithon est un évenement sur Minecraft de collecte de fond pour le téléthon 2017 qui se déroule le 8 et 9 décembre !</i></div>
        </div><!--
     --><div id="c_form" class="col">
            <div class="f_title">Vous souhaitez plus d'infos ?</div>
            <div class="f_subtitle">Inscrivez-vous et recevez-les en avant première !</div>
            <form method="post" action="/">
                <input name="email" type="email" id="email" required placeholder="Adresse Mail" <?php if(isset($res)) echo 'value="' . $res . '"'; ?>/><br />
                <input type="submit" value="S'inscrire à la newsletter" name="run" />
                <input type="hidden" name="token" value="<?php echo $token; ?>" />
            </form>
        </div>
    </section>
    <?php echo '<!--['.round(chrono()-$debut,6).']-->'; ?>
</body>
</html>
