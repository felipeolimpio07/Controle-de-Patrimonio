<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include 'conexao.php';  // arquivo com a conexão ao banco

// Verifica se o parâmetro id foi passado
if (!isset($_GET['id'])) {
    header("Location: listar_materiais.php");
    exit();
}

$id = intval($_GET['id']);
$msg = '';

// Busca o material pelo id
$stmt = $conn->prepare("SELECT nome FROM materiais WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: listar_materiais.php");
    exit();
}

$material = $result->fetch_assoc();
$stmt->close();

// Processa submissão do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);

    if ($nome === '') {
        $msg = "O nome do material não pode ser vazio.";
    } else {
        $stmt_update = $conn->prepare("UPDATE materiais SET nome = ? WHERE id = ?");
        $stmt_update->bind_param("si", $nome, $id);
        if ($stmt_update->execute()) {
            $msg = "Material atualizado com sucesso!";
            $material['nome'] = $nome;
        } else {
            $msg = "Erro ao atualizar material: " . $stmt_update->error;
        }
        $stmt_update->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<title>Editar Material</title>
<style>
    body {
        font-family: Arial, sans-serif;
    }
    .form-container {
        width: 400px;
        margin: 50px auto;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 6px;
        box-shadow: 2px 2px 10px #aaa;
    }
    label {
        display: block;
        margin-top: 15px;
        font-weight: bold;
    }
    input[type="text"] {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        box-sizing: border-box;
    }
    button {
        margin-top: 20px;
        width: 100%;
        padding: 10px;
        background-color: #007bff;
        border: none;
        color: white;
        font-size: 16px;
        cursor: pointer;
        border-radius: 4px;
    }
    button:hover {
        background-color: #0056b3;
    }
    .msg {
        margin-top: 15px;
        text-align: center;
        color: #28a745;
    }
    a {
        display: block;
        text-align: center;
        margin-top: 20px;
        text-decoration: none;
        color: #007bff;
    }
    a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>

<div class="form-container">
    <h2>Editar Material</h2>

    <?php if ($msg): ?>
        <p class="msg"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="nome">Nome do Material:</label>
        <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($material['nome']); ?>">

        <button type="submit">Salvar Alteração</button>
    </form>

    <a href="listar_materiais.php">Voltar à lista de Materiais</a>
</div>

</body>
</html>
