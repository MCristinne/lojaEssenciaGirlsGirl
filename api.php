<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config/Conexao.php';
require_once 'utils/Sessao.php';
require_once 'utils/Logger.php';

Sessao::iniciar();
$pdo = Conexao::getConexao();
$acao = $_GET['acao'] ?? '';

try {
    // 1. LISTAR / BUSCAR PRODUTOS (AJAX da Vitrine e do React)
    if ($acao === 'produtos') {
        $busca = '%' . trim($_GET['busca'] ?? '') . '%';
        $stmt = $pdo->prepare("
            SELECT p.*, c.nome AS categoria_nome 
            FROM produtos p 
            JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.nome LIKE ? OR p.descricao LIKE ?
        ");
        $stmt->execute([$busca, $busca]);
        echo json_encode(['status' => 'ok', 'dados' => $stmt->fetchAll()]);
        exit;
    }

    // 2. FINALIZAR PEDIDO / CARRINHO N..N (Recebe JSON do React)
    if ($acao === 'finalizar_pedido' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['status' => 'erro', 'mensagem' => 'Faça login para finalizar a compra.']);
            exit;
        }

        $body = json_decode(file_get_contents('php://input'), true);
        $itens = $body['itens'] ?? [];

        if (empty($itens)) {
            http_response_code(400);
            echo json_encode(['status' => 'erro', 'mensagem' => 'Carrinho vazio.']);
            exit;
        }

        $pdo->beginTransaction();
        $total = 0;
        foreach ($itens as $item) {
            $total += floatval($item['preco']) * intval($item['quantidade']);
        }

        $stmtPedido = $pdo->prepare("INSERT INTO pedidos (usuario_id, total) VALUES (?, ?)");
        $stmtPedido->execute([$_SESSION['usuario_id'], $total]);
        $pedidoId = $pdo->lastInsertId();

        $stmtItem = $pdo->prepare("INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        $stmtEstoque = $pdo->prepare("UPDATE produtos SET estoque = estoque - ? WHERE id = ? AND estoque >= ?");

        foreach ($itens as $item) {
            $qtd = intval($item['quantidade']);
            $prodId = intval($item['id']);
            $stmtItem->execute([$pedidoId, $prodId, $qtd, floatval($item['preco'])]);
            $stmtEstoque->execute([$qtd, $prodId, $qtd]);
        }

        $pdo->commit();
        Logger::registrar('INFO', 'ORDER_CREATED', "Pedido #$pedidoId | Total: R$ $total");
        echo json_encode(['status' => 'ok', 'pedido_id' => $pedidoId]);
        exit;
    }

    // 3. CONSULTOR DE ESTILO & FRAGRÂNCIAS VIA GEMINI (LLM Gratuita)
    if ($acao === 'recomendar_ia' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true);
        $ocasiao = trim($body['ocasiao'] ?? '');

        if (strlen($ocasiao) < 3) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Descreva a ocasião ou estilo desejado.']);
            exit;
        }

        $produtos = $pdo->query("SELECT nome, descricao, preco FROM produtos")->fetchAll();
        $catalogoTexto = json_encode($produtos, JSON_UNESCAPED_UNICODE);

        // Lê a chave do arquivo config/segredos.php (ignorado pelo Git)
        if (file_exists(__DIR__ . '/config/segredos.php')) {
            require_once __DIR__ . '/config/segredos.php';
        }
        $apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : 'COLE_SUA_CHAVE_GEMINI_AQUI';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent";

        $prompt = "Você é a consultora virtual da loja 'Essência Girls Girl' (perfumes e pregadores de cabelo). "
                . "Catálogo atual: $catalogoTexto. "
                . "O cliente pediu sugestão para: '$ocasiao'. "
                . "Recomende 1 perfume e 1 pregador de cabelo do catálogo acima explicando brevemente a combinação em português.";

        $payload = json_encode([
            'contents' => [['parts' => [['text' => $prompt]]]]
        ]);

        // Tentativa 1: cURL forçando IPv4
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . trim($apiKey)
            ],
            CURLOPT_POSTFIELDS => $payload
        ]);
        $resposta = curl_exec($ch);
        curl_close($ch);

        // Tentativa 2 (Fallback se o cURL do XAMPP for bloqueado pelo Windows): file_get_contents
        if ($resposta === false) {
            $contexto = stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-Type: application/json\r\nx-goog-api-key: " . trim($apiKey) . "\r\n",
                    'content' => $payload,
                    'timeout' => 20,
                    'ignore_errors' => true
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);
            $resposta = @file_get_contents($url, false, $contexto);
        }

        if ($resposta === false) {
            echo json_encode(['status' => 'ok', 'resposta' => 'Sem acesso externo à API no XAMPP local (bloqueio de rede/antivírus). Na hospedagem online funcionará normalmente.']);
            exit;
        }

        $jsonIa = json_decode($resposta, true);
        if (isset($jsonIa['error'])) {
            echo json_encode(['status' => 'ok', 'resposta' => 'Erro da API Google: ' . $jsonIa['error']['message']]);
            exit;
        }

        $textoSugestao = $jsonIa['candidates'][0]['content']['parts'][0]['text'] 
            ?? 'Resposta vazia retornada pelo modelo.';

        Logger::registrar('INFO', 'LLM_QUERY', 'Consulta Gemini: ' . substr($ocasiao, 0, 50));
        echo json_encode(['status' => 'ok', 'resposta' => $textoSugestao]);
        exit;
    }

    echo json_encode(['status' => 'erro', 'mensagem' => 'Ação inválida.']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    Logger::registrar('ERROR', 'API_EXCEPTION', $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro interno no servidor.']);
}