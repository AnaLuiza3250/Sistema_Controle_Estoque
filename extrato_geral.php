<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['usuarios_id'])) { 
    header("Location: index.php"); 
    exit; 
}

// Busca todas as movimentações
$sql = "SELECT m.*, p.nome as produto, u.nome as usuario 
        FROM Movimentacoes m 
        JOIN Produtos p ON m.idProdutos = p.id 
        JOIN usuarios u ON m.idusuarios = u.id
        ORDER BY m.dta_hora DESC";
$stmt = $pdo->query($sql);
$movimentacoes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">
    <title>Extrato Geral - Estoque</title>
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
            <h2>Extrato Geral de Movimentações</h2>
            <a href="dashboard.php" style="padding: 10px 15px; background-color: #6b7280; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">&larr; Voltar ao Dashboard</a>
        </div>

        <?php if (count($movimentacoes) > 0): ?>
            <table>
                <tr>
                    <th>Data/Hora</th>
                    <th>Produto</th>
                    <th>Tipo</th>
                    <th>Qtd</th>
                    <th>Motivo</th>
                    <th>Usuário</th>
                </tr>
                <?php foreach($movimentacoes as $mov): ?>
                <tr>
                    <td><?= date('d/m/Y H:i:s', strtotime($mov['dta_hora'])) ?></td>
                    <td><strong><?= $mov['produto'] ?></strong></td>
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
            <p>Nenhuma movimentação registrada no sistema.</p>
        <?php endif; ?>
    </div>
</body>
</html>