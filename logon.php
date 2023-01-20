<?php
session_start();

include 'db_con.php';
include 'func.php';

if (empty($_SESSION['csrf_token'])) {
    $csrf_token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $csrf_token;
}

if (isset($_POST['user']) && isset($_POST['pass'])) {

    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $csrf_token = $_POST['csrf_token'];

    // check if the CSRF token is valid
    if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $csrf_token) {
        echo "Invalid CSRF token.";
        exit;
    }

    // prepare the SQL statement
    $stmt = $conn->prepare("SELECT * FROM tb_users WHERE user = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $hashed_password = $row['pass'];

    // Compare the provided password with the hashed password in the database
    if (password_verify($pass, $hashed_password)) {

        // create the cookies
        $cookie1_name = "user";
        $cookie1_value = "$user";
        setcookie($cookie1_name, $cookie1_value, time() + (86400 * 30), "/", "", false, true);

        $cookie2_name = "mail";
        $cookie2_value = $row['mail'];
        setcookie($cookie2_name, $cookie2_value, time() + (86400 * 30), "/", "", false, true);

        $cookie3_name = "id";
        $cookie3_value = $row['user_id'];
        setcookie($cookie3_name, $cookie3_value, time() + (86400 * 30), "/", "", false, true);

        // generate a new CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        header("Location: index.php");
    } else {
        echo "Usuário e/ou senha incorretos";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Entrar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/logon.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="fonts/iconic/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link rel="icon" type="image/png" href="https://www.boardgamefinder.net/assets/images/favicon.ico" sizes="32x32">
</head>

<body class="<?php echo wallpaper(); ?>">
    <div class="container-contact100">
        <div class="wrap-contact100">

            <form class="contact100-form validate-form" _lpchecked="1" method="post">

                <span class="contact100-form-title">Bem vindo</span>

                <div class="wrap-input100 validate-input bg1">
                    <span class="label-input100">USUÁRIO</span>
                    <input class="input100" type="text" name="user" placeholder="" value="" required>
                </div>

                <div class="wrap-input100 validate-input bg1">
                    <span class="label-input100">SENHA</span>
                    <input class="input100" type="password" name="pass" placeholder="" value="" required>
                </div>

                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="container-contact100-form-btn">
                    <button class="contact100-form-btn" formaction="" id="btn" name="btn">
                        <span>
                            Log In
                            <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>
            </form>

            <div class="container-contact100-form-btn">
                <a href="register.php">
                    <button class="contact100-form-btn" formaction="" id="btn" name="btn">
                        <span>
                            Criar Usuário
                        </span>
                        <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
                        </span>
                    </button>
                </a>
            </div>
        </div>
    </div>
</body>

</html>