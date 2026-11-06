<?php
session_start();
require 'config/db.php';

// Corrigido para usuarios_id para não deslogar
if (!isset($_SESSION['usuarios_id'])) { 
    header("Location: index.php"); 
    exit; 
}

// Busca produtos com estoque crítico
$stmt = $pdo->query("SELECT nome, qtd, estoque_minimo FROM Produtos WHERE qtd <= estoque_minimo");
$alertas = $stmt->fetchAll();

// Busca as últimas 5 movimentações gerais
$sqlMov = "SELECT m.*, p.nome as produto 
           FROM Movimentacoes m 
           JOIN Produtos p ON m.idProdutos = p.id 
           ORDER BY m.dta_hora DESC LIMIT 5";
$ultimasMov = $pdo->query($sqlMov)->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">
    <title>Dashboard</title>
</head>
<body>
    <div class="nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="categorias.php">Categorias</a>
        <a href="fornecedores.php">Fornecedores</a>
        <a href="estoque.php">Estoque & Produtos</a>
        <!-- Corrigido para usuarios_nome -->
        <a href="dashboard.php" class="identidade">Olá <?php echo $_SESSION['usuarios_nome']; ?></a>
        <a href="logout.php" class="logout">Sair</a>
    </div>

    <div style="display: flex; justify-content: flex-end; margin-top: 10px; width: 100%; margin-right: 10px;">
        <a href="cadastrar_produto.php" style="padding: 12px 20px; background-color: var(--primary-color, #4f46e5); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">+ Cadastrar Produto</a>
    </div>

    
    <div class="container">
        <h2>Bem-vindo ao Sistema de Gerenciamento</h2><br>
        
        <?php if (count($alertas) > 0): ?>
            <div class="alert">
                <strong>Atenção! Produtos com estoque crítico:</strong>
                <ul>
                    <?php foreach ($alertas as $alerta): ?>
                        <li><?= $alerta['nome'] ?> - Saldo: <?= $alerta['qtd'] ?> (Mínimo: <?= $alerta['estoque_minimo'] ?>)</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <p>Seu estoque está em níveis regulares.</p>
        <?php endif; ?>

        <!-- Seção de Últimas Movimentações -->
        <div style="background:#fff; padding:20px; border:1px solid #ddd; margin-top: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3>Últimas Movimentações</h3>
                <a href="extrato_geral.php" style="padding: 10px 15px; background-color: var(--primary-color, #4f46e5); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">Ver Extrato Completo</a>
            </div>
            
            <?php if (count($ultimasMov) > 0): ?>
                <table>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Produto</th>
                        <th>Tipo</th>
                        <th>Qtd</th>
                        <th>Motivo</th>
                    </tr>
                    <?php foreach($ultimasMov as $mov): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($mov['dta_hora'])) ?></td>
                        <td><?= $mov['produto'] ?></td>
                        <td style="color: <?= $mov['tipo'] == 'ENTRADA' ? 'green' : 'red' ?>; font-weight: bold;">
                            <?= $mov['tipo'] ?>
                        </td>
                        <td><?= $mov['qtd'] ?></td>
                        <td><?= $mov['motivo'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>Nenhuma movimentação registrada ainda.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>