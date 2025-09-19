<?php
session_start();

$msg = ''; // Inicializa a variável para a mensagem de erro

// Configuração da conexão ao banco de dados
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "auca_engenharia";

// Cria a conexão
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Lógica de login
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
        
        // Armazena a mensagem de boas-vindas na sessão antes de redirecionar
        $_SESSION['bem_vindo_msg'] = "Bem-vindo ao sistema!";
        
        header("Location: dashboard.php");
        exit();
    } else {
        // A mensagem de erro é atribuída aqui quando o login falha
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
    <link rel="stylesheet" href="css/login.css" />
</head>
<body>

<div style="text-align: center; margin-top: 40px;">
    <img src="imagens/logo_nova.png" alt="Logo" style="max-width: 250px; height: auto; border-radius: 15px;">
</div>

<div class="login-form">
    <h2>Login</h2>

    <?php if ($msg != ''): ?>
        <p class="error-msg"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="usuario">Usuário:</label>
        <input type="text" id="usuario" name="usuario" required>

        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required>

        <button type="submit">Entrar</button>
    </form>
</div>

</body>
</html>