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

if (!isset($_GET['id'])) {
    header("Location: listar_colaboradores.php");
    exit();
}

$id = intval($_GET['id']);
$msg = '';

// Atualizar dados do colaborador e suas associações
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['deletar'])) {
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $cargo = $_POST['cargo'];
    $materiais_selecionados = isset($_POST['materiais']) ? $_POST['materiais'] : [];

    // Atualizar dados do colaborador
    $stmt_update = $conn->prepare("UPDATE colaboradores SET nome = ?, cpf = ?, cargo = ? WHERE id = ?");
    $stmt_update->bind_param("sssi", $nome, $cpf, $cargo, $id);
    if ($stmt_update->execute()) {
        $msg = "Colaborador atualizado com sucesso!";

        // Atualizar associações de materiais
        $stmt_del_mat = $conn->prepare("DELETE FROM colaborador_materiais WHERE colaborador_id = ?");
        $stmt_del_mat->bind_param("i", $id);
        $stmt_del_mat->execute();
        $stmt_del_mat->close();

        if (count($materiais_selecionados) > 0) {
            $stmt_ins_mat = $conn->prepare("INSERT INTO colaborador_materiais (colaborador_id, material_id) VALUES (?, ?)");
            foreach ($materiais_selecionados as $material_id) {
                $mid = intval($material_id);
                $stmt_ins_mat->bind_param("ii", $id, $mid);
                $stmt_ins_mat->execute();
            }
            $stmt_ins_mat->close();
        }
    } else {
        $msg = "Erro ao atualizar colaborador: " . $stmt_update->error;
    }
    $stmt_update->close();
}

// Processar exclusão do colaborador
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deletar'])) {
    $stmt_del = $conn->prepare("DELETE FROM colaboradores WHERE id = ?");
    $stmt_del->bind_param("i", $id);
    if ($stmt_del->execute()) {
        $stmt_del->close();
        $conn->close();
        header("Location: listar_colaboradores.php?msg=Colaborador+deletado+com+sucesso");
        exit();
    } else {
        $msg = "Erro ao deletar colaborador: " . $stmt_del->error;
        $stmt_del->close();
    }
}

// Buscar dados do colaborador
$stmt = $conn->prepare("SELECT nome, cpf, cargo FROM colaboradores WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: listar_colaboradores.php");
    exit();
}

$colaborador = $result->fetch_assoc();
$stmt->close();

// Buscar somente os materiais associados ao colaborador para mostrar na lista
$stmtMat = $conn->prepare("
    SELECT m.id, m.nome 
    FROM materiais m
    INNER JOIN colaborador_materiais cm ON m.id = cm.material_id
    WHERE cm.colaborador_id = ?
    ORDER BY m.nome
");
$stmtMat->bind_param("i", $id);
$stmtMat->execute();
$resMat = $stmtMat->get_result();

$materiais_associados = [];
while ($rowMat = $resMat->fetch_assoc()) {
    $materiais_associados[] = $rowMat;
}
$stmtMat->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<title>Editar Colaborador</title>
<style>
    body { font-family: Arial, sans-serif; }
    .form-container { width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 6px; box-shadow: 2px 2px 10px #aaa; }
    h2 { text-align: center; margin-bottom: 20px; }
    label { display: block; margin-top: 15px; font-weight: bold; }
    input[type="text"] { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
    .materiais-list { margin-top: 15px; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px; background: #fafafa; }
    .materiais-list label { font-weight: normal; display: block; cursor: pointer; margin-bottom: 5px; }
    button, .btn-delete {
        margin-top: 20px; width: 100%; padding: 10px; font-size: 16px; border-radius: 4px; cursor: pointer;
    }
    button {
        background-color: #007bff; border: none; color: white;
    }
    button:hover {
        background-color: #0056b3;
    }
    .btn-delete {
        background-color: #dc3545; border: none; color: white;
    }
    .btn-delete:hover {
        background-color: #a71d2a;
    }
    .msg { margin-top: 15px; text-align: center; color: #28a745; }
    a { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #007bff; }
    a:hover { text-decoration: underline; }
</style>
<script>
function confirmarExclusao() {
    return confirm('Tem certeza que deseja excluir este colaborador? Esta ação não pode ser desfeita.');
}
</script>
</head>
<body>

<div class="form-container">
    <h2>Editar Colaborador</h2>

    <?php if ($msg): ?>
        <p class="msg"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($colaborador['nome']); ?>">

        <label for="cpf">CPF:</label>
        <input type="text" id="cpf" name="cpf" maxlength="14" required value="<?php echo htmlspecialchars($colaborador['cpf']); ?>">

        <label for="cargo">Cargo:</label>
        <input type="text" id="cargo" name="cargo" required value="<?php echo htmlspecialchars($colaborador['cargo']); ?>">

        <div class="materiais-list">
            <label>Materiais Associados:</label>
            <?php if (count($materiais_associados) > 0): ?>
                <?php foreach ($materiais_associados as $material): ?>
                    <label>
                        <input type="checkbox" name="materiais[]" value="<?php echo $material['id']; ?>" checked>
                        <?php echo htmlspecialchars($material['nome']); ?>
                    </label>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum material associado.</p>
            <?php endif; ?>
        </div>

        <button type="submit">Atualizar</button>
    </form>

    <form method="POST" action="" onsubmit="return confirmarExclusao();">
        <input type="hidden" name="deletar" value="1" />
        <button type="submit" class="btn-delete">Excluir Colaborador</button>
    </form>

    <a href="listar_colaboradores.php">Voltar à lista</a>
</div>

</body>
</html>
