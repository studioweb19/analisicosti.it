<?php
//https://bootsnipp.com/snippets/featured/google-style-login-extended-with-html5-localstorage
include 'config.php';

// usiamo la nostra funzione per avviare una sessione php sicura
if(isset($_POST['username'], $_POST['password'])) {
    $user = (string)$_POST['username'];
    $password = (string)$_POST['password']; // Recupero la password criptata.
    if(login($user, $password, $conn) == true) {
        ob_start(); // ensures anything dumped out will be caught
        // do stuff here

        echo "<script>window.location = './caditoie.php'</script>";
    } else {
        // Login fallito

        header('Location: ./login.php?error=1&tipo=3');
    }
}
?>
<html >
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <!-- Bootstrap Core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

    <!-- login css -->
    <link rel="stylesheet" href="css/login.css">

</head>

<body>

<?php

if(isset($_GET['error']))
{
    echo "<div class='alert alert-danger' role='alert'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> <span class='sr-only'>Errore:</span>User o password errati o inesistenti</div>";

}

?>


<div class="container">
    <div class="card card-container">
        <!-- <img class="profile-img-card" src="//lh3.googleusercontent.com/-6V8xOA6M7BA/AAAAAAAAAAI/AAAAAAAAAAA/rzlHcD0KYwo/photo.jpg?sz=120" alt="" /> -->
        <img id="profile-img" class="img-responsive" src="login.png" />
        <p id="profile-name" class="profile-name-card"></p>
        <form class="form-signin" method="post">
            <span id="reauth-email" class="reauth-email"></span>
            <input type="text" name="username" id="username" class="form-control" placeholder="Username" required autofocus>
            <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
            <!--<div id="remember" class="checkbox">
                <label>
                    <input type="checkbox" value="remember-me"> Remember me
                </label>
            </div>-->
            <button class="btn btn-lg btn-primary btn-block btn-signin" onclick="myFunction()" type="submit">Accedi</button>
        </form><!-- /form -->
        <!--
        <a href="#" class="forgot-password">
            Forgot the password?
        </a>
        -->

        <div class="row">
            <div class="col-xs-12" style="text-align: center;">
                <h4 style="color:white;">Scarica la APP!</h4>
                <a href="https://itunes.apple.com/us/app/titano-spurghi/id1312680106?mt=8&l=it&ls=1" title="App Store" style="margin-right:10px;"><img src="img/badge-appstore.png" alt="App Store" width="150" style="margin-top:5px;" /></a>
                <a href="https://play.google.com/store/apps/details?id=info.inyourlife.titanospurghi" title="Play Store"><img src="img/badge-playstore.png" alt="Play Store" width="150" style="margin-top:5px;" /></a>
            </div>
        </div>

    </div><!-- /card-container -->
</div><!-- /container -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>
