<?php
require_once 'config/Conexao.php';
require_once 'utils/Sessao.php';
require_once 'utils/Logger.php';

Sessao::exigirLogin();
$pdo = Conexao::getConexao();
$mensagem = '';
$tipoAlerta = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    try {
        // 1. CADASTRO SIMPLES 1: CATEGORIA
        if ($acao === 'add_categoria') {
            $nome = trim($_POST['nome'] ?? '');
            $desc = trim($_POST['descricao'] ?? '');
            if (strlen($nome) < 3) throw new Exception('Nome da categoria deve ter ao menos 3 caracteres.');

            $stmt = $pdo->prepare("INSERT INTO categorias (nome, descricao) VALUES (?, ?)");
            $stmt->execute([$nome, $desc]);
            Logger::registrar('INFO', 'CRUD_CREATE_CATEGORIA', "Categoria: $nome");
            $mensagem = 'Categoria cadastrada com sucesso!';
        }

        // 2. CADASTRO SIMPLES 2: ESTILO / NOTA OLFATIVA
        if ($acao === 'add_estilo') {
            $nome = trim($_POST['nome'] ?? '');
            if (strlen($nome) < 2) throw new Exception('Nome do estilo inválido.');

            $stmt = $pdo->prepare("INSERT INTO estilos (nome) VALUES (?)");
            $stmt->execute([$nome]);
            Logger::registrar('INFO', 'CRUD_CREATE_ESTILO', "Estilo: $nome");
            $mensagem = 'Estilo cadastrado com sucesso!';
        }

        // 3. CADASTRO 1..N + N..N: PRODUTO E SEUS ESTILOS
        if ($acao === 'add_produto') {
            $categoriaId = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);
            $nome = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $preco = floatval(str_replace(',', '.', $_POST['preco'] ?? 0));
            $estoque = filter_input(INPUT_POST, 'estoque', FILTER_VALIDATE_INT) ?: 1;
            $estilosSelecionados = $_POST['estilos'] ?? [];

            if (!$categoriaId || strlen($nome) < 3 || $preco <= 0) {
                throw new Exception('Preencha categoria, nome e preço válido.');
            }

            $nomeImagem = 'sem_foto.jpg';
            if (!empty($_FILES['imagem']['name']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                $tipos = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array(mime_content_type($_FILES['imagem']['tmp_name']), $tipos)) {
                    throw new Exception('Formato de imagem inválido (use JPG, PNG ou WEBP).');
                }
                $dir = __DIR__ . '/uploads/produtos/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
                $nomeImagem = uniqid('prod_') . '.' . $ext;
                move_uploaded_file($_FILES['imagem']['tmp_name'], $dir . $nomeImagem);
            }

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO produtos (categoria_id, nome, descricao, preco, estoque, imagem) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$categoriaId, $nome, $descricao, $preco, $estoque, $nomeImagem]);
            $produtoId = $pdo->lastInsertId();

            // Grava vínculos N..N na tabela produto_estilos
            $stmtVinculo = $pdo->prepare("INSERT INTO produto_estilos (produto_id, estilo_id) VALUES (?, ?)");
            foreach ($estilosSelecionados as $estId) {
                $stmtVinculo->execute([$produtoId, intval($estId)]);
            }
            $pdo->commit();

            Logger::registrar('INFO', 'CRUD_CREATE_PRODUTO', "Produto #$produtoId: $nome");
            $mensagem = 'Produto e estilos vinculados cadastrados!';
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $mensagem = $e->getMessage();
        $tipoAlerta = 'danger';
    }
}

// Exclusão rápida via GET (para demonstrar CRUD completo)
if (isset($_GET['excluir_produto'])) {
    $idDel = filter_input(INPUT_GET, 'excluir_produto', FILTER_VALIDATE_INT);
    if ($idDel) {
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
        $stmt->execute([$idDel]);
        Logger::registrar('WARNING', 'CRUD_DELETE_PRODUTO', "Produto ID excluído: $idDel");
        header('Location: admin_cadastros.php');
        exit;
    }
}

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nome")->fetchAll();
$estilos = $pdo->query("SELECT * FROM estilos ORDER BY nome")->fetchAll();
$produtos = $pdo->query("SELECT p.*, c.nome AS categoria FROM produtos p JOIN categorias c ON p.categoria_id = c.id ORDER BY p.id DESC")->fetchAll();

$tituloPagina = 'Painel de Cadastros — Essência Girls Girl';
require_once 'includes/header.php';
?>

<h2 class="mb-4">Gerenciamento de Cadastros (Área Restrita)</h2>

<?php if ($mensagem): ?>
    <div class="alert alert-<?= $tipoAlerta ?>"><?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>

<div class="row g-4 mb-5">
    <!-- Cadastro Simples 1: Categorias -->
    <div class="col-md-6">
        <div class="card p-3 h-100">
            <h5>1. Nova Categoria (Cadastro Simples)</h5>
            <form method="POST">
                <input type="hidden" name="acao" value="add_categoria">
                <div class="mb-2">
                    <input type="text" name="nome" class="form-control" placeholder="Nome (ex: Perfumes Doces)" required>
                </div>
                <div class="mb-2">
                    <input type="text" name="descricao" class="form-control" placeholder="Descrição breve">
                </div>
                <button class="btn btn-dark btn-sm">Salvar Categoria</button>
            </form>
        </div>
    </div>

    <!-- Cadastro Simples 2: Estilos -->
    <div class="col-md-6">
        <div class="card p-3 h-100">
            <h5>2. Novo Estilo / Nota Olfativa (Cadastro Simples)</h5>
            <form method="POST">
                <input type="hidden" name="acao" value="add_estilo">
                <div class="mb-2">
                    <input type="text" name="nome" class="form-control" placeholder="Nome (ex: Cítrico, Luxo, Casual)" required>
                </div>
                <button class="btn btn-dark btn-sm">Salvar Estilo</button>
            </form>
        </div>
    </div>
</div>

<!-- Cadastro 1..N + N..N: Produto -->
<div class="card p-4 mb-5">
    <h5>3. Cadastrar Produto (Relação 1..N com Categoria e N..N com Estilos)</h5>
    <form method="POST" enctype="multipart/form-data" class="row g-3">
        <input type="hidden" name="acao" value="add_produto">
        <div class="col-md-4">
            <label>Categoria (1..N)</label>
            <select name="categoria_id" class="form-select" required>
                <option value="">Selecione...</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label>Nome do Produto</label>
            <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label>Preço (R$)</label>
            <input type="number" step="0.01" name="preco" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label>Estoque</label>
            <input type="number" name="estoque" value="10" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label>Descrição</label>
            <input type="text" name="descricao" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label>Foto do Produto</label>
            <input type="file" name="imagem" accept="image/*" class="form-control" required>
        </div>
        <div class="col-12">
            <label class="d-block mb-1">Estilos / Notas (Relação N..N):</label>
            <?php foreach ($estilos as $e): ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="estilos[]" value="<?= $e['id'] ?>" id="est_<?= $e['id'] ?>">
                    <label class="form-check-label" for="est_<?= $e['id'] ?>"><?= htmlspecialchars($e['nome']) ?></label>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Cadastrar Produto</button>
        </div>
    </form>
</div>

<h4>Produtos Cadastrados</h4>
<table class="table table-bordered align-middle">
    <thead>
        <tr>
            <th>ID</th>
            <th>Produto</th>
            <th>Categoria</th>
            <th>Preço</th>
            <th>Ação</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($produtos as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['nome']) ?></td>
                <td><?= htmlspecialchars($p['categoria']) ?></td>
                <td>R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
                <td>
                    <a href="?excluir_produto=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Excluir este produto?')">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'includes/footer.php'; ?>