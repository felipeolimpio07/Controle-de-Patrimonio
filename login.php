<?php
session_start();

$msg = ''; // Inicializa variável msg para evitar warnings

// Configuração da conexão ao banco de dados
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "auca_engenharia";

// Cria conexão
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Verifica conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];


    $sql = "SELECT * FROM usuarios WHERE usuario = ? AND senha = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Erro na preparação da consulta: " . $conn->error);
    }
    $stmt->bind_param("ss", $usuario, $senha);
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows == 1) {
        $_SESSION['usuario'] = $usuario;
        header("Location: dashboard.php");
        exit();
    } else {
        $msg = "Usuário ou senha incorretos.";
    }


    $stmt->close();
}


$conn->close();
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Tela de Login</title>
    <link rel="stylesheet" href="css/dashboard.css" />

</head>
<body>

<body>

<div style="text-align: center; margin-top: 40px;">
<img src="imagens/logo_nova.png" alt="Logo" style="max-width: 250px; height: auto; border-radius: 15px;">

    <head>
    <meta charset="UTF-8" />
    <title>Título da Página</title>
    <link rel="stylesheet" href="css/unico.css">
</head>

</div>

<div class="login-form">
    <h2>Login</h2>
    <form method="POST" action="">
        <label for="usuario">Usuário:</label>
        <input type="text" id="usuario" name="usuario" required>

        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required>

        <button type="submit">Entrar</button>
    </form>

    <?php if ($msg != ''): ?>
        <p class="error-msg"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>
</div>


</body>


</body>
</html>
