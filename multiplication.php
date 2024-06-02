<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 
    $ligne_min = isset($_POST['Lmin']) ? intval($_POST['Lmin']) : 0;
    $ligne_max = isset($_POST['Lmax']) ? intval($_POST['Lmax']) : 50;
    $colonne_min = isset($_POST['Cmin']) ? intval($_POST['Cmin']) : 0;
    $colonne_max = isset($_POST['Cmax']) ? intval($_POST['Cmax']) : 50;


    echo "<table>";
    echo "<tr>";
    echo "<th></th>";
    for ($col = $colonne_min; $col <= $colonne_max; $col++) {
       
        echo "<th>{$col}</th>";
    }
    echo "</tr>";

    for ($row = $ligne_min; $row <= $ligne_max; $row++) {
        echo "<tr>";
       
        echo "<th>{$row}</th>";
        for ($col = $colonne_min; $col <= $colonne_max; $col++) {
           
            $value = $row * $col;
           
            $blueClass = ($row + $col) % 2 == 0 ? ' class="blue-background"' : '';
            echo "<td{$blueClass}>{$value}</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
   
    header('Location: formulaire.html');
    exit;
}

?>

<style>
  table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
  }
  th, td {
    padding: 5px;
    text-align: center;
  }
  .blue-background {
    background-color: #add8e6;
  }
</style>