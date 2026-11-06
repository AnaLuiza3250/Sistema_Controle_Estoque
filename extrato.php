<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['usuarios_id'])) { 
    header("Location: index.php"); 
    exit; 
}

$idProdutos = $_GET['id'] ?? null;

if (!$idProduto) {
    header("Location: estoque.php");
    exit;
}

// Busca os dados do produto
$stmtProd = $pdo->prepare("SELECT nome, qtd FROM Produtos WHERE id = ?");
$stmtProd->execute([$idProdutos]);
$produtos = $stmtProd->fetch();

if (!$produto) {
    echo "Produto não encontrado.";
    exit;
}

// Busca as movimentações apenas deste produto
$sql = "SELECT m.*, u.nome as usuarios 
        FROM Movimentacoes m 
        JOIN usuarios u ON m.idusuarios = u.id
        WHERE m.idProdutos = ? 
        ORDER BY m.dta_hora DESC";
$stmtMov = $pdo->prepare($sql);
$stmtMov->execute([$idProdutos]);
$movimentacoes = $stmtMov->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">
    <title>Extrato do Produto</title>
</head>
<body>
    <div class="nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="categorias.php">Categorias</a>
        <a href="fornecedores.php">Fornecedores</a>
        <a href="estoque.php">Estoque & Produtos</a>
        <a href="logout.php" class="logout">Sair</a>
    </div>
    
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h2>Extrato de Produto: <?= $produtos['nome'] ?></h2>
                <p style="font-size: 18px; margin-top: 5px;">Saldo Atual: <strong><?= $produto['qtd'] ?> unidades</strong></p>
            </div>
            <a href="estoque.php" style="padding: 10px 15px; background-color: #6b7280; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">&larr; Voltar para Estoque</a>
        </div>

        <?php if (count($movimentacoes) > 0): ?>
            <table>
                <tr>
                    <th>Data/Hora</th>
                    <th>Tipo</th>
                    <th>Qtd</th>
                    <th>Motivo</th>
                    <th>Responsável</th>
                </tr>
                <?php foreach($movimentacoes as $mov): ?>
                <tr>
                    <td><?= date('d/m/Y H:i:s', strtotime($mov['dta_hora'])) ?></td>
                    <td style="color: <?= $mov['tipo'] == 'ENTRADA' ? 'green' : 'red' ?>; font-weight: bold;">
                        <?= $mov['tipo'] ?>
                    </td>
                    <td><?= $mov['qtd'] ?></td>
                    <td><?= $mov['motivo'] ?></td>
                    <td><?= $mov['usuario'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <div style="background: #f9fafb; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
                <p>Nenhuma movimentação registrada para este produto.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>