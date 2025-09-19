<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "auca_engenharia";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$msg = '';
$msgClass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $origem = trim($_POST['origem']);

    if ($nome === '' || $origem === '') {
        $msg = "O nome e a origem do material não podem ser vazios.";
        $msgClass = "msg error";
    } else {
        $sql = "INSERT INTO materiais (nome, origem) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Erro na preparação da consulta: " . $conn->error);
        }
        $stmt->bind_param("ss", $nome, $origem);

        if ($stmt->execute()) {
            $msg = "Material cadastrado com sucesso!";
            $msgClass = "msg success";
        } else {
            if ($conn->errno == 1062) {
                $msg = "Material já cadastrado.";
                $msgClass = "msg error";
            } else {
                $msg = "Erro ao cadastrar material: " . htmlspecialchars($stmt->error);
                $msgClass = "msg error";
            }
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
    <title>Cadastro de Materiais</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="css/colaboradores_novo_usuario.css" />
</head>
<body>

<div class="navbar">
    <img src="imagens/logo_nova.png" alt="Logo AUCA" class="logo" />
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
    <h2>Cadastro de Materiais</h2>

    <?php if ($msg !== ''): ?>
        <p class="<?php echo $msgClass; ?>">
            <?php echo htmlspecialchars($msg); ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="nome">Nome do Material:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="origem">Origem do Material:</label>
        <input type="text" id="origem" name="origem" placeholder="Escritório ou obra ?" required>

        <button type="submit">Cadastrar Material</button>
    </form>

</div>

</body>
</html>
