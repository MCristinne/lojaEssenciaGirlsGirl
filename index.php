<?php
require_once 'config/Conexao.php';
require_once 'utils/Logger.php';

$pdo = Conexao::getConexao();
Logger::registrar('INFO', 'PAGE_VIEW', 'Vitrine index.php');

$produtos = $pdo->query("
    SELECT p.*, c.nome AS categoria_nome 
    FROM produtos p 
    JOIN categorias c ON p.categoria_id = c.id
")->fetchAll();

$tituloPagina = 'Vitrine — Essência Girls Girl';
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Vitrine Essência Girls Girl</h2>
    <input type="text" id="campoBusca" class="form-control w-50" placeholder="Buscar perfume ou pregador (AJAX)...">
</div>

<div class="row g-4" id="gradeProdutos">
    <?php foreach ($produtos as $p): ?>
        <div class="col-md-3">
            <div class="card h-100">
                <img src="uploads/produtos/<?= htmlspecialchars($p['imagem']) ?>" class="card-img-top" style="height:200px;object-fit:cover;" alt="<?= htmlspecialchars($p['nome']) ?>">
                <div class="card-body d-flex flex-column">
                    <span class="badge bg-secondary mb-2 align-self-start"><?= htmlspecialchars($p['categoria_nome']) ?></span>
                    <h5 class="card-title"><?= htmlspecialchars($p['nome']) ?></h5>
                    <p class="card-text text-muted small"><?= htmlspecialchars(substr($p['descricao'], 0, 75)) ?>...</p>
                    <p class="fw-bold mt-auto">R$ <?= number_format($p['preco'], 2, ',', '.') ?></p>
                    <div class="d-grid gap-2">
                        <a href="produto.php?id=<?= $p['id'] ?>" class="btn btn-outline-dark btn-sm">Ver Detalhes</a>
                        <button class="btn btn-dark btn-sm" onclick='adicionarCarrinho(<?= json_encode($p) ?>)'>+ Carrinho</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
// Busca dinâmica via AJAX consumindo api.php
document.getElementById('campoBusca').addEventListener('input', async (e) => {
    const termo = e.target.value;
    const resp = await fetch(`api.php?acao=produtos&busca=${encodeURIComponent(termo)}`);
    const json = await resp.json();
    const grade = document.getElementById('gradeProdutos');

    grade.innerHTML = json.dados.map(p => `
        <div class="col-md-3">
            <div class="card h-100">
                <img src="uploads/produtos/${p.imagem}" class="card-img-top" style="height:200px;object-fit:cover;">
                <div class="card-body d-flex flex-column">
                    <span class="badge bg-secondary mb-2 align-self-start">${p.categoria_nome}</span>
                    <h5 class="card-title">${p.nome}</h5>
                    <p class="fw-bold mt-auto">R$ ${Number(p.preco).toFixed(2).replace('.', ',')}</p>
                    <div class="d-grid gap-2">
                        <a href="produto.php?id=${p.id}" class="btn btn-outline-dark btn-sm">Ver Detalhes</a>
                        <button class="btn btn-dark btn-sm" onclick='adicionarCarrinho(${JSON.stringify(p)})'>+ Carrinho</button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
});

// Compartilha o carrinho com o módulo React via localStorage
function adicionarCarrinho(produto) {
    let carrinho = JSON.parse(localStorage.getItem('carrinho_egg') || '[]');
    const existente = carrinho.find(item => item.id == produto.id);
    if (existente) {
        existente.quantidade += 1;
    } else {
        carrinho.push({ id: produto.id, nome: produto.nome, preco: produto.preco, quantidade: 1 });
    }
    localStorage.setItem('carrinho_egg', JSON.stringify(carrinho));
    alert(`"${produto.nome}" adicionado ao carrinho!`);
}
</script>

<?php require_once 'includes/footer.php'; ?>