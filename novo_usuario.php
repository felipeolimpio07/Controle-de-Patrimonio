<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Configuração da conexão
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "auca_engenharia";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $usuario = trim($_POST['usuario']);
    $senha = trim($_POST['senha']);

    if ($nome == '' || $usuario == '' || $senha == '') {
        $msg = "Por favor, preencha todos os campos.";
    } else {
        // Insere usuário e senha no banco (sem hash para exemplo simples)
        $sql = "INSERT INTO usuarios (nome, usuario, senha) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Erro na preparação da consulta: " . $conn->error);
        }

        $stmt->bind_param("sss", $nome, $usuario, $senha);

        if ($stmt->execute()) {
            $msg = "Usuário cadastrado com sucesso!";
        } else {
            $msg = "Erro ao cadastrar usuário. Talvez o nome de usuário já exista.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Novo Usuário</title>
<link rel="stylesheet" href="css/colaboradores_novo_usuario.css" />
</head>
<body>

<div class="navbar">
    <img src="imagens/logo_nova.png" alt="Logo AUCA" class="logo">
    <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario']); ?>!</h1>
    <a href="logout.php" class="logout">Sair</a>
</div>

<div class="sidebar">
    <a href="colaboradores.php">Cadastrar Colaboradores</a>
    <a href="listar_colaboradores.php">Listar Colaboradores</a>
    <a href="materiais.php">Cadastrar Materiais</a>
    <a href="listar_materiais.php">Editar Materiais</a>
    <a href="novo_usuario.php">Cadastrar novo usuário</a>
    <a href="associar_materiais.php">Associar Materiais a Colaboradores</a>
</div>

<div class="main-content">

    <h1>Cadastrar Novo Usuário</h1>

    <form method="POST" action="">
        <label for="nome">Nome Completo:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="usuario">Usuário:</label>
        <input type="text" id="usuario" name="usuario" required>

        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required>

        <button type="submit">Cadastrar</button>
    </form>

    <?php if ($msg != ''): ?>
        <p class="msg <?php echo strpos($msg, 'sucesso') !== false ? 'success' : ''; ?>"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>


</div>

</body>
</html>
