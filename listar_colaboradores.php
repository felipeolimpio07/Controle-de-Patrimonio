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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="css/colaboradores_novo_usuario.css" />
    <style>
        /* Estilos complementares para esta página */

        /* Área principal com fundo cinza claro */
       .main-content {
    max-width: 1000px;
    margin: 0 auto 50px auto; /* 40px de espaço extra abaixo da caixa */
    padding: 100px 30px 100px;  /* 60px de padding inferior (ou mais se quiser) */
    background-color: #C7BFBF; /* ou #f0f0f0, etc */
    min-height: calc(90vh - 40px);
    border-radius: 8px;
    box-sizing: border-box;
    box-shadow: 2spx 2px 10px rgba(0,0,0,0.2);
    color: black;
}


        /* Filtro */
        form.filter-form {
            margin-bottom: 20px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        form.filter-form label {
            font-weight: bold;
            color: black;
        }

        form.filter-form input[type="text"] {
            padding: 8px;
            font-size: 14px;
            border-radius: 6px;
            border: 1px solid #ccc;
            flex-grow: 1;
            min-width: 200px;
        }

        form.filter-form button,
        form.filter-form a.btn {
            padding: 10px 20px;
            font-weight: bold;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        }

        /* Tabela */
        .table-container {
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
            overflow-x: auto;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            min-width: 700px;
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

        /* Botões editar */
        a.btn {
            background-color: #668245;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            padding: 6px 12px;
            display: inline-block;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        a.btn:hover {
            background-color: #506831;
        }

        /* Mensagens */
        .msg {
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            padding: 12px;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            color: #28a745;
            background-color: #e6ffe6;
            box-shadow: 0 2px 5px rgba(40,167,69,0.4);
        }

        /* Link voltar */
        .button-back {
            display: inline-block;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            color: #007bff;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            padding: 10px 15px;
            border-radius: 6px;
            background-color: #f0f0f0;
            transition: background-color 0.3s ease;
        }

        .button-back:hover {
            background-color: #d6d6d6;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            form.filter-form {
                flex-direction: column;
                gap: 8px;
            }

            table {
                min-width: 100%;
            }
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

    <h1>Lista de Colaboradores</h1>

    <form method="GET" action="" class="filter-form" novalidate>
        <label for="filtro">Filtrar por nome ou CPF:</label>
        <input type="text" id="filtro" name="filtro" value="<?php echo htmlspecialchars($filtro); ?>" placeholder="Digite nome ou CPF" />
        <button type="submit">Buscar</button>
        <a href="listar_colaboradores.php" class="btn">Limpar filtro</a>
         <a href="filtros_relatorios.php" class="btn">Gerar Relatórios</a>
    </form>

    <?php if (isset($msg) && $msg !== ''): ?>
        <div class="msg"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <div class="table-container">
        <?php if ($result && $result->num_rows > 0): ?>
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
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['nome']); ?></td>
                            <td><?php echo htmlspecialchars($row['cpf']); ?></td>
                            <td><?php echo htmlspecialchars($row['cargo']); ?></td>
                            <td><?php echo htmlspecialchars($row['materiais'] ?? ''); ?></td>
                            <td><a href="editar_colaborador.php?id=<?php echo $row['id']; ?>" class="btn">Editar</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum colaborador encontrado.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>

<?php
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>
