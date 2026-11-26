# Essência Girls Girl — E-commerce de Perfumes e Pregadores de Cabelo

Projeto Final da disciplina de **Desenvolvimento para Web II (TADS — IFRS Campus Canoas)**.

## Links Obrigatórios
* **Aplicação Hospedada:** https://SEU-DOMINIO-NA-LOCAWEB.com.br
* **Repositório Público (GitHub/GitLab):** https://github.com/SEU-USUARIO/lojaEssenciaGirlsGirl

## Funcionalidades Implementadas
* **Backend PHP Cru + PDO:** Arquitetura modular com conexão isolada (`config/Conexao.php`), mitigação de *Session Fixation* (`session_regenerate_id(true)`) e sistema de auditoria em arquivo (`logs/sistema.log`).
* **Banco de Dados Relacional (7 tabelas):** Cadastros simples (`categorias`, `estilos`), relação `1..N` (`categorias` -> `produtos`) e duas relações `N..N` (`produto_estilos` e `pedidos` + `itens_pedido`).
* **Front-end + React + IA:** Vitrine com busca AJAX, detalhamento dinâmico (`produto.php?id=X`), SPA em React + React Router (`spa-react`) para carrinho de compras e consultor de fragrâncias integrado à API do Gemini.