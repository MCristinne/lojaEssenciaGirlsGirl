<?php
require_once __DIR__ . '/../utils/Sessao.php';
Sessao::iniciar();
$tituloPagina = $tituloPagina ?? 'Essência & Forma — Perfumes e Pregadores de Cabelo';
$descPagina = $descPagina ?? 'Loja virtual de perfumes artesanais, fragrâncias exclusivas e pregadores de cabelo.';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina) ?></title>
    <meta name="description" content="<?= htmlspecialchars($descPagina) ?>">
    <!-- Tags obrigatórias de Plágio e Open Graph -->
    <meta name="plagiarism" content="original-content-academic-project-ifrs">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="<?= htmlspecialchars($tituloPagina) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($descPagina) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://<?= htmlspecialchars($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Essência & Forma</a>
        <div class="navbar-nav me-auto">
            <a class="nav-link" href="index.php">Vitrine</a>
            <a class="nav-link" href="colecoes.php">Coleções & Estilos</a>
            <a class="nav-link" href="spa-react/dist/index.html">Consultor IA & Carrinho (React)</a>
            <a class="nav-link" href="contato.php">Fale Conosco</a>
            <a class="nav-link" href="termos.php">Termos & LGPD</a>
        </div>
        <div class="d-flex align-items-center text-white gap-2">
            <?php if (!empty($_SESSION['usuario_id'])): ?>
                <img src="uploads/perfis/<?= htmlspecialchars($_SESSION['usuario_foto']) ?>" width="32" height="32" class="rounded-circle object-fit-cover">
                <span>Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
                <a href="admin_cadastros.php" class="btn btn-sm btn-outline-light">Cadastros</a>
                <a href="logout.php" class="btn btn-sm btn-danger">Sair</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-sm btn-outline-light">Entrar / Cadastrar</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="container">