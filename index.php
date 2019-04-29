<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Parser</title>
  </head>
  <body>
    <h1>HOLA</h1>
    <?php
    $archivo = fopen("Facturas.txt","r") or die ("Unable to read the file");

    $info = array();

    while (!feof($archivo)) {
      $traer = fgets($archivo);
      $salto = nl2br($traer);
      $info[] = $salto;
    }

    foreach ($info as $value) {
      $char = str_split($value);
      echo sizeof($char)."</br>";
      echo $value."</br>";
    }

   ?>
  </body>
</html>
