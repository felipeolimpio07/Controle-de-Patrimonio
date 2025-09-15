<?php
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $cargo = $_POST['cargo'];  // Novo campo

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

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Colaboradores</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .form-container { width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 6px; box-shadow: 2px 2px 10px #aaa; }
        h2 { text-align: center; margin-bottom: 20px; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input[type="text"] { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        button { margin-top: 20px; width: 100%; padding: 10px; background-color: #28a745; border: none; color: white; font-size: 16px; cursor: pointer; border-radius: 4px; }
        button:hover { background-color: #218838; }
        .msg { margin-top: 20px; text-align: center; color: #d63333; }
        .msg.success { color: #28a745; }
        a { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #007bff; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Cadastro de Colaboradores</h2>

    <form method="POST" action="">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="cpf">CPF:</label>
        <input type="text" id="cpf" name="cpf" maxlength="14" required>

        <label for="cargo">Cargo:</label>
        <input type="text" id="cargo" name="cargo" required>

        <button type="submit">Cadastrar</button>
    </form>

    <?php if ($msg != ''): ?>
        <p class="msg <?php echo strpos($msg, 'sucesso') !== false ? 'success' : '' ?>"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>

    <a href="dashboard.php">Voltar ao Dashboard</a>
</div>

</body>
</html>
