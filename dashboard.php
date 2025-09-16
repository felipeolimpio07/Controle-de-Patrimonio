<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
        }
        h1 {
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            font-size: 16px;
            color: white;
            background-color: #007bff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            margin-right: 10px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .logout {
            background-color: #dc3545;
        }
        .logout:hover {
            background-color: #a71d2a;
        }
    </style>
</head>
<body>

<h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario']); ?>!</h1>

<a href="colaboradores.php" class="btn">Cadastrar Colaboradores</a>
<a href="listar_colaboradores.php" class="btn">Listar Colaboradores</a>
<a href="materiais.php" class="btn">Cadastrar Materiais</a>
<a href="editar_material.php" class="btn">editar materiais</a> <!-- novo link -->
<a href="associar_materiais.php" class="btn">Associar Materiais a Colaboradores</a>

<a href="logout.php" class="btn logout">Sair</a>




</body>
</html>
