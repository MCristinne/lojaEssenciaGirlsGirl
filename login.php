<?php
require_once 'config/Conexao.php';
require_once 'utils/Sessao.php';
require_once 'utils/Logger.php';

Sessao::iniciar();
$pdo = Conexao::getConexao();
$mensagem = '';
$tipoAlerta = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // 1. LOGIN
    if ($acao === 'login') {
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $senha = $_POST['senha'] ?? '';

        if (!$email || empty($senha)) {
            $mensagem = 'Preencha um e-mail válido e sua senha.';
            $tipoAlerta = 'danger';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
                Sessao::autenticarUsuario($usuario);
                header('Location: index.php');
                exit;
            } else {
                Logger::registrar('WARNING', 'LOGIN_FAILED', 'Tentativa para: ' . $email);
                $mensagem = 'E-mail ou senha incorretos.';
                $tipoAlerta = 'danger';
            }
        }
    }

    // 2. CADASTRO COM UPLOAD DE FOTO
    if ($acao === 'cadastrar') {
        $nome = trim($_POST['nome'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $senha = $_POST['senha'] ?? '';

        if (strlen($nome) < 3 || !$email || strlen($senha) < 6) {
            $mensagem = 'Nome (mín. 3 letras), e-mail válido e senha (mín. 6 caracteres) são obrigatórios.';
            $tipoAlerta = 'danger';
        } else {
            $nomeFoto = 'default.png';

            // Validação e salvamento da foto em arquivo
            if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
                $tipoArquivo = mime_content_type($_FILES['foto']['tmp_name']);

                if (!in_array($tipoArquivo, $tiposPermitidos) || $_FILES['foto']['size'] > 2 * 1024 * 1024) {
                    $mensagem = 'A foto deve ser JPG, PNG ou WEBP com no máximo 2MB.';
                    $tipoAlerta = 'danger';
                } else {
                    $dirUploads = __DIR__ . '/uploads/perfis/';
                    if (!is_dir($dirUploads)) mkdir($dirUploads, 0755, true);

                    $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                    $nomeFoto = uniqid('perfil_') . '.' . $extensao;
                    move_uploaded_file($_FILES['foto']['tmp_name'], $dirUploads . $nomeFoto);
                }
            }

            if ($tipoAlerta !== 'danger') {
                try {
                    $hash = password_hash($senha, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, foto_perfil) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$nome, $email, $hash, $nomeFoto]);

                    Logger::registrar('INFO', 'USER_REGISTER', 'Novo cadastro: ' . $email);
                    $mensagem = 'Cadastro realizado na Essência Girls Girl! Faça seu login.';
                    $tipoAlerta = 'success';
                } catch (PDOException $e) {
                    $mensagem = 'Este e-mail já está cadastrado.';
                    $tipoAlerta = 'danger';
                }
            }
        }
    }

    // 3. RECUPERAÇÃO DE SENHA
    if ($acao === 'recuperar') {
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $novaSenha = $_POST['nova_senha'] ?? '';

        if (!$email || strlen($novaSenha) < 6) {
            $mensagem = 'Informe um e-mail válido e a nova senha (mín. 6 caracteres).';
            $tipoAlerta = 'danger';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $token = bin2hex(random_bytes(16));
                $novoHash = password_hash($novaSenha, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE usuarios SET senha_hash = ?, token_recuperacao = ? WHERE id = ?");
                $upd->execute([$novoHash, $token, $user['id']]);

                Logger::registrar('SECURITY', 'PASSWORD_RESET', 'Senha redefinida para: ' . $email);
                $mensagem = 'Senha redefinida com sucesso! Você já pode entrar.';
                $tipoAlerta = 'success';
            } else {
                Logger::registrar('WARNING', 'PASSWORD_RESET_FAILED', 'E-mail inexistente: ' . $email);
                $mensagem = 'E-mail não encontrado na base.';
                $tipoAlerta = 'danger';
            }
        }
    }
}

$tituloPagina = 'Acesso & Cadastro — Essência Girls Girl';
require_once 'includes/header.php';
?>

<?php if ($mensagem): ?>
    <div class="alert alert-<?= $tipoAlerta ?>"><?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Formulário de Login -->
    <div class="col-md-4">
        <div class="card p-3">
            <h4>Entrar</h4>
            <form method="POST">
                <input type="hidden" name="acao" value="login">
                <div class="mb-2">
                    <label>E-mail</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Senha</label>
                    <input type="password" name="senha" class="form-control" required>
                </div>
                <button class="btn btn-dark w-100">Acessar Conta</button>
            </form>
        </div>
    </div>

    <!-- Formulário de Cadastro com Foto -->
    <div class="col-md-4">
        <div class="card p-3">
            <h4>Criar Conta</h4>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="acao" value="cadastrar">
                <div class="mb-2">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" minlength="3" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>E-mail</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Senha (mín. 6 dígitos)</label>
                    <input type="password" name="senha" minlength="6" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Foto de Perfil (JPG/PNG)</label>
                    <input type="file" name="foto" accept="image/png, image/jpeg, image/webp" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100">Cadastrar</button>
            </form>
        </div>
    </div>

    <!-- Recuperação de Senha -->
    <div class="col-md-4">
        <div class="card p-3">
            <h4>Recuperar Senha</h4>
            <form method="POST">
                <input type="hidden" name="acao" value="recuperar">
                <div class="mb-2">
                    <label>E-mail Cadastrado</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Nova Senha</label>
                    <input type="password" name="nova_senha" minlength="6" class="form-control" required>
                </div>
                <button class="btn btn-outline-secondary w-100">Redefinir Senha</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>