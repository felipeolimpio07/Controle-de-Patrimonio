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
    }
    a.btn:hover {
        background-color: #0056b3;
    }
    .top-buttons {
        margin-bottom: 20px;
    }
</style>
</head>
<body>

<h1>Lista de Materiais</h1>

<div class="top-buttons">
    <a href="materiais.php" class="btn">Cadastrar Novo Material</a>
    <a href="dashboard.php" class="btn">Voltar ao Dashboard</a>
</div>

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
