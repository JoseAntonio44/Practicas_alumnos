<?php
//TODO: Corregir que se quedan varios 'radios' seleccionados.
$num_paginas = intval($_POST['pagina'] ?? 1);
require_once 'validar_sesion.php';

$host = 'localhost';
$dbname = 'gestion_fct';
$user = 'root';
$pass = '';

$total_registros = 0;
$total_paginas = 0;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo $e->getMessage();
} catch (PDOException $err) {
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

    $user = $_SESSION['usuario'];

    $sql = "SELECT nombre FROM alumno WHERE email='$user'";
    $gsent = $pdo->prepare($sql);
    $gsent->execute();

    $nombreUsu = null;
    if ($row = $gsent->fetch(PDO::FETCH_ASSOC)) {
        $nombreUsu = $row['nombre'];
    }

    ?>

    <header>
        <p>Bienvenido <?php echo $nombreUsu ?>!</p>
        <form action="inicio_alumnos.php" method="post">
            <input type="submit" id="boton_logout" name="logout" value="Cerrar Sesión">
            <?php
            if (isset($_POST['logout'])) {
                session_destroy();
                header('Location: login.php');
            }
            ?>
        </form>
    </header>

    <section>
        <div>
            <?php

            //Total registros
            $total_registros_query = $pdo->query("SELECT count(*) FROM alumno");
            $total_registros = $total_registros_query->fetchColumn(); //Para obtener el resultado de la consulta y poder calcular con él

            $registros_pagina = 10;
            //Calculo del número total de páginas

            $total_paginas = ceil($total_registros / $registros_pagina);

            //Pasar de pagina y retroceder
            if ($num_paginas < $total_paginas) {
                if (isset($_POST["siguiente_pagina"])) {

                    $num_paginas++;
                }
            }
            if ($num_paginas > 1) {
                if (isset($_POST["pagina_anterior"])) {
                    $num_paginas--;
                }
            }


            //Ultima y primera pagina
            if (isset($_POST["ultima_pagina"])) {
                $num_paginas = $total_paginas;
            }
            if (isset($_POST["primera_pagina"])) {
                $num_paginas = 1;
            }
            //Calculo del registro desde el que comienza la pagina 
            $registros = ($num_paginas - 1) * $registros_pagina;



            //Tabla para mostrar el estado de la FCT
            $sql = "SELECT comentario, fecha, estado_id 
                    FROM estados_historico 
                    WHERE practica_id IN (SELECT id FROM practica WHERE alumno_id = '$user')";
            //De momento se muestra el estado de la FCT de este alumno pero en un futuro se mostrará del usuario logueado
            $gsent = $pdo->prepare($sql);
            $gsent->execute();
            echo "<table>";
            echo "<tr><th id=\"alumno\" rowspan=\"2\"> <img src=\"IMG/alumno_icono.png\" alt=\"img\"> <h1>alumno</h1> <h1>estado FCT</h1></th><th>Estado</th><th>Comentario</th><th>Fecha</th></tr>";
            while ($row = $gsent->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr><td>" . $row['estado_id'] . "</td><td>" . $row['comentario'] . "</td><td>" . $row['fecha'] . "</td>";
            }
            echo "</table>";


            //Tabla para mostrar los mensajes con las empresas
            $sql = "SELECT p.empresa_id, c.comentario, c.hablado_con, c.hablado_por, c.fecha
            FROM comentario c
            JOIN prioridades p ON p.id = c.prioridad_id
            WHERE p.alumno_id = '$user'";


            $gsent = $pdo->prepare($sql);
            $gsent->execute();
            echo "<table>";
            echo "<tr><th id=\"encabezado_tabla\" colspan=\"7\">Mesajes con empresa</th></tr>";
            echo "<tr><th>Empresa</th><th>Comentario</th><th>Hablado con</th><th>Hablado por</th><th>Fecha</th></tr>";
            while ($row = $gsent->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr><td>"
                    . $row['empresa_id'] . "</td><td>"
                    . $row['comentario'] . "</td><td>"
                    . $row['hablado_con'] . "</td><td>"
                    . $row['hablado_por'] . "</td><td>"
                    . $row['fecha'] . "</td>";
            }
            echo "</table>";

            ?>
        </div>

        <?php
        $nombre = $_POST['empresa'] ?? null;
        //Añade la empresa a la tabla de prioridades
        if ($nombre != null) {
            $sql = "INSERT INTO prioridades (alumno_id, empresa_id) VALUES ('$user', '$nombre')";
            $gsent = $pdo->prepare($sql);
            $gsent->execute();
            echo "<script>alert('La empresa $nombre se ha añadido correctamente a sus prioridades');</script>";
        }

        //Tabla para mostrar las empresas y que el alumno elija la que quiera
        $sql = "SELECT nombre, cif, email, CONCAT_WS(', ', direccion, localidad, provincia) AS direccion, telefono, persona_contacto 
            FROM empresa 
            WHERE nombre NOT IN (SELECT empresa_id FROM prioridades WHERE alumno_id = '$user')
            ORDER BY cif
            LIMIT $registros, $registros_pagina";

        $gsent = $pdo->prepare($sql);
        $gsent->execute();

        echo "<table>";

        echo "<tr><th id='encabezado_tabla' colspan='7'>Elegir empresa</th></tr>";
        echo "<tr><th></th><th>Nombre</th><th>CIF</th><th>Email</th><th>Direccion</th><th>Telefono</th><th>Persona de contacto</th></tr>";
        while ($row = $gsent->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td>"
                //Con la casilla selecciona el alumno la empresa para ponerlo en sus prioridades
                . "<form action='inicio_alumnos.php' method='post'> 
                <input type='hidden' name='empresa' value='" . $row['nombre'] . "'>
                <input type='submit' value='Seleccionar'>
                </form></td><td>"
                . $row['nombre'] . "</td><td>"
                . $row['cif'] . "</td><td>"
                . $row['email'] . "</td><td>"
                . $row['direccion'] . "</td><td>"
                . $row['telefono'] . "</td><td>"
                . $row['persona_contacto'] . "</td>";
        }
        echo "</table>";

        ?>

        <!--Paginación-->
        <form action="inicio_alumnos.php" method="post" id="paginacion">
            <input type="submit" name="primera_pagina" value="<<" <?php ?>>
            <input type="submit" name="pagina_anterior" value="<" <?php ?>>
            <input type="text" name="pagina" value="<?php echo $num_paginas ?>">
            <input type="submit" name="siguiente_pagina" value=">">
            <input type="submit" name="ultima_pagina" value=">>" <?php ?>>
        </form>

        <?php

        ?>
    </section>






</body>

</html>