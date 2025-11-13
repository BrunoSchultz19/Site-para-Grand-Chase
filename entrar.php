<?php
session_start();
require_once 'banco.php';

if (isset($_SESSION['usuario_login'])) {
    header('Location: painel.php');
    exit;
}

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST['login']);
    $senha = $_POST['senha'];

    if (empty($login) || empty($senha)) {
        $mensagem = "⚠️ Login e senha são obrigatórios!";
    } else {
        try {
            // Buscar usuário por Login
            $stmt = $pdo->prepare("SELECT Login, passwd, nome, email FROM users WHERE Login = ?");
            $stmt->execute([$login]);
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                // ✅ VERIFICAÇÃO COM MD5
                $senha_md5 = md5($senha);
                
                if ($senha_md5 === $usuario['passwd']) {
                    $_SESSION['usuario_login'] = $usuario['Login'];
                    $_SESSION['usuario_nome'] = $usuario['nome'];
                    $_SESSION['usuario_email'] = $usuario['email'];
                    
                    header('Location: painel.php');
                    exit;
                } else {
                    $mensagem = "❌ Senha incorreta!";
                }
            } else {
                $mensagem = "❌ Login não encontrado!";
            }
        } catch (PDOException $e) {
            $mensagem = "❌ Erro no login: " . $e->getMessage();
        }
    }
}

include 'topo.php';
?>

<?php if ($mensagem): ?>
    <div class="message error"><?php echo $mensagem; ?></div>
<?php endif; ?>

<h2 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">🔐 Acessar Sistema</h2>

<form method="POST" action="">
    <div class="form-group">
        <label for="login">🔑 Login:</label>
        <input type="text" id="login" name="login" required 
               value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>">
    </div>
    
    <div class="form-group">
        <label for="senha">🔒 Senha:</label>
        <input type="password" id="senha" name="senha" required>
        <small style="color: #666; font-size: 12px;">Senha criptografada com MD5</small>
    </div>
    
    <button type="submit" class="btn">🚀 Entrar</button>
</form>

<div class="links">
    <a href="cadastrar.php">📋 Criar nova conta</a>
</div>

<?php include 'rodape.php'; ?>