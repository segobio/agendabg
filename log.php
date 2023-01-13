<!DOCTYPE html>
<html>
<head>
<style> 
.entry:nth-of-type(odd) {
  background: lightgray;
}

.entry:nth-of-type(even) {
  background: white;
}

</style>
</head>
<body>

<?php
$file = fopen("log.txt", "r");

//Output lines until EOF is reached
while(! feof($file)) {
  $line = fgets($file);
  echo "<span class='entry'>$line</span>";
  echo "<br>";
}

fclose($file);
?>

</body>
</html>

