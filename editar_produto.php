<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['usuarios_id'])) { 
    header("Location: index.php"); 
    exit; 
}

$idProduto = $_GET['id'] ?? null;

if (!$idProduto) {
    header("Location: estoque.php");
    exit;
}

$erro = ''; $sucesso = '';

// Atualização de dados do Produto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_produto'])) {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = (float)$_POST['preco'];
    $min = (int)$_POST['estoque_minimo'];
    $cat = (int)$_POST['idCategorias'];
    $for = (int)$_POST['idFornecedores'];
    
    $stmtUpd = $pdo->prepare("UPDATE Produtos SET nome = ?, descricao = ?, preco = ?, estoque_minimo = ?, idCategorias = ?, idFornecedores = ? WHERE id = ?");
    if ($stmtUpd->execute([$nome, $descricao, $preco, $min, $cat, $for, $idProduto])) {
        $sucesso = "Produto atualizado com sucesso!";
        header('Location: estoque.php');
    } else {
        $erro = "Erro ao atualizar produto.";
    }
}

// Busca os dados do produto para preencher o formulário
$stmtProd = $pdo->prepare("SELECT * FROM Produtos WHERE id = ?");
$stmtProd->execute([$idProduto]);
$produto = $stmtProd->fetch();

if (!$produto) {
    echo "Produto não encontrado.";
    exit;
}

// Busca categorias e fornecedores para montar os Selects
$categorias = $pdo->query("SELECT * FROM Categorias")->fetchAll();
$fornecedores = $pdo->query("SELECT * FROM Fornecedores")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">
    <title>Editar Produto</title>
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
            <h2>Editar Produto #<?= $produto['id'] ?></h2>
            <a href="estoque.php" style="padding: 10px 15px; background-color: #6b7280; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">&larr; Voltar para Estoque</a>
        </div>

        <?php if($erro) echo "<div class='alert'>$erro</div>"; ?>
        <?php if($sucesso) echo "<div style='color:green; padding:15px; margin-bottom: 20px; font-weight: bold;'>$sucesso</div>"; ?>

        <div style="background:#fff; padding:30px; border:1px solid #ddd; border-radius: 12px; margin-bottom: 20px;">
            <form method="POST" style="display:flex; flex-direction:column; gap:15px; max-width: 500px;">
                <input type="hidden" name="atualizar_produto" value="1">
                
                <div>
                    <label style="font-weight: 600; margin-bottom: 5px; display: block;">Nome do Produto</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($produto['nome']) ?>" required>
                </div>

                <div>
                    <label style="font-weight: 600; margin-bottom: 5px; display: block;">Descrição</label>
                    <input type="text" name="descricao" value="<?= htmlspecialchars($produto['descricao']) ?>">
                </div>
                
                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label style="font-weight: 600; margin-bottom: 5px; display: block;">Preço (R$)</label>
                        <input type="number" step="0.01" name="preco" value="<?= $produto['preco'] ?>" required>
                    </div>
                    <div style="flex: 1;">
                        <label style="font-weight: 600; margin-bottom: 5px; display: block;">Estoque Mínimo</label>
                        <input type="number" name="estoque_minimo" value="<?= $produto['estoque_minimo'] ?>" required>
                    </div>
                </div>
                
                <div>
                    <label style="font-weight: 600; margin-bottom: 5px; display: block;">Categoria</label>
                    <select name="idCategorias" required>
                        <?php foreach($categorias as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($c['id'] == $produto['idCategorias']) ? 'selected' : '' ?>>
                                <?= $c['nome'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label style="font-weight: 600; margin-bottom: 5px; display: block;">Fornecedor</label>
                    <select name="idFornecedores" required>
                        <?php foreach($fornecedores as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= ($f['id'] == $produto['idFornecedores']) ? 'selected' : '' ?>>
                                <?= $f['nome'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" style="margin-top: 15px; padding: 14px;" >Atualizar Produto</button>
            </form>
        </div>
    </div>
</body>
</html>