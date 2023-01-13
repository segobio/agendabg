<?php

include 'db_con.php';

if(isset($_POST['submit'])){
    $user = $_POST['username'];
    $mail = $_POST['email'];
    $pass = $_POST['password'];
    $pass_confirm = $_POST['password_confirm'];
    if($pass != $pass_confirm){
        echo "Passwords do not match. Please try again.";
    }
    else{
        //$connection = mysqli_connect("localhost", "root", "", "bgdb");
        //$query = "INSERT INTO tb_users (user, mail, pass) VALUES ('$username', '$email', '$password')";
        $sql = "INSERT INTO tb_users (user, mail, pass, mobile) VALUES ( '$user', '$mail', '$pass', '')";

        //mysqli_query($connection, $query);
        //mysqli_close($connection);
        //header("Location: logon.php");

        if ($conn->query($sql) === TRUE) {           

          header("Location: logon.php");

        } else {
          echo "Error: " . $sql . "<br>" . $conn->error;
      }
      
      $conn->close();

    }
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f2f2f2;
}

h1 {
    text-align: center;
    margin: 40px 0;
}

form {
    width: 400px;
    margin: 0 auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.2);
}

label {
    font-weight: bold;
    display: block;
    margin-bottom: 10px;
}

input[type="text"], input[type="email"], input[type="password"] {
    width: 100%;
    padding: 12px 20px;
    margin: 8px 0;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 4px;
}

input[type="submit"] {
    width: 100%;
    background-color: #4CAF50;
    color: white;
    padding: 14px 20px;
    margin: 8px 0;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

input[type="submit"]:hover {
    background-color: #45a049;
}

</style>

<form method="post" action="register.php">
    <label for="username">Nome de usuário (exibição):</label>
    <input type="text" name="username" id="username"><br>
    <label for="email">Email:</label>
    <input type="email" name="email" id="email"  pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="example@example.com" required><br>
    <label for="password">Password:</label>
    <input type="password" name="password" id="password"><br>
    <label for="password_confirm">Confirm Password:</label>
    <input type="password" name="password_confirm" id="password_confirm"><br>
    <input type="submit" name="submit" value="OK">
</form>
<script>
    var password = document.getElementById("password")
      , confirm_password = document.getElementById("password_confirm");

    function validatePassword(){
      if(password.value != confirm_password.value) {
        confirm_password.setCustomValidity("Passwords Don't Match");
      } else {
        confirm_password.setCustomValidity('');
      }
    }
    password.onchange = validatePassword;
    confirm_password.onkeyup = validatePassword;
</script>