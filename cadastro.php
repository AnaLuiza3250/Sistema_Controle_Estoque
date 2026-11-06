<?php
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
    if ($stmt->execute([$nome, $email, $senha])) {
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="css/style.css"><title>Cadastro</title></head>
<body>
    <div class="auth-box">
        <h2>Cadastrar Usuário</h2>
        <form method="POST">
            <input type="text" name="nome" placeholder="Nome completo" required>
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit">Cadastrar</button>
        </form>
        <p><a href="index.php">Voltar ao login</a></p>
    </div>
</body>
</html>