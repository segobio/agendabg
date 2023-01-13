<?php
if(isset($_POST['submit'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    if($password != $password_confirm){
        echo "Passwords do not match. Please try again.";
    }
    else{
        $connection = mysqli_connect("host", "username", "password", "db");
        $query = "INSERT INTO tb_users (username, password) VALUES ('$username', '$password')";
        mysqli_query($connection, $query);
        mysqli_close($connection);
        header("Location: index.php");
    }
}
?>
<form method="post" action="register.php">
    <label for="username">Username:</label>
    <input type="text" name="username" id="username"><br>
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