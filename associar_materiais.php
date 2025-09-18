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

$colaboradores_result = $conn->query("SELECT id, nome FROM colaboradores ORDER BY nome");

$colaborador_id = 0;
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $colaborador_id = isset($_POST['colaborador_id']) ? intval($_POST['colaborador_id']) : 0;
    $materiais_selecionados = isset($_POST['materiais']) ? $_POST['materiais'] : [];

    if ($colaborador_id > 0) {
        $stmt_del = $conn->prepare("DELETE FROM colaborador_materiais WHERE colaborador_id = ?");
        $stmt_del->bind_param("i", $colaborador_id);
        $stmt_del->execute();
        $stmt_del->close();

        if (count($materiais_selecionados) > 0) {
            $stmt_ins = $conn->prepare("INSERT INTO colaborador_materiais (colaborador_id, material_id) VALUES (?, ?)");
            foreach ($materiais_selecionados as $material_id) {
                $mid = intval($material_id);
                $stmt_ins->bind_param("ii", $colaborador_id, $mid);
                $stmt_ins->execute();
            }
            $stmt_ins->close();
        }
        $msg = "Materiais atualizados com sucesso.";
    } else {
        $msg = "Nenhum colaborador selecionado.";
    }
} else {
    if (isset($_GET['colaborador_id'])) {
        $colaborador_id = intval($_GET['colaborador_id']);
    }
}

if ($colaborador_id > 0) {
    $stmt_mat = $conn->prepare("SELECT material_id FROM colaborador_materiais WHERE colaborador_id = ?");
    $stmt_mat->bind_param("i", $colaborador_id);
    $stmt_mat->execute();
    $res_mat = $stmt_mat->get_result();
    $materiais_associados = [];
    while ($row = $res_mat->fetch_assoc()) {
        $materiais_associados[] = $row['material_id'];
    }
    $stmt_mat->close();

    $materiais_result = $conn->query("SELECT id, nome FROM materiais ORDER BY nome");
} else {
    $materiais_associados = [];
    $materiais_result = null;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<title>Associar Materiais a Colaborador</title>
<link rel="stylesheet" href="css/colaboradores_novo_usuario.css" />
<style>
  /* Ajustes específicos para melhorar a página */
  .select-colaborador select {
    font-size: 16px;
    padding: 12px;
    border-radius: 8px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
  }

  .select-colaborador select:focus {
    border-color: #28a745;
    box-shadow: 0 0 8px rgba(40,167,69,0.6);
  }

  .materiais-list {
    justify-content: center;
  }

  .materiais-list label {
    font-size: 15px;
  }

  button {
    font-size: 18px;
    font-weight: 700;
    padding: 14px 0;
    box-shadow: 0 4px 8px rgba(40,167,69,0.3);
  }

  button:hover {
    box-shadow: 0 6px 12px rgba(40,167,69,0.5);
  }

  .msg {
    font-size: 16px;
    background-color: #e6ffe6;
    color: #2a7a2a;
    padding: 10px 15px;
    border-radius: 6px;
    box-shadow: 0 2px 5px rgba(40,167,69,0.4);
    max-width: 450px;
    margin: 0 auto 20px auto;
  }

  .no-materials {
    font-size: 16px;
    color: #666;
  }
</style>
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

<h2>Associar Materiais a um Colaborador</h2>

<form method="GET" action="" class="select-colaborador">
    <label for="colaborador_id">Selecione o Colaborador:</label>
    <select id="colaborador_id" name="colaborador_id" onchange="this.form.submit()">
        <option value="0">-- Selecione --</option>
        <?php foreach ($colaboradores_result as $col): ?>
            <option value="<?php echo $col['id']; ?>" <?php if ($col['id'] == $colaborador_id) echo 'selected'; ?>>
                <?php echo htmlspecialchars($col['nome']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <noscript><button type="submit">Selecionar</button></noscript>
</form>

<?php if ($colaborador_id > 0): ?>

    <?php if ($msg): ?>
        <p class="msg"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>

    <?php if ($materiais_result && $materiais_result->num_rows > 0): ?>
        <form method="POST" action="">
            <input type="hidden" name="colaborador_id" value="<?php echo $colaborador_id; ?>" />
            <div class="materiais-list">
                <?php foreach ($materiais_result as $mat): ?>
                    <label>
                        <input 
                            type="checkbox" 
                            name="materiais[]" 
                            value="<?php echo $mat['id']; ?>"
                            <?php echo in_array($mat['id'], $materiais_associados) ? 'checked' : ''; ?>
                        >
                        <?php echo htmlspecialchars($mat['nome']); ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn">Salvar Associação</button>
        </form>
    <?php else: ?>
        <p class="no-materials">Não há materiais disponíveis para associar.</p>
    <?php endif; ?>

<?php endif; ?>


</div>

</body>
</html>
