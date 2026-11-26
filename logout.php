<?php
require_once 'utils/Sessao.php';
require_once 'utils/Logger.php';

Sessao::iniciar();
Logger::registrar('INFO', 'LOGOUT', 'Usuário encerrou sessão');
session_unset();
session_destroy();
header('Location: index.php');
exit;