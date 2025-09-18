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
                $material_id = intval($material_id);
                $stmt_ins->bind_param("ii", $colaborador_id, $material_id);
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

$materiais_result = $conn->query("
    SELECT id, nome FROM materiais
    WHERE id NOT IN (SELECT material_id FROM colaborador_materiais)
    ORDER BY nome
");

$materiais_associados = [];
if ($colaborador_id > 0) {
    $stmt_mat = $conn->prepare("SELECT material_id FROM colaborador_materiais WHERE colaborador_id = ?");
    $stmt_mat->bind_param("i", $colaborador_id);
    $stmt_mat->execute();
    $res_mat = $stmt_mat->get_result();
    while ($row = $res_mat->fetch_assoc()) {
        $materiais_associados[] = $row['material_id'];
    }
    $stmt_mat->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<title>Associar Materiais a Colaborador</title>
<style>
    body { font-family: Arial, sans-serif; padding: 30px; background-color: #f7f9fc; }
    h2 { margin-bottom: 20px; color: #333; }
    label { display: block; margin-top: 10px; font-weight: bold; }
    select, button { margin-top: 10px; padding: 8px 12px; font-size: 16px; border-radius: 6px; border: 1px solid #ccc; }
    select:hover, button:hover { border-color: #007bff; }
    button { background-color: #007bff; color: white; border: none; cursor: pointer; }
    button:hover { background-color: #0056b3; }
    .materiais-list { 
        margin-top: 20px; 
        display: flex; 
        flex-wrap: wrap; 
        gap: 15px; 
    }
    .materiais-list label { 
        font-weight: normal; 
        background: white; 
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
        border-radius: 8px; 
        padding: 10px 15px; 
        display: flex; 
        align-items: center; 
        cursor: pointer;
        user-select: none;
        transition: box-shadow 0.3s ease;
        width: 200px;
    }
    .materiais-list label:hover {
        box-shadow: 0 4px 15px rgba(0,123,255,0.3);
    }
    .materiais-list input[type="checkbox"] {
        margin-right: 10px;
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    .msg { 
        margin: 15px 0; 
        color: #28a745; 
        font-weight: bold; 
    }
    .no-materials { 
        margin-top: 20px; 
        font-style: italic; 
        color: #555; 
        font-size: 1.1em; 
    }
</style>
</head>
<body>

<h2>Associar Materiais a um Colaborador</h2>

<form method="GET" action="" style="display: inline-block;">
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

    <?php if ($materiais_result->num_rows > 0): ?>
        <form method="POST" action="">
            <input type="hidden" name="colaborador_id" value="<?php echo $colaborador_id; ?>" />
            <div class="materiais-list">
                <label>Materiais disponíveis:</label>
                <?php foreach ($materiais_result as $mat): ?>
                    <label>
                        <input type="checkbox" name="materiais[]" value="<?php echo $mat['id']; ?>"
                            <?php if (in_array($mat['id'], $materiais_associados)) echo 'checked'; ?>>
                        <?php echo htmlspecialchars($mat['nome']); ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <button type="submit">Salvar Associação</button>
        </form>
    <?php else: ?>
        <p class="no-materials">Não há materiais disponíveis para associar.</p>
    <?php endif; ?>
<?php endif; ?>

<a href="dashboard.php" style="display: block; margin-top: 20px; text-decoration: none; color: #007bff;">Voltar ao Dashboard</a>

</body>
</html>
