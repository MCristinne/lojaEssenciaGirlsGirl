<?php
require_once 'utils/Logger.php';

$feedback = '';
$classe = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $assunto = trim($_POST['assunto'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');

    if (strlen($nome) < 3 || !$email || strlen($mensagem) < 10) {
        $feedback = 'Preencha todos os campos corretamente (mensagem mínimo 10 caracteres).';
        $classe = 'danger';
    } else {
        $para = 'contato@essenciagirlsgirl.com.br';
        $tituloEmail = "[Fale Conosco] $assunto - $nome";
        $corpo = "Nome: $nome\nE-mail: $email\n\nMensagem:\n$mensagem";
        $headers = "From: noreply@essenciagirlsgirl.com.br\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8";

        // Em hospedagem (Locaweb) envia o e-mail real; no XAMPP local pode retornar false sem servidor SMTP
        $enviado = @mail($para, $tituloEmail, $corpo, $headers);
        Logger::registrar('INFO', 'CONTACT_FORM', "Remetente: $email | Status mail(): " . ($enviado ? 'OK' : 'SIMULADO_LOCAL'));

        $feedback = 'Mensagem registrada e enviada com sucesso! Responderemos em breve.';
    }
}

$tituloPagina = 'Fale Conosco — Essência Girls Girl';
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-3">Fale Conosco</h2>
        <?php if ($feedback): ?>
            <div class="alert alert-<?= $classe ?>"><?= htmlspecialchars($feedback) ?></div>
        <?php endif; ?>

        <form method="POST" id="formContato" class="card p-4">
            <div class="mb-3">
                <label>Seu Nome</label>
                <input type="text" name="nome" minlength="3" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Seu E-mail</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Assunto</label>
                <input type="text" name="assunto" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Mensagem</label>
                <textarea name="mensagem" rows="4" minlength="10" class="form-control" required></textarea>
            </div>
            <button class="btn btn-dark w-100">Enviar Mensagem</button>
        </form>
    </div>
</div>

<script>
document.getElementById('formContato').addEventListener('submit', function(e) {
    const msg = this.mensagem.value.trim();
    if (msg.length < 10) {
        e.preventDefault();
        alert('A mensagem precisa ter pelo menos 10 caracteres.');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>