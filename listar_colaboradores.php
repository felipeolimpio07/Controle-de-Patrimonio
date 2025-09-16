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

$filtro = '';
if (isset($_GET['filtro'])) {
    $filtro = trim($_GET['filtro']);
}

if ($filtro != '') {
    // Consulta com filtro e agregação dos materiais
    $sql = "SELECT c.id, c.nome, c.cpf, c.cargo, 
        GROUP_CONCAT(m.nome SEPARATOR ', ') AS materiais
        FROM colaboradores c
        LEFT JOIN colaborador_materiais cm ON c.id = cm.colaborador_id
        LEFT JOIN materiais m ON cm.material_id = m.id
        WHERE c.nome LIKE CONCAT('%', ?, '%') OR c.cpf LIKE CONCAT('%', ?, '%')
        GROUP BY c.id, c.nome, c.cpf, c.cargo
        ORDER BY c.nome";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $filtro, $filtro);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Consulta sem filtro com agregação dos materiais
    $sql = "SELECT c.id, c.nome, c.cpf, c.cargo, 
        GROUP_CONCAT(m.nome SEPARATOR ', ') AS materiais
        FROM colaboradores c
        LEFT JOIN colaborador_materiais cm ON c.id = cm.colaborador_id
        LEFT JOIN materiais m ON cm.material_id = m.id
        GROUP BY c.id, c.nome, c.cpf, c.cargo
        ORDER BY c.nome";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Lista de Colaboradores</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; }
        form { margin-bottom: 15px; }
        input[type="text"] { padding: 7px; width: 250px; }
        button { padding: 7px 15px; }
        table { border-collapse: collapse; width: 100%; max-width: 900px; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #007bff; color: white; }
        a.btn, a { padding: 5px 10px; background-color: #007bff; color: white; border-radius: 6px; text-decoration: none; }
        a.btn:hover, a:hover { background-color: #0056b3; }
    </style>
</head>
<body>

<h1>Lista de Colaboradores</h1>

<form method="GET" action="">
    <label for="filtro">Filtrar por nome ou CPF:</label>
    <input type="text" id="filtro" name="filtro" value="<?php echo htmlspecialchars($filtro); ?>" />
    <button type="submit">Buscar</button>
    <a href="listar_colaboradores.php" class="btn">Limpar filtro</a>
</form>

<?php if ($result->num_rows > 0): ?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>CPF</th>
            <th>Cargo</th>
            <th>Materiais</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['nome']); ?></td>
            <td><?php echo htmlspecialchars($row['cpf']); ?></td>
            <td><?php echo htmlspecialchars($row['cargo']); ?></td>
            <td><?php echo htmlspecialchars($row['materiais'] ?? ''); ?></td>
            <td><a href="editar_colaborador.php?id=<?php echo $row['id']; ?>">Editar</a></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
    <p>Nenhum colaborador encontrado.</p>
<?php endif; ?>

<a href="dashboard.php" class="btn">Voltar ao Dashboard</a>

<?php
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>

</body>
</html>

