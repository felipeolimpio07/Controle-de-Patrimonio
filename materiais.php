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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);

    if ($nome == '') {
        $msg = "O nome do material não pode ser vazio.";
    } else {
        $sql = "INSERT INTO materiais (nome) VALUES (?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Erro na preparação da consulta: " . $conn->error);
        }
        $stmt->bind_param("s", $nome);

        if ($stmt->execute()) {
            $msg = "Material cadastrado com sucesso!";
        } else {
            if ($conn->errno == 1062) {
                $msg = "Material já cadastrado.";
            } else {
                $msg = "Erro ao cadastrar material: " . $stmt->error;
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
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; }
        .form-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-shadow: 2px 2px 10px #aaa;
            text-align: center;
        }
        h2 { margin-bottom: 20px; }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            text-align: left;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        button, .btn {
            margin-top: 20px;
            padding: 10px 25px;
            background-color: #007bff;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            font-family: Arial, sans-serif;
        }
        button:hover, .btn:hover {
            background-color: #0056b3;
        }
        p.msg {
            margin-top: 15px;
            color: #d63333;
        }
        p.msg.success {
            color: #28a745;
        }
        a {
            display: inline-block;
            margin-top: 15px;
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        /* Separação entre botões */
        .btn + a, button + .btn {
            margin-left: 10px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Cadastro de Materiais</h2>

    <form method="POST" action="">
        <label for="nome">Nome do Material:</label>
        <input type="text" id="nome" name="nome" required>

        <button type="submit">Cadastrar Material</button>
    </form>

    <?php if ($msg != ''): ?>
        <p class="msg <?php echo strpos($msg, 'sucesso') !== false ? 'success' : '' ?>">
            <?php echo htmlspecialchars($msg); ?>
        </p>
    <?php endif; ?>

    <a href="dashboard.php">Voltar ao Dashboard</a>
</div>

</body>
</html>
