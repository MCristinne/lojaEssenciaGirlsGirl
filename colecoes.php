<?php
require_once 'config/Conexao.php';
require_once 'utils/Logger.php';

$pdo = Conexao::getConexao();
Logger::registrar('INFO', 'PAGE_VIEW', 'Listagem colecoes.php');

$categorias = $pdo->query("SELECT * FROM categorias")->fetchAll();
$estilos = $pdo->query("
    SELECT e.nome AS estilo, p.id, p.nome AS produto, p.preco
    FROM estilos e
    JOIN produto_estilos pe ON e.id = pe.estilo_id
    JOIN produtos p ON pe.produto_id = p.id
    ORDER BY e.nome
")->fetchAll();

$tituloPagina = 'Coleções e Estilos — Essência Girls Girl';
require_once 'includes/header.php';
?>

<h2 class="mb-4">Categorias da Loja</h2>
<div class="row g-3 mb-5">
    <?php foreach ($categorias as $cat): ?>
        <div class="col-md-4">
            <div class="card p-3 h-100">
                <h5><?= htmlspecialchars($cat['nome']) ?></h5>
                <p class="text-muted mb-0"><?= htmlspecialchars($cat['descricao']) ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<h2 class="mb-3">Produtos por Estilo & Nota Olfativa (Relação N..N)</h2>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Estilo / Nota</th>
            <th>Produto</th>
            <th>Preço</th>
            <th>Ação</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($estilos as $item): ?>
            <tr>
                <td><span class="badge bg-dark"><?= htmlspecialchars($item['estilo']) ?></span></td>
                <td><?= htmlspecialchars($item['produto']) ?></td>
                <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                <td><a href="produto.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary">Abrir</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'includes/footer.php'; ?>