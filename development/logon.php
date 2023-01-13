<?php    
// ----- Including necessary files
include 'db_con.php';

if( isset($_POST['user']) && isset($_POST['pass']) ){
    
    $user = $_POST['user'];
    $pass = $_POST['pass'];

    $sql = "SELECT * FROM tb_users WHERE user = '$user' AND pass = '$pass'";
        $query = mysqli_query($conn, $sql);
        $row = mysqli_fetch_row($query);

        if ($row !== NULL) {

            $cookie1_name = "user";
            $cookie1_value = "$user";
            setcookie($cookie1_name, $cookie1_value, time() + (86400 * 30), "/");            

            $cookie2_name = "mail";
            $cookie2_value = "$row[1]";
            setcookie($cookie2_name, $cookie2_value, time() + (86400 * 30), "/");
            header("Location: index.php");
        }
        else{
            echo "ERRORU!!!";
        }
}


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Bem-vindo @GameCorner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="css/cadastro.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="main.js"></script>
    <link rel="icon" type="image/png" href="https://www.boardgamefinder.net/assets/images/favicon.ico" sizes="32x32">
</head>
<body>

<div class="cadastro_box">

    <form class="" method="post">

        <div class="cell"><input class="w3-input" type="text" name="user" placeholder="Usuário" value=""></div>        
        <div class="cell"><input class="w3-input" type="password" name="pass" placeholder="Senha" value=""></div>

        <button class="w3-button w3-block w3-blue" formaction="" id="btn" name="btn" class=""><b>Logar</b></button>
        <a class="delete" href="newuser.php"><p>Novo Usuário</p></a>

    </form>
    
</div>
    
</body>
</html>