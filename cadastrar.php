<?php
session_start();
require_once 'banco.php';

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $login = trim($_POST['login']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Validações com limites corretos
    if (empty($nome) || empty($login) || empty($email) || empty($senha)) {
        $mensagem = "⚠️ Todos os campos são obrigatórios!";
    } elseif ($senha !== $confirmar_senha) {
        $mensagem = "⚠️ As senhas não coincidem!";
    } elseif (strlen($senha) < 6) {
        $mensagem = "⚠️ A senha deve ter pelo menos 6 caracteres!";
    } elseif (strlen($login) > 40) {
        $mensagem = "⚠️ O Login deve ter no máximo 40 caracteres!";
    } elseif (strlen($email) > 150) {
        $mensagem = "⚠️ O Email deve ter no máximo 150 caracteres!";
    } elseif (strlen($nome) > 100) {
        $mensagem = "⚠️ O Nome deve ter no máximo 100 caracteres!";
    } else {
        try {
            // Verificar se Login já existe
            $stmt_login = $pdo->prepare("SELECT Login FROM users WHERE Login = ?");
            $stmt_login->execute([$login]);
            $login_existe = $stmt_login->fetch();

            if ($login_existe) {
                $mensagem = "❌ Este Login já está em uso! Escolha outro.";
            } else {
                // ✅ CRIAR HASH MD5 DA SENHA
                $senha_md5 = md5($senha);
                
                // SQL COM MD5
                $sql = "INSERT INTO users (
                    Login, 
                    passwd, 
                    sex, 
                    firstLogin, 
                    lastConnect, 
                    lastLogin, 
                    playTime, 
                    gamePoint, 
                    IPAddress, 
                    Connecting, 
                    ModeLevel, 
                    ChannelingID, 
                    Grade, 
                    email, 
                    nome
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CONVERT(VARBINARY(200), ?), ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    substr($login, 0, 40),           // Login (nvarchar 40)
                    $senha_md5,                      // passwd (nvarchar 40) - MD5 (32 caracteres)
                    '0',                             // sex (char 1)
                    date('Y-m-d H:i:s'),             // firstLogin
                    date('Y-m-d H:i:s'),             // lastConnect
                    date('Y-m-d H:i:s'),             // lastLogin
                    0,                               // playTime
                    2000,                            // gamePoint
                    substr('127.0.0.1', 0, 15),      // IPAddress (nvarchar 15)
                    0,                               // Connecting
                    '',                              // ModeLevel
                    0,                               // ChannelingID
                    0,                               // Grade
                    substr($email, 0, 150),          // email (nvarchar 150)
                    substr($nome, 0, 100)            // nome (varchar)
                ]);
                
                $mensagem = "🎉 Cadastro realizado com sucesso! <a href='entrar.php'>Faça login aqui</a>";
                
                // Limpar campos após sucesso
                $_POST = array();
            }
        } catch (PDOException $e) {
            $mensagem = "❌ Erro no cadastro: " . $e->getMessage();
        }
    }
}

include 'topo.php';
?>

<?php if ($mensagem): ?>
    <div class="message <?php 
        if (strpos($mensagem, 'sucesso') !== false) echo 'success';
        elseif (strpos($mensagem, '⚠️') !== false) echo 'warning';
        else echo 'error';
    ?>">
        <?php echo $mensagem; ?>
    </div>
<?php endif; ?>

<h2 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">📝 Criar Nova Conta</h2>

<form method="POST" action="">
    <div class="form-group">
        <label for="nome">👤 Nome Completo (máx 100 caracteres):</label>
        <input type="text" id="nome" name="nome" required maxlength="100"
               value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>">
        <small style="color: #666; font-size: 12px;">Máximo 100 caracteres</small>
    </div>
    
    <div class="form-group">
        <label for="login">🔑 Login (máx 40 caracteres):</label>
        <input type="text" id="login" name="login" required maxlength="40"
               value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>">
        <small style="color: #666; font-size: 12px;">Máximo 40 caracteres</small>
    </div>
    
    <div class="form-group">
        <label for="email">📧 Email (máx 150 caracteres):</label>
        <input type="email" id="email" name="email" required maxlength="150"
               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        <small style="color: #666; font-size: 12px;">Máximo 150 caracteres</small>
    </div>
    
    <div class="form-group">
        <label for="senha">🔒 Senha (mínimo 6 caracteres):</label>
        <input type="password" id="senha" name="senha" required minlength="6">
        <small style="color: #666; font-size: 12px;">Senha será criptografada com MD5</small>
    </div>
    
    <div class="form-group">
        <label for="confirmar_senha">✅ Confirmar Senha:</label>
        <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="6">
    </div>
    
    <button type="submit" class="btn btn-success">📋 Cadastrar</button>
</form>

<div class="links">
    <a href="entrar.php">↩️ Já tenho uma conta</a>
</div>

<?php include 'rodape.php'; ?>