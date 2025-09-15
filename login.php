<?php
session_start();

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

$msg = '';

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
    <meta charset="UTF-8">
    <title>Tela de Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .login-form {
            width: 300px;
            margin: 80px auto;
            padding: 30px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-shadow: 2px 2px 12px #aaa;
        }
        .login-form h2 {
            text-align: center;
            margin-bottom: 25px;
        }
        .login-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }
        .login-form button {
            width: 100%;
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
        }
        .login-form button:hover {
            background-color: #0056b3;
        }
        .error-msg {
            color: red;
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>

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
</html>
