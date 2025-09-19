<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $cargo = $_POST['cargo'];

    $cpf_limpo = preg_replace('/[^0-9]/', '', $cpf);

    if (!validarCPF($cpf_limpo)) {
        $msg = "CPF inválido. Por favor, insira um CPF válido.";
    } else {
        $sql = "INSERT INTO colaboradores (nome, cpf, cargo) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Erro na preparação da consulta: " . $conn->error);
        }
        $stmt->bind_param("sss", $nome, $cpf, $cargo);

        if ($stmt->execute()) {
            $msg = "Colaborador cadastrado com sucesso!";
        } else {
            $msg = "Erro ao cadastrar colaborador: " . $stmt->error;
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
    <title>Cadastrar Colaboradores</title>
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

    <h1>Cadastrar Colaboradores</h1>

    <form method="POST" action="">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required />

        <label for="cpf">CPF:</label>
        <input type="text" id="cpf" name="cpf" maxlength="14" required oninput="mascaraCPF(this)" />

        <label for="cargo">Cargo:</label>
        <input type="text" id="cargo" name="cargo" required />

        <button type="submit">Cadastrar</button>
    </form>

    <?php if ($msg != ''): ?>
        <p class="msg <?php echo strpos($msg, 'sucesso') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($msg); ?>
        </p>
    <?php endif; ?>


</div>

<script>
function mascaraCPF(input) {
    let cpf = input.value.replace(/\D/g, '');
    cpf = cpf.substring(0, 11);

    cpf = cpf.replace(/^(\d{3})(\d)/, "$1.$2");
    cpf = cpf.replace(/^(\d{3})\.(\d{3})(\d)/, "$1.$2.$3");
    cpf = cpf.replace(/\.(\d{3})(\d)/, ".$1-$2");

    input.value = cpf;
}
</script>

</body>
</html>
