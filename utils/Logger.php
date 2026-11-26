<?php
class Logger {
    public static function registrar(string $nivel, string $acao, string $detalhes = ''): void {
        $dir = __DIR__ . '/../logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $data = date('c');
        $userId = $_SESSION['usuario_id'] ?? 'visitante';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'desconhecido', 0, 120);

        $linha = sprintf(
            "[%s] [%s] ACAO=%s | USER=%s | IP=%s | DETALHES=%s | AGENT=%s" . PHP_EOL,
            $data, strtoupper($nivel), $acao, $userId, $ip, $detalhes, $agent
        );

        file_put_contents($dir . '/sistema.log', $linha, FILE_APPEND | LOCK_EX);
    }
}