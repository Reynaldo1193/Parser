<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Parser</title>
  </head>
  <body>
    <?php
    $archivo = fopen("Facturas.txt","r") or die ("Unable to read the file");

    class Header
    {
      public $noFactura;
      public $noCliente;
      public $fecha;
      public $moneda;

      function __construct($noFactura,$noCliente,$fecha,$moneda)
      {
        $this->noFactura = $noFactura;
        $this->noCliente = $noCliente;
        $this->fecha = $fecha;
        $this->moneda = $moneda;
      }
    }

    class Item
    {
      public $idProd;
      public $antiguedad;
      public $cantidad;
      public $valor;

      function __construct($idProd,$antiguedad,$cantidad,$valor)
      {
        $this->idProd = $idProd;
        $this->antiguedad = $antiguedad;
        $this->cantidad = $cantidad;
        $this->valor = $valor;
      }
    }

    class Trailer
    {
      public $totalLineas;
      public $total;


      function __construct($totalLineas,$total)
      {
        $this->totalLineas = $totalLineas;
        $this->total = $total;
      }
    }

    $info = array();

    while (!feof($archivo)) {
      $traer = fgets($archivo);
      $salto = nl2br($traer);
      $info[] = $salto;
    }

    foreach ($info as $value) {
      $char = str_split($value);
      switch ($char[0]) {
        case 'H':
        echo "</br>Header</br>";
          $noFactura = array_slice($char,1,8);
          $noFactura = implode($noFactura);
          $noCliente = array_slice($char,9,6);
          $noCliente = implode($noCliente);
          $fecha = array_slice($char,-18,8);
          $fecha = implode($fecha);
          $moneda = array_slice($char,-10,3);
          $moneda = implode($moneda);
          echo "</br>".$noFactura."</br>".$noCliente."</br>".$fecha."</br>".$moneda."</br></br>";
          //$header = new Header();
          break;
        case 'I':
          echo "<br>Item</br>";
          $idProd = array_slice($char,1,8);
          $idProd = implode($idProd);
          $antiguedad = array_slice($char,9,1);
          $antiguedad = implode($antiguedad);
          $cantidad = array_slice($char,12,4);
          $cantidad = implode($cantidad);
          $valor = array_slice($char,17,8);
          $valor = implode($valor);
          $valor = trim($valor);
          echo "</br>".$idProd."</br>".$antiguedad."</br>".$cantidad."</br>".$valor."</br></br>";
          var_dump($valor)."";
          break;
        case 'T':
          echo "<br>Trailer";
          break;

        default:
          echo "<br>Cosas extraños <br>";
          break;
      }
    }



   ?>
  </body>
</html>
