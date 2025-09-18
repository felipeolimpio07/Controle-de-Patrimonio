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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #808080; /* fundo cinza */
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-form {
            width: 320px;
            padding: 30px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-shadow: 2px 2px 12px #aaa;
            background-color: white;
            text-align: center;
        }
        .login-form img {
            max-width: 250px;
            margin: 20px auto 30px auto;
            display: block;
            height: auto;
        }
        .login-form h2 {
            margin-bottom: 25px;
        }
        .login-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            text-align: left;
        }
        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .login-form input[type="text"]:focus,
        .login-form input[type="password"]:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0,123,255,0.5);
        }
        .login-form button {
            width: 100%;
            background-color: #3A943B;
            border: none;
            color: white;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .login-form button:hover {
            background-color: #008000;
        }
        .error-msg {
            color: red;
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<body>

<div style="text-align: center; margin-top: 40px;">
<img src="imagens/logo_nova.png" alt="Logo" style="max-width: 250px; height: auto; border-radius: 15px;">

    <head>
    <meta charset="UTF-8" />
    <title>Título da Página</title>
    <link rel="stylesheet" href="css/styles.css">
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
