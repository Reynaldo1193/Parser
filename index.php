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

    class Factura
    {
      public $header;
      public $item = array();
      public $trailer;
      public $error = "";

    }

    $info = array();

    while (!feof($archivo)) {
      $traer = fgets($archivo);
      $salto = nl2br($traer);
      $info[] = $salto;
    }

    $facturas = array();

    foreach ($info as $value) {
      $char = str_split($value);
      switch ($char[0]) {
        case 'H':
          $factura = new Factura();
          $noFactura = array_slice($char,1,8);
          $noFactura = implode($noFactura);
          $noFactura = trim($noFactura);
          $noCliente = array_slice($char,9,6);
          $noCliente = implode($noCliente);
          $noCliente = trim($noCliente);
          $fecha = array_slice($char,15,8);
          $fecha = implode($fecha);
          $fecha = trim($fecha);
          $moneda = array_slice($char,23,3);
          $moneda = implode($moneda);
          $moneda = trim($moneda);
          $header = new Header($noFactura,$noCliente,$fecha,$moneda);
          $factura->header = $header;
          break;
        case 'I':
          $idProd = array_slice($char,1,7);
          $idProd = implode($idProd);
          $idProd = trim($idProd);
          $antiguedad = array_slice($char,8,2);
          $antiguedad = implode($antiguedad);
          $antiguedad = trim($antiguedad);
          $cantidad = array_slice($char,12,4);
          $cantidad = implode($cantidad);
          $cantidad = trim($cantidad);
          $cantidad = (int)$cantidad;
          $valor = array_slice($char,17,8);
          $valor = implode($valor);
          $valor = trim($valor);
          $valor = (float)$valor;
          $item = new Item($idProd,$antiguedad,$cantidad,$valor);
          $factura->item[] = $item;
          break;
        case 'T':
          $totalLineas = array_slice($char,2,1);
          $totalLineas = implode($totalLineas);
          $totalLineas = trim($totalLineas);
          $totalLineas = (int)$totalLineas;
          $total = array_slice($char,7,7);
          $total = implode($total);
          $total = trim($total);
          $total = (float)$total;
          $trailer = new Trailer($totalLineas,$total);
          $factura->trailer[] = $trailer;
          $facturas[] = $factura;
          break;

        default:
          break;
      }
    }

    ?>
    <pre>
      <?php var_dump($facturas); ?>
    </pre>
    <?php

    comprobarFacturas($facturas);

    function comprobarFacturas($facturas){
      foreach ($facturas as $porRevisar) {
        ?> <hr><?php
        if ( $porRevisar->trailer[0]->totalLineas != sizeof($porRevisar->item)) {
          echo "Numero de Items y numero de items en el trailer no coinciden";
          $porRevisar->trailer[0]->totalLineas = sizeof($porRevisar->item);
        }
        $totalFactura = 0;

        foreach ($porRevisar->item as $tempItem) {
          //Se calcula el total de la factura sin confiar en el escrito en el trailer
          $totalFactura += ($tempItem->valor) * ($tempItem->cantidad);
        }

        if ($porRevisar->trailer[0]->total != $totalFactura) {
          $porRevisar->error.= " El total de la factura no es el correcto a la hora de realizar la operacion";
          echo $porRevisar->error;
        }else {
          //Else en caso de que los valores esten correctos
        }

      } //Fin del foreach que revisa las facturas
    }



   ?>
  </body>
</html>
