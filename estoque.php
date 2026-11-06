<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['usuarios_id'])) { 
    header("Location: index.php"); 
    exit; 
}

$erro = ''; $sucesso = '';

// Exclusão de Produto
if (isset($_GET['deletar'])) {
    $idDeletar = (int)$_GET['deletar'];
    
    try {
        // Tenta deletar o produto
        $stmtDel = $pdo->prepare("DELETE FROM Produtos WHERE id = ?");
        $stmtDel->execute([$idDeletar]);
        $sucesso = "Produto excluído com sucesso!";
    } catch (PDOException $e) {
        // Se houver movimentações vinculadas (chave estrangeira), impede a exclusão para manter a integridade
        if ($e->getCode() == '23000') {
            $erro = "Não é possível excluir este produto pois ele já possui histórico de movimentações no sistema.";
        } else {
            $erro = "Erro ao excluir o produto: " . $e->getMessage();
        }
    }
}

// Registar Movimentação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movimentar'])) {
    $idProdutos = (int)$_POST['idProdutos'];
    $tipo = $_POST['tipo'];
    $qtd = (int)$_POST['qtd'];
    $motivo = $_POST['motivo'];
    $idusuarios = $_SESSION['usuarios_id'];

    $stmt = $pdo->prepare("SELECT qtd FROM Produtos WHERE id = ?");
    $stmt->execute([$idProdutos]);
    $produto = $stmt->fetch();

    if ($tipo === 'SAIDA' && $qtd > $produto['qtd']) {
        $erro = "Quantidade de saída maior que saldo.";
    } else {
        $pdo->beginTransaction();
        try {
            $stmtMov = $pdo->prepare("INSERT INTO Movimentacoes (tipo, qtd, motivo, idProdutos, idusuarios) VALUES (?, ?, ?, ?, ?)");
            $stmtMov->execute([$tipo, $qtd, $motivo, $idProdutos, $idusuarios]);

            $operador = ($tipo === 'ENTRADA') ? '+' : '-';
            $stmtUpd = $pdo->prepare("UPDATE Produtos SET qtd = qtd $operador ? WHERE id = ?");
            $stmtUpd->execute([$qtd, $idProdutos]);
            
            $pdo->commit();
            $sucesso = "Movimentação registrada com sucesso!";
        } catch (Exception $e) { 
            $pdo->rollBack(); 
            $erro = "Erro na movimentação: " . $e->getMessage(); 
        }
    }
}

// Consulta de Produtos (Ordem Alfabética)
$pesquisa = $_GET['busca'] ?? '';
$sql = "SELECT p.*, c.nome as categoria, f.nome as fornecedor 
        FROM Produtos p 
        JOIN Categorias c ON p.idCategorias = c.id 
        JOIN Fornecedores f ON p.idFornecedores = f.id 
        WHERE p.nome LIKE ?
        ORDER BY p.nome ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$pesquisa%"]);
$produtos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">
    <title>Estoque</title>
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
            <h2>Gestão de Estoque e Produtos</h2>
            <a href="cadastrar_produto.php" style="padding: 12px 20px; background-color: var(--primary-color, #4f46e5); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">+ Cadastrar Produto</a>
        </div>

        <?php if($erro) echo "<div class='alert'>$erro</div>"; ?>
        <?php if($sucesso) echo "<div style='color:green; padding:15px; margin-bottom: 20px; font-weight: bold;'>$sucesso</div>"; ?>

        <div>
            <h3>Lista de Produtos</h3>
            <form method="GET" style="margin-bottom: 20px;">
                <input type="text" name="busca" placeholder="Buscar..." value="<?= htmlspecialchars($pesquisa) ?>" style="width:80%; display:inline-block;">
                <button type="submit" style="width:18%; display:inline-block;">Buscar</button>
            </form>
            
            <table>
                <tr>
                    <th>ID</th>
                    <th>Produto</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Fornecedor</th>
                    <th>Preço</th>
                    <th>Saldo</th>
                    <th style="width: 180px;">Ações</th>
                </tr>
                <?php foreach($produtos as $prod): ?>
                <tr>
                    <td><?= $prod['id'] ?></td>
                    <td><strong><?= $prod['nome'] ?></strong></td>
                    <td><?= $prod['descricao'] ?></td>
                    <td><?= $prod['categoria'] ?></td>
                    <td><?= $prod['fornecedor'] ?></td>
                    <td>R$ <?= number_format($prod['preco'], 2, ',', '.') ?></td>
                    <td><strong><?= $prod['qtd'] ?></strong></td>
                    <td>
                        <a href="extrato.php?id=<?= $prod['id'] ?>" style="color:var(--primary-color, #4f46e5); text-decoration:none; font-weight:bold;">Extrato</a> | 
                        <a href="editar_produto.php?id=<?= $prod['id'] ?>" style="color:#d97706; text-decoration:none; font-weight:bold;">Editar</a> | 
                        <a href="estoque.php?deletar=<?= $prod['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir o produto <?= htmlspecialchars($prod['nome']) ?>?');" style="color:#dc2626; text-decoration:none; font-weight:bold;">Deletar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <br>

        <div style="background:#fff; padding:20px; border:1px solid #ddd; margin-bottom: 20px;">
            <h3>Registrar Movimentação</h3>
            <form method="POST" style="display:flex; gap:10px; flex-wrap:wrap;">
                <input type="hidden" name="movimentar" value="1">
                
                <select name="idProdutos" required>
                    <option value="">Produto...</option>
                    <?php foreach($produtos as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= $p['nome'] ?> (Saldo: <?= $p['qtd'] ?>)</option>
                    <?php endforeach; ?>
                </select>
                
                <select name="tipo" required>
                    <option value="ENTRADA">Entrada</option>
                    <option value="SAIDA">Saída</option>
                </select>
                
                <input type="number" name="qtd" placeholder="Qtd" min="1" required style="width:100px;">
                <input type="text" name="motivo" placeholder="Motivo" required>
                
                <button type="submit" style="width:150px;">Registrar</button>
            </form>
        </div>
    </div>
</body>
</html>