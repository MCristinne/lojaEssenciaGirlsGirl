<?php
require_once __DIR__ . '/Logger.php';

class Sessao {
    public static function iniciar(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    public static function autenticarUsuario(array $usuario): void {
        self::iniciar();
        // Mitiga Session Fixation gerando novo ID e apagando a sessão antiga
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_foto'] = $usuario['foto_perfil'];
        $_SESSION['perfil'] = $usuario['perfil'];

        Logger::registrar('INFO', 'LOGIN_SUCCESS', 'Email: ' . $usuario['email']);
    }

    public static function exigirLogin(): void {
        self::iniciar();
        if (empty($_SESSION['usuario_id'])) {
            Logger::registrar('SECURITY', 'UNAUTHORIZED_ACCESS', $_SERVER['REQUEST_URI'] ?? '');
            header('Location: login.php');
            exit;
        }
    }
}