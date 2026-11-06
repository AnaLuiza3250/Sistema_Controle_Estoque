<?php
session_start();
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuarios = $stmt->fetch();

    if ($usuarios && password_verify($senha, $usuarios['senha'])) {
        $_SESSION['usuarios_id'] = $usuarios['id'];
        $_SESSION['usuarios_nome'] = $usuarios['nome'];
        header("Location: dashboard.php");
        exit;
    } else {
        $erro = "E-mail ou senha inválidos.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="css/style.css"><title>Login - Estoque</title></head>
<body>
    <div class="auth-box">
        <h2>Acesso ao Sistema</h2>
        <?php if(isset($erro)) echo "<p style='color:red;'>$erro</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>
        <p><a href="cadastro.php">Registrar novo usuário</a></p>
    </div>
</body>
</html>