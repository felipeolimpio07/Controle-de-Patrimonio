<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $cargo = $_POST['cargo'];

    // Limpar formatação do CPF para validar
    $cpf_limpo = preg_replace('/[^0-9]/', '', $cpf);

    if (!validarCPF($cpf_limpo)) {
        $msg = "CPF inválido. Por favor, insira um CPF válido.";
    } else {
        $sql = "INSERT INTO colaboradores (nome, cpf, cargo) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Erro na preparação da consulta: " . $conn->error);
        }
        $stmt->bind_param("sss", $nome, $cpf, $cargo);

        if ($stmt->execute()) {
            $msg = "Colaborador cadastrado com sucesso!";
        } else {
            $msg = "Erro ao cadastrar colaborador: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Cadastrar Colaboradores</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .form-container { width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 6px; box-shadow: 2px 2px 10px #aaa; }
        h2 { text-align: center; margin-bottom: 20px; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input[type="text"] { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        button { margin-top: 20px; width: 100%; padding: 10px; background-color: #28a745; border: none; color: white; font-size: 16px; cursor: pointer; border-radius: 4px; }
        button:hover { background-color: #218838; }
        .msg { margin-top: 20px; text-align: center; color: #d63333; }
        .msg.success { color: #28a745; }
        a { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #007bff; }
        a:hover { text-decoration: underline; }
    </style>
    <script>
        function mascaraCPF(input) {
            let cpf = input.value.replace(/\D/g, '');
            cpf = cpf.substring(0, 11);

            cpf = cpf.replace(/^(\d{3})(\d)/, "$1.$2");
            cpf = cpf.replace(/^(\d{3})\.(\d{3})(\d)/, "$1.$2.$3");
            cpf = cpf.replace(/\.(\d{3})(\d)/, ".$1-$2");

            input.value = cpf;
        }

        function validarCPF(cpf) {
            cpf = cpf.replace(/[^\d]+/g, '');
            if (cpf == '') return false;
            if (cpf.length != 11 ||
                cpf == "00000000000" || cpf == "11111111111" ||
                cpf == "22222222222" || cpf == "33333333333" ||
                cpf == "44444444444" || cpf == "55555555555" ||
                cpf == "66666666666" || cpf == "77777777777" ||
                cpf == "88888888888" || cpf == "99999999999") 
                return false;
            let soma = 0;
            let resto;
            for (let i = 1; i <= 9; i++) {
                soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
            }
            resto = (soma * 10) % 11;
            if (resto == 10 || resto == 11)
                resto = 0;
            if (resto != parseInt(cpf.substring(9, 10)))
                return false;
            soma = 0;
            for (let i = 1; i <= 10; i++) {
                soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
            }
            resto = (soma *10) % 11;
            if (resto == 10 || resto == 11)
                resto = 0;
            if (resto != parseInt(cpf.substring(10, 11)))
                return false;
            return true;
        }

        function validarFormulario() {
            const cpfInput = document.getElementById('cpf');
            const cpf = cpfInput.value;
            if (!validarCPF(cpf)) {
                alert("CPF inválido. Por favor, digite um CPF válido.");
                cpfInput.focus();
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<div class="form-container">
    <h2>Cadastro de Colaboradores</h2>

    <form method="POST" action="" onsubmit="return validarFormulario();">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="cpf">CPF:</label>
        <input type="text" id="cpf" name="cpf" maxlength="14" required oninput="mascaraCPF(this)">

        <label for="cargo">Cargo:</label>
        <input type="text" id="cargo" name="cargo" required>

        <button type="submit">Cadastrar</button>
    </form>

    <?php if ($msg != ''): ?>
        <p class="msg <?php echo strpos($msg, 'sucesso') !== false ? 'success' : '' ?>">
            <?php echo htmlspecialchars($msg); ?>
        </p>
    <?php endif; ?>

    <a href="dashboard.php">Voltar ao Dashboard</a>
</div>

</body>
</html>
