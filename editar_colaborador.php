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

// Buscar dados atuais do colaborador
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

// Buscar lista de todos os materiais disponíveis
$materiais_result = $conn->query("SELECT id, nome FROM materiais ORDER BY nome");

// Buscar materiais associados ao colaborador
$materiais_associados = [];
$stmt2 = $conn->prepare("SELECT material_id FROM colaborador_materiais WHERE colaborador_id = ?");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($row = $res2->fetch_assoc()) {
    $materiais_associados[] = $row['material_id'];
}
$stmt2->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $cargo = $_POST['cargo'];
    $materiais_selecionados = isset($_POST['materiais']) ? $_POST['materiais'] : [];

    // Atualizar dados do colaborador
    $stmt_update = $conn->prepare("UPDATE colaboradores SET nome = ?, cpf = ?, cargo = ? WHERE id = ?");
    $stmt_update->bind_param("sssi", $nome, $cpf, $cargo, $id);
    $stmt_update->execute();
    $stmt_update->close();

    // Atualizar materiais associados
    // Remove associações antigas
    $stmt_del = $conn->prepare("DELETE FROM colaborador_materiais WHERE colaborador_id = ?");
    $stmt_del->bind_param("i", $id);
    $stmt_del->execute();
    $stmt_del->close();

    // Inserir novas associações
    if (count($materiais_selecionados) > 0) {
        $stmt_ins = $conn->prepare("INSERT INTO colaborador_materiais (colaborador_id, material_id) VALUES (?, ?)");
        foreach ($materiais_selecionados as $material_id) {
            $mat_id = intval($material_id);
            $stmt_ins->bind_param("ii", $id, $mat_id);
            $stmt_ins->execute();
        }
        $stmt_ins->close();
    }

    $msg = "Colaborador atualizado com sucesso!";

    // Atualizar array para marcação
    $materiais_associados = $materiais_selecionados;

    // Atualizar dados exibidos
    $colaborador['nome'] = $nome;
    $colaborador['cpf'] = $cpf;
    $colaborador['cargo'] = $cargo;
}

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
    .materiais-list { margin-top: 15px; }
    .materiais-list label { font-weight: normal; }
    button { margin-top: 20px; width: 100%; padding: 10px; background-color: #007bff; border: none; color: white; font-size: 16px; cursor: pointer; border-radius: 4px; }
    button:hover { background-color: #0056b3; }
    .msg { margin-top: 15px; text-align: center; color: #28a745; }
    a { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #007bff; }
    a:hover { text-decoration: underline; }
</style>
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
            <?php while ($mat = $materiais_result->fetch_assoc()): ?>
                <label>
                    <input type="checkbox" name="materiais[]" value="<?php echo $mat['id']; ?>"
                        <?php if (in_array($mat['id'], $materiais_associados)) echo 'checked'; ?>>
                    <?php echo htmlspecialchars($mat['nome']); ?>
                </label><br>
            <?php endwhile; ?>
        </div>

        <button type="submit">Atualizar</button>
    </form>

    <a href="listar_colaboradores.php">Voltar à lista</a>
</div>

</body>
</html>
