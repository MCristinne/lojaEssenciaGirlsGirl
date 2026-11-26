<?php
require_once 'utils/Logger.php';
Logger::registrar('INFO', 'PAGE_VIEW', 'Termos e LGPD');

$tituloPagina = 'Termos de Serviço, LGPD e Política de Plágio — Essência Girls Girl';
require_once 'includes/header.php';
?>

<div class="card p-4">
    <h2>Termos de Serviço, Privacidade (LGPD) e Política de Plágio</h2>
    <hr>
    <h5>1. Proteção de Dados Pessoais (LGPD — Lei nº 13.709/2018)</h5>
    <p>A <strong>Essência Girls Girl</strong> coleta apenas os dados estritamente necessários para autenticação e processamento de pedidos (nome, e-mail, foto de perfil e histórico de compras). As senhas são armazenadas exclusivamente por meio de hash criptográfico irreversível (<code>password_hash</code>), e nenhum dado sensível ou credencial é gravado nos arquivos de auditoria do sistema.</p>

    <h5>2. Política de Plágio e Autoria Acadêmica</h5>
    <p>Todo o código-fonte, modelagem relacional e estrutura desta aplicação foram desenvolvidos individualmente para a disciplina de <strong>Desenvolvimento para Web II (TADS — IFRS Campus Canoas)</strong>. As descrições de produtos, marcas fictícias e conteúdos textuais são originais, protegidos por meta-tags de identificação de autoria e destinados exclusivamente a fins educacionais.</p>

    <h5>3. Uso de Cookies e Sessões</h5>
    <p>Utilizamos cookies de sessão configurados com parâmetros de segurança (<code>HttpOnly</code> e <code>SameSite=Lax</code>) e regeneração automática de identificador após o login para proteção contra sequestro e fixação de sessão.</p>
</div>

<?php require_once 'includes/footer.php'; ?>