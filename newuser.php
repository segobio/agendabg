<?php    
// ----- Including necessary files
    include 'db_con.php';

    //--------------------------------------------------------------------------------
    //------------------------- I AM CREATING A NEW USER -----------------------------
    //--------------------------------------------------------------------------------
    
    if(isset($_POST['btn'])) {

        $user = $_POST['user'];
        $mail = $_POST['mail'];
        $pass = $_POST['pass'];

        $sql = "INSERT INTO tb_users (user, mail, pass, mobile) VALUES ( '$user', '$mail', '$pass', '')";

        if ($conn->query($sql) === TRUE) {           

            header("Location: logon.php");

        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        
        $conn->close();
    }


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>New User @GameCorner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- FONTS -->
    <link href="https://fonts.googleapis.com/css?family=Righteous|Russo+One&display=swap" rel="stylesheet">
    <!-- END FONTS -->
    <link rel="stylesheet" type="text/css" media="screen" href="css/cadastro.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="main.js"></script>
    <link rel="icon" type="image/png" href="https://www.boardgamefinder.net/assets/images/favicon.ico" sizes="32x32">
</head>
<body>

<div class="cadastro_box">

    <form class="" method="post">

        <div class="cell"><input class="w3-input" type="text" name="user" placeholder="Usuário" value="" required></div>
        <div class="cell"><input class="w3-input" type="text" name="mail" placeholder="e-mail" value="" required></div>
        <div class="cell"><input class="w3-input" type="password" name="pass" placeholder="Senha" value="" required></div>
        <div class="cell"><input class="w3-input" type="password" name="pass2" placeholder="Repetir Senha" value="" required></div>

        <button class="w3-button w3-block w3-blue" formaction="" id="btn" name="btn" class=""><b>Criar Usuário</b></button>

    </form>
    
</div>
    
</body>
</html>