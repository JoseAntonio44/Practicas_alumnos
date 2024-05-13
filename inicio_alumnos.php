<?php
  $host='localhost';
  $dbname='gestion_fct';
  $user='root';
  $pass='';
  
  $total_registros = 0;
  $total_paginas = 0;

  try{
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
  catch(PDOException $err)
  {
   echo "Error: ejecutando consulta SQL.";  
  }
  ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/inicio_alumnosCSS.css">
    <title>Inicio</title>

</head>
<body>
    <?php

    //Tabla para mostrar el estado de la FCT
    $sql = "SELECT comentario, fecha, estado_id FROM estados_historico WHERE practica_id = 4;"; 
    //De momento se muestra el estado de la FCT de este alumno pero en un futuro se mostrará del usuario logueado
    $gsent = $pdo->prepare($sql);
    $gsent->execute();
    echo "<table>";
    	echo "<tr><th>Estado</th><th>Comentario</th><th>fecha</th></tr>";
    	while ($row = $gsent->fetch(PDO::FETCH_ASSOC)) {
      echo "<tr><td>".$row['estado_id']."</td><td>".$row['comentario']."</td><td>".$row['fecha']."</td>";
    }
    echo "</table>";


    //Tabla para mostrar los mensajes con las empresas
    $sql = "SELECT p.empresa_id, c.comentario, c.hablado_con, c.hablado_por, c.fecha
            FROM comentario c
            JOIN prioridades p ON p.id = c.prioridad_id";
            //WHERE p.alumno_id = "correo del alumno";

    $gsent = $pdo->prepare($sql);
    $gsent->execute();
    echo "<table>";
    	echo "<tr><th>Empresa</th><th>Comentario</th><th>hablado_con</th><th>hablado_por</th><th>fecha</th></tr>";
    	while ($row = $gsent->fetch(PDO::FETCH_ASSOC)) {
      echo "<tr><td>"
      .$row['empresa_id']."</td><td>"
      .$row['comentario']."</td><td>"
      .$row['hablado_con']."</td><td>"
      .$row['hablado_por']."</td><td>"
      .$row['fecha']."</td>";
    }
    echo "</table>";

    //Tabla para mostrar las empresas y que el alumno elija la que quiera
    $sql = "SELECT nombre, cif, email, CONCAT_WS('\", \"', direccion, localidad, provincia) AS direccion, telefono, persona_contacto 
            FROM empresa
            limit 10,10";

    $gsent = $pdo->prepare($sql);
    $gsent->execute();

    echo "<table>";
    	echo "<tr><th>Seleccionar</th><th>Nombre</th><th>CIF</th><th>EMAIL</th><th>DIRECCIÓN</th><th>TELEFONO</th><th>PERSONA DE CONTACTO</th></tr>";
    	while ($row = $gsent->fetch(PDO::FETCH_ASSOC)) {
      echo "<tr><td>"
      //Con la casilla selecciona el alumno la empresa para ponerlo en sus prioridades
      ."<form action=\"inicio_alumnos.php\" method=\"post\"> <input type=\"radio\" name=\"empresa\" value=\"".$row['nombre']."\">
      <input type=\"submit\" value=\"Seleccionar\"></form></td><td>"
      .$row['nombre']."</td><td>"
      .$row['cif']."</td><td>"
      .$row['email']."</td><td>"
      .$row['direccion']."</td><td>"
      .$row['telefono']."</td><td>"
      .$row['persona_contacto']."</td>";
    }
    echo "</table>";
    ?>

    <?php

    $nombre = $_POST['empresa'] ?? null;

    if($nombre != null){                                             //aquí iria el correo del alumno logueado
      $sql = "INSERT INTO prioridades (alumno_id, empresa_id) VALUES ('alumno100@example.com', '$nombre')";
      $gsent = $pdo->prepare($sql);
      $gsent->execute();
      echo "Empresa $nombre seleccionada correctamente";
    }
    
    
    ?>



    
</body>
</html>