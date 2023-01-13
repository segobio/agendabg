<?php    
// ----- Including necessary files
include 'db_con.php';
include 'func.php';

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

            $cookie3_name = "id";
            $cookie3_value = "$row[4]";
            setcookie($cookie3_name, $cookie3_value, time() + (86400 * 30), "/");
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

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Logon</title>  
    <meta name="viewport" content="width=device-width, initial-scale=1">    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="fonts/iconic/css/material-design-iconic-font.min.css">	
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" type="text/css" href="css/main.css">

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
                
                <div class="container-contact100-form-btn">
                    <button class="contact100-form-btn" formaction="" id="btn" name="btn">
					    <span>
                            Log In
					        <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
	    				</span>
    				</button>
                </div>                    
            </form>
            <br>
            <a class="flex-c" href="newuser.php"><p>CRIAR USUÁRIO</p></a>

        </div>
    </div>               

</body>
</html>