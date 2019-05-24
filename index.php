<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Parser</title>
  </head>
  <body>
    <?php
    $archivo = fopen("Facturas.txt","r") or die ("Unable to read the file");
    try {
      require_once("conexion.php");
    } catch (\Exception $e) {

    }


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
    class Bitacora{
      public $idFactura;
      public $descripcion;
    }

    #Se lee el archivo

    $info = array();

    while (!feof($archivo)) {
      $traer = fgets($archivo);
      $salto = nl2br($traer);
      $info[] = $salto;
    }

    $facturas = array();

    #Se lee cada linea de la factura y se aplica una accion dependiendo 
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
    $stmt = $conn->prepare("ss");
    $facturas = comprobarFacturas($facturas,$conn,$stmt);
    $facturas =  agregarFactura($facturas,$conn,$stmt);
      agregarCliente($facturas,$conn,$stmt);
     //$stmt->close();

     agregarBitacora($facturas,$conn,$stmt);
     $conn->close();

    function comprobarFacturas($facturas,$conn,$stmt){

      try {
        $stmt = $conn->prepare("INSERT INTO producto (idProducto,nombreProd,valor)VALUES (?,?,?)");
        $stmt->bind_param("isd", $tamanioProdDB, $idProdITEM, $valorITEM);
      } catch (\Exception $e) {
        echo $e->getMessage();
      }

      foreach ($facturas as $porRevisar) {
        ?> <hr><?php
        if ( $porRevisar->trailer[0]->totalLineas != sizeof($porRevisar->item)) {
          //echo "Numero de Items y numero de items en el trailer no coinciden";
          $porRevisar->trailer[0]->totalLineas = sizeof($porRevisar->item);
        }
        $totalFactura = 0;

        foreach ($porRevisar->item as $tempItem) {
          $prodDB = array();
          $sql = "Select nombreProd From producto ";
          $result = $conn->query($sql);
          while ($row = $result->fetch_assoc()) {
            $prodDB [] = $row["nombreProd"];
          }
          $tamanioProdDB = sizeof($prodDB);
          $idProdITEM = $tempItem->idProd;
          $valorITEM =$tempItem->valor;
          $cantidadITEM = $tempItem->cantidad;
          //Se calcula el total de la factura sin confiar en el escrito en el trailer
          $totalFactura += ($valorITEM) * ($cantidadITEM);
          if ($posss =array_search($idProdITEM,$prodDB)!= FALSE) {
          }
          else {
            $stmt->execute();
          }
        }

        if ($porRevisar->trailer[0]->total != $totalFactura) {
          $porRevisar->error.= "El total de la factura no es el correcto a la hora de realizar la operacion";
          //echo "HOLI";
        }else {
          //Else en caso de que los valores esten correctos
        }

      } //Fin del foreach que revisa las facturas
      return $facturas;
    }//Fin funcion comprobar Facturas

    function agregarCliente($facturas,$conn,$stmt){
      try {
        $stmt = $conn->prepare("INSERT INTO cliente (idCliente,nombre)VALUES (?,?)");
        $stmt->bind_param("is", $tamanioclientesBD, $cliente);
      } catch (\Exception $e) {
        echo $e->getMessage();
      }

      foreach ($facturas as $facturita) {
        $clientesBD = array();
        $sql = "Select nombre From cliente ";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
          $clientesBD [] = $row["nombre"];
        }
        $tamanioclientesBD = sizeof($clientesBD);

        $cliente = $facturita->header->noCliente;

        if ($posss =array_search($cliente,$clientesBD)!= FALSE) {
        }
        else {
          $stmt->execute();
        }
      }
    }//Fin funcion agregar cliente

    function agregarFactura($facturas,$conn,$stmt){
      try {
        $stmt = $conn->prepare("INSERT INTO numerosfactura (idFactura,numerosFactura)VALUES (?,?)");
        $stmt->bind_param("is", $tamanioNumsFactBD, $numFact);
      } catch (\Exception $e) {
        echo $e->getMessage();
      }

      foreach ($facturas as $facturita) {
        $factBD = array();
        $sql = "Select numerosFactura From numerosfactura ";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
          $factBD [] = $row["numerosFactura"];
        }
        $tamanioNumsFactBD = sizeof($factBD);

        $numFact = $facturita->header->noFactura;

        if ($tamanioNumsFactBD<1) {
          $stmt->execute();
        }else {

        if ($posss =array_search($numFact,$factBD)!= FALSE) {
          $facturita->error .= "  La factura ya existía";
        }
        else {
          $stmt->execute();
        }
      }
    }

    return $facturas;

    }//Fin de la funcion agregar Factura

    function agregarBitacora($facturas,$conn,$stmt){

      foreach ($facturas as $facturita) {
        $idFactura = $facturita->header->noFactura;
        $descripcion = $facturita->error;
        $idFacturaDB = 0;

        try {
          $stmt = $conn->prepare("SELECT idFactura FROM numerosfactura WHERE numerosFactura = ?");
          $stmt->bind_param("s", $idFactura);
          $stmt->execute();
          $result = $stmt->get_result();

          $idFacturaDB = $result -> fetch_assoc();

        } catch (\Exception $e) {
          echo $e->getMessage();
        }


        try {
          $stmt = $conn->prepare("INSERT INTO bitacora (idFactura,descripcion)VALUES (?,?)");
          $stmt->bind_param("is", $idFacturaDB ["idFactura"], $descripcion);
        } catch (\Exception $e) {
          echo $e->getMessage();
        }

        $stmt->execute();
      }

    }

   ?>
  </body>
</html>
