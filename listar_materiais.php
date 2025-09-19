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

    // Verificar se o material está associado a algum colaborador para evitar inconsistência
    $stmt_check = $conn->prepare("SELECT COUNT(*) as count_assoc FROM colaborador_materiais WHERE material_id = ?");
    $stmt_check->bind_param("i", $delete_id);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    $row_check = $res_check->fetch_assoc();
    $stmt_check->close();

    if ($row_check['count_assoc'] > 0) {
        $msg = "Material não pode ser excluído porque está associado a colaboradores.";
        $msgClass = "msg error";
    } else {
        $stmt_del = $conn->prepare("DELETE FROM materiais WHERE id = ?");
        $stmt_del->bind_param("i", $delete_id);
        if ($stmt_del->execute()) {
            $msg = "Material excluído com sucesso.";
            $msgClass = "msg success";
        } else {
            $msg = "Erro ao excluir material: " . htmlspecialchars($stmt_del->error);
            $msgClass = "msg error";
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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="css/colaboradores_novo_usuario.css" />
    <style>
        /* Ajustes específicos para a tabela e botões */

        body {
            padding-left: 250px; /* Para espaço da sidebar fixa */
        }

        .main-content {
            padding: 90px 30px 30px;
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 2px 2px 12px rgba(0,0,0,0.2);
            box-sizing: border-box;
            color: black;
            min-height: calc(100vh - 60px);
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #668245;
            color: white;
        }

        a.btn {
            background-color: #668245;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            padding: 6px 12px;
            font-weight: bold;
            margin-right: 5px;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        a.btn:hover {
            background-color: #506831;
        }

        .top-buttons {
            margin-bottom: 20px;
        }

        .msg {
            margin-bottom: 15px;
            font-weight: bold;
            color: #28a745;
            background-color: #e6ffe6;
            padding: 10px 15px;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(40,167,69,0.4);
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .msg.error {
            color: #d63333;
            background-color: #ffe6e6;
            box-shadow: 0 2px 5px rgba(214,51,51,0.4);
        }

        /* Responsividade */
        @media (max-width: 800px) {
            body {
                padding-left: 0;
            }
            .main-content {
                padding: 20px 10px;
                max-width: 100%;
            }
        }
    </style>
    <script>
        function confirmDelete(materialName) {
            return confirm('Tem certeza que deseja excluir o material "' + materialName + '"?');
        }
    </script>
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

    <h1>Lista de Materiais</h1>

    <div class="top-buttons">
        <a href="materiais.php" class="btn">Cadastrar Novo Material</a>
        <a href="relatorio_colaboradores_materias.php" class="btn">Gerar Relatório</a>

    </div>

    <?php if ($msg != ''): ?>
        <p class="<?php echo $msgClass ?? 'msg'; ?>">
            <?php echo htmlspecialchars($msg); ?>
        </p>
    <?php endif; ?>

    <?php if ($result && $result->num_rows > 0): ?>
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
                        <a href="?delete_id=<?php echo $row['id']; ?>" class="btn" 
                           onclick="return confirmDelete('<?php echo htmlspecialchars(addslashes($row['nome'])); ?>')">Excluir</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhum material cadastrado.</p>
    <?php endif; ?>

</div>

</body>
</html>

<?php
$conn->close();
?>
