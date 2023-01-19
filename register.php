<?php

include 'db_con.php';

if (isset($_POST['submit']))
{
  $user = $_POST['username'];
  $mail = $_POST['email'];
  $pass = $_POST['password'];
  $pass_confirm = $_POST['password_confirm'];

  // Check if a file has been uploaded
  if (isset($_FILES['user_pic']))
  {
          
    // Get the file information
    $file = $_FILES['user_pic'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
          
    // Get the file extension
    $file_ext = explode('.', $file_name);
    $file_ext = strtolower(end($file_ext));
          
    // Set allowed file extensions
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        
    // Check if the file extension is allowed
    if (in_array($file_ext, $allowed_ext))
    {
      // Check for any errors
      if ($file_error === 0)
      {
        // Check if the file size is less than 2MB
        if ($file_size <= 2000000) {
          // Create a new file name
          $file_name_new = strtolower($user . '.' . $file_ext);                      
          // Set the destination path
          $file_destination = 'img/' . $file_name_new;                      
          // Move the file to the destination path
          move_uploaded_file($file_tmp, $file_destination);
        }
      }
    }
  }

  # Resume the creation of the user
  $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
  $sql = "INSERT INTO tb_users (user, mail, pass, mobile, user_pic) VALUES ( '$user', '$mail', '$hashed_password','', '$file_destination')";
  if ($conn->query($sql) === TRUE) {
    header("Location: logon.php");
    } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}
?>

<head>
  <title>Novo Usuário - GameCorner</title>
  <link rel="stylesheet" type="text/css" media="screen" href="css/register.css">
  <link rel="icon" type="image/png" href="https://www.boardgamefinder.net/assets/images/favicon.ico" sizes="32x32">
</head>

<form method="post" action="register.php" enctype="multipart/form-data">
  <label for="username">Nome de usuário (exibição):</label>
  <input type="text" name="username" id="username" required><br>
  <label for="email">E-mail (para notificações de eventos):</label>
  <input type="email" name="email" id="email" required><br>
  <label for="password">Senha</label>
  <input type="password" name="password" id="password" required><br>
  <label for="password_confirm">Repita a senha</label>
  <input type="password" name="password_confirm" id="password_confirm" required><br>
  <br>
  <label for="user_pic">Forneça uma foto para o perfil:</label>
  <input type="file" name="user_pic" id="user_pic" accept="image/*" required>  
  <br>
  <br>
  <input type="submit" name="submit" value="OK">
</form>
<script>
  var password = document.getElementById("password"),
    confirm_password = document.getElementById("password_confirm");

  function validatePassword() {
    if (password.value != confirm_password.value) {
      confirm_password.setCustomValidity("Passwords Don't Match");
    } else {
      confirm_password.setCustomValidity('');
    }
  }
  password.onchange = validatePassword;
  confirm_password.onkeyup = validatePassword;  
</script>