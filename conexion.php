<?php

  $conn = new mysqli("localhost","root", "","parser");
  if ($conn->connect_error) {
    echo $error ->$conn->connect_error;
  }

?>
