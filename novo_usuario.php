<?php
session_start();

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
        // Inserir usuário e senha no banco (senha sem hash para exemplo simples)
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
    <meta charset="UTF-8">
    <title>Novo Usuário</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        form { max-width: 400px; margin: auto; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        button { margin-top: 20px; padding: 10px 15px; background-color: #007bff; border: none; color: white; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .msg { margin-top: 20px; text-align: center; font-weight: bold; color: red; }
        .success { color: green; }
    </style>
</head>
<body>

<h2>Cadastrar Novo Usuário</h2>

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

<a href="dashboard.php" style="margin-left:10px; padding:10px 15px; background-color:#6c757d; color:white; border-radius:4px; text-decoration:none; display:inline-block; cursor:pointer;">
    Voltar
</a>

</body>
</html>
