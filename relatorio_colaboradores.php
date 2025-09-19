<?php
require('fpdf/fpdf.php');
include 'conexao.php';

// Recebe filtros
$nome = $_GET['nome'] ?? '';
$cpf = $_GET['cpf'] ?? '';

// Montar consulta SQL com filtros
$sql = "SELECT id, nome, cpf FROM colaboradores WHERE 1=1 ";
$params = [];
$types = '';

if ($nome !== '') {
    $sql .= "AND nome LIKE ? ";
    $params[] = "%$nome%";
    $types .= 's';
}

if ($cpf !== '') {
    $sql .= "AND cpf LIKE ? ";
    $params[] = "%$cpf%";
    $types .= 's';
}

$sql .= "ORDER BY nome";

$stmt = $conn->prepare($sql);

if ($params) {
    $bind_names = [];
    $bind_names[] = $types;
    for ($i=0; $i < count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

$stmt->execute();
$result = $stmt->get_result();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Colaboradores', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(20, 10, 'ID', 1);
$pdf->Cell(80, 10, 'Nome', 1);
$pdf->Cell(50, 10, 'CPF', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);

while ($row = $result->fetch_assoc()) {
    $pdf->Cell(20, 10, $row['id'], 1);
    $pdf->Cell(80, 10, utf8_decode($row['nome']), 1);
    $pdf->Cell(50, 10, $row['cpf'], 1);
    $pdf->Ln();
}

$pdf->Output();
?>
