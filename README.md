# Essência Girls Girl — E-commerce de Perfumes e Pregadores de Cabelo

Projeto final desenvolvido individualmente para a disciplina de **Desenvolvimento para Web II** do curso de **Tecnologia em Análise e Desenvolvimento de Sistemas (TADS) — IFRS Campus Canoas**, ministrada pelo Prof. **Marcio Bigolin**.

---

## Links Obrigatórios

* **Aplicação Hospedada (Locaweb):** http://essenciagirlsgir1.hospedagemdesites.ws/
* **Repositório Público (GitHub):** https://github.com/MCristinne/lojaEssenciaGirlsGirl

---

## Tecnologias Utilizadas

* **Back-end:** PHP puro (sem frameworks) com PDO, arquitetura modular e API REST JSON (`api.php`)
* **Front-end Tradicional:** HTML5, CSS3, Bootstrap 5, JavaScript puro e AJAX (`fetch`)
* **Front-end SPA:** React + React Router + Vite (`spa-react/`)
* **Banco de Dados:** MySQL (`banco.sql`)
* **Inteligência Artificial:** Integração server-side com a API Google Gemini (`gemini-3.8-flash`)

---

## Arquitetura e Funcionalidades Implementadas

### 1. Segurança, Sessões e Auditoria
* **Conexão Isolada (`config/Conexao.php`):** Classe PDO dedicada para facilitar a troca entre ambiente local (XAMPP) e produção (Locaweb).
* **Proteção de Credenciais (`config/segredos.php`):** Isolamento da chave da API Gemini fora do controle de versão (`.gitignore`).
* **Controle de Sessão (`utils/Sessao.php`):** Cookies configurados com `HttpOnly` e `SameSite=Lax`, além de `session_regenerate_id(true)` no login para mitigação de *Session Fixation*.
* **Sistema de Logs em Arquivo (`utils/Logger.php`):** Registro automático em `logs/sistema.log` contendo timestamp ISO-8601, nível (`INFO`, `WARNING`, `ERROR`), ação executada, usuário autenticado, endereço IP e *User-Agent*, sem armazenar senhas ou tokens.

### 2. Modelagem do Banco de Dados (`banco.sql` — 7 Tabelas)
* **Cadastros Simples (2):** `categorias` e `estilos`.
* **Entidades e Relação `1..N`:** `usuarios` e `produtos` (vinculados `1..N` a `categorias`).
* **Relações `N..N` (2):**
  * `produto_estilos`: associação `N..N` entre `produtos` e `estilos`.
  * `pedidos` e `itens_pedido`: carrinho de compras e finalização transacional `N..N` entre `pedidos` e `produtos`, com baixa automática de estoque.

### 3. Telas Públicas, Privadas e SEO/GEO
* **Vitrine Pública (`index.php`):** Listagem inicial de produtos com campo de busca em tempo real via AJAX (`api.php?acao=produtos`).
* **Coleções e Estilos (`colecoes.php`):** Segunda listagem pública com filtragem por categorias e notas/estilos.
* **Detalhamento Dinâmico (`produto.php?id=X`):** Página única para exibição individual de qualquer produto via parâmetro `GET` com consultas preparadas.
* **Autenticação Completa (`login.php` e `logout.php`):** Login, recuperação de senha e cadastro de novo usuário com validação front-end/back-end e upload de foto de perfil salva em arquivo (`uploads/perfis/`).
* **Gestão Administrativa (`admin_cadastros.php`):** Área protegida por login para operações CRUD de categorias, estilos, produtos e vínculos `N..N`.
* **Fale Conosco (`contato.php`):** Formulário de contato com validação e envio de e-mail via PHP.
* **Termos, LGPD e SEO (`termos.php` e `includes/header.php`):** Política de privacidade (LGPD), política antiplágio, meta-tag `<meta name="plagiarism">` e tags Open Graph (`og:*`) em todas as páginas públicas.

### 4. Módulo React + React Router + Consultor IA (`spa-react/`)
* **Carrinho de Compras (SPA):** Sincronização com o `localStorage` da vitrine, ajuste de quantidades e envio via `POST` para `api.php?acao=finalizar_pedido`.
* **Consultor de Estilo & Fragrâncias (LLM):** Recebe a ocasião desejada pelo cliente e consulta a API do Gemini no back-end (`api.php?acao=recomendar_ia`) usando o catálogo real do banco para recomendar combinações personalizadas.

---

## Estrutura de Pastas

```text
lojaEssenciaGirlsGirl/
├── config/
│   ├── Conexao.php          # Conexão PDO com o MySQL
│   └── segredos.php         # Chave da API