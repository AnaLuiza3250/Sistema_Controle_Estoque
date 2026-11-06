<?php
session_start();
require 'config/db.php';
if (!isset($_SESSION['usuarios_id'])) { header("Location: index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'])) {
    $stmt = $pdo->prepare("INSERT INTO fornecedores (nome, cnpj) VALUES (?, ?)");
    $stmt->execute([$_POST['nome'], $_POST['cnpj']]);
}
if (isset($_GET['del'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM fornecedores WHERE id = ?");
        $stmt->execute([$_GET['del']]);
    } catch (Exception $e) { $erro = "Erro: Fornecedor em uso."; }
}

$fornecedores = $pdo->query("SELECT * FROM fornecedores")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="css/style.css"><title>Fornecedores</title></head>
<body>
    <div class="nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="categorias.php">Categorias</a>
        <a href="fornecedores.php">Fornecedores</a>
        <a href="estoque.php">Estoque & Produtos</a>
        <a href="logout.php" class="logout">Sair</a>
    </div>
    <div class="container">
        <h2>Fornecedores</h2>
        <?php if(isset($erro)) echo "<div class='alert'>$erro</div>"; ?>
        <form method="POST" style="display:flex; gap:10px;">
            <input type="text" name="nome" placeholder="Nome" required>
            <input type="text" name="cnpj" placeholder="CNPJ" required>
            <button type="submit" style="width:150px;">Adicionar</button>
        </form>
        <table>
            <tr><th>ID</th><th>Nome</th><th>CNPJ</th><th>Ação</th></tr>
            <?php foreach($fornecedores as $f): ?>
            <tr><td><?= $f['id'] ?></td><td><?= $f['nome'] ?></td><td><?= $f['cnpj'] ?></td><td><a href="?del=<?= $f['id'] ?>" style="color:red;">Excluir</a></td></tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>