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

// Deletar material quando passar delete_id via GET
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Primeiro, verificar se o material está associado a algum colaborador para evitar inconsistência
    $stmt_check = $conn->prepare("SELECT COUNT(*) as count_assoc FROM colaborador_materiais WHERE material_id = ?");
    $stmt_check->bind_param("i", $delete_id);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    $row_check = $res_check->fetch_assoc();
    $stmt_check->close();

    if ($row_check['count_assoc'] > 0) {
        $msg = "Material não pode ser excluído porque está associado a colaboradores.";
    } else {
        $stmt_del = $conn->prepare("DELETE FROM materiais WHERE id = ?");
        $stmt_del->bind_param("i", $delete_id);
        if ($stmt_del->execute()) {
            $msg = "Material excluído com sucesso.";
        } else {
            $msg = "Erro ao excluir material: " . $stmt_del->error;
        }
        $stmt_del->close();
    }
}

// Buscar lista de materiais
$sql = "SELECT id, nome FROM materiais ORDER BY nome";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<title>Lista de Materiais</title>
<style>
    body { font-family: Arial, sans-serif; padding: 30px; }
    table { border-collapse: collapse; width: 100%; max-width: 600px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background-color: #007bff; color: white; }
    a.btn {
        display: inline-block;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        padding: 7px 12px;
        border-radius: 4px;
        font-size: 14px;
        margin-right: 5px;
    }
    a.btn:hover {
        background-color: #0056b3;
    }
    .top-buttons {
        margin-bottom: 20px;
    }
    .msg {
        margin-bottom: 15px;
        font-weight: bold;
        color: #28a745;
    }
    .msg.error {
        color: #d63333;
    }
</style>
<script>
    function confirmDelete(materialName) {
        return confirm('Tem certeza que deseja excluir o material "' + materialName + '"?');
    }
</script>
</head>
<body>

<h1>Lista de Materiais</h1>

<div class="top-buttons">
    <a href="materiais.php" class="btn">Cadastrar Novo Material</a>
    <a href="dashboard.php" class="btn">Voltar ao Dashboard</a>
</div>

<?php if ($msg != ''): ?>
    <p class="msg <?php echo strpos($msg, 'erro') !== false ? 'error' : ''; ?>">
        <?php echo htmlspecialchars($msg); ?>
    </p>
<?php endif; ?>

<?php if ($result->num_rows > 0): ?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome do Material</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td><?php echo htmlspecialchars($row['nome']); ?></td>
            <td>
                <a href="editar_material.php?id=<?php echo $row['id']; ?>" class="btn">Editar</a>
                <a href="?delete_id=<?php echo $row['id']; ?>" class="btn" onclick="return confirmDelete('<?php echo htmlspecialchars(addslashes($row['nome'])); ?>')">Excluir</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
<p>Nenhum material cadastrado.</p>
<?php endif; ?>

</body>
</html>

<?php
$conn->close();
?>
