<?php
require_once 'config/Conexao.php';
require_once 'utils/Logger.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit;
}

$pdo = Conexao::getConexao();
$stmt = $pdo->prepare("
    SELECT p.*, c.nome AS categoria_nome 
    FROM produtos p 
    JOIN categorias c ON p.categoria_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$id]);
$produto = $stmt->fetch();

if (!$produto) {
    header('Location: index.php');
    exit;
}

// Busca os estilos vinculados na relação N..N
$stmtEstilos = $pdo->prepare("
    SELECT e.nome FROM estilos e 
    JOIN produto_estilos pe ON e.id = pe.estilo_id 
    WHERE pe.produto_id = ?
");
$stmtEstilos->execute([$id]);
$estilos = $stmtEstilos->fetchAll(PDO::FETCH_COLUMN);

Logger::registrar('INFO', 'PRODUCT_DETAIL', "Produto ID: $id - " . $produto['nome']);

$tituloPagina = $produto['nome'] . ' — Essência Girls Girl';
$descPagina = $produto['descricao'];
require_once 'includes/header.php';
?>

<div class="row g-4 align-items-center">
    <div class="col-md-5">
        <img src="uploads/produtos/<?= htmlspecialchars($produto['imagem']) ?>" class="img-fluid rounded shadow-sm" alt="<?= htmlspecialchars($produto['nome']) ?>">
    </div>
    <div class="col-md-7">
        <span class="badge bg-secondary mb-2"><?= htmlspecialchars($produto['categoria_nome']) ?></span>
        <h1><?= htmlspecialchars($produto['nome']) ?></h1>
        <p class="lead"><?= htmlspecialchars($produto['descricao']) ?></p>

        <div class="mb-3">
            <strong>Estilos / Notas:</strong>
            <?php foreach ($estilos as $est): ?>
                <span class="badge bg-info text-dark me-1"><?= htmlspecialchars($est) ?></span>
            <?php endforeach; ?>
        </div>

        <h3 class="text-success mb-3">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></h3>
        <p class="text-muted">Estoque disponível: <?= (int)$produto['estoque'] ?> unidades</p>

        <button class="btn btn-dark btn-lg" onclick='comprarAgora(<?= json_encode($produto) ?>)'>Comprar e Ir para o Carrinho</button>
        <a href="index.php" class="btn btn-outline-secondary btn-lg ms-2">Voltar</a>
    </div>
</div>

<script>
function comprarAgora(produto) {
    let carrinho = JSON.parse(localStorage.getItem('carrinho_egg') || '[]');
    const existente = carrinho.find(item => item.id == produto.id);
    if (existente) existente.quantidade += 1;
    else carrinho.push({ id: produto.id, nome: produto.nome, preco: produto.preco, quantidade: 1 });
    localStorage.setItem('carrinho_egg', JSON.stringify(carrinho));
    window.location.href = 'spa-react/dist/index.html';
}
</script>

<?php require_once 'includes/footer.php'; ?>