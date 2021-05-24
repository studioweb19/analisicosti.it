<html >
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <!-- Bootstrap Core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

    <!-- login css -->
    <link rel="stylesheet" href="css/login.css">

</head>

<body>

<div class="container">
    <div class="card card-container">
        <!-- <img class="profile-img-card" src="//lh3.googleusercontent.com/-6V8xOA6M7BA/AAAAAAAAAAI/AAAAAAAAAAA/rzlHcD0KYwo/photo.jpg?sz=120" alt="" /> -->
        <img id="profile-img" class="img-responsive" src="login.png" />
        <p id="profile-name" class="profile-name-card"></p>

        <div id="responsologin"></div>
        <form class="form-signin" id="loginform" enctype="application/x-www-form-urlencoded" name="loginform" action="javascript:loggati();">
            <input type="text" name="matricolalogin" id="matricolalogin" class="form-control" placeholder="Username" required autofocus>
            <input type="password" id="passwordlogin" name="passwordlogin" class="form-control" placeholder="Password" required>
            <button class="btn btn-lg btn-primary btn-block btn-signin" type="submit">Accedi</button>
        </form>

        <!--
        <a href="#" class="forgot-password">
            Forgot the password?
        </a>
        -->


    </div><!-- /card-container -->
</div><!-- /container -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script type="text/javascript">
    function loggati(){
        $.post("ajax_login.php", $("#loginform").serialize(), function(msg){$("#responsologin").html(msg);} );
    }
</script>
</body>
</html>
