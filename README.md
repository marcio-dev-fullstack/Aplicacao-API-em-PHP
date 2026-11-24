# 🛡️ FinTech API v1 - Laravel 11 (SaaS Backend)

API RESTful para gestão financeira e transferências entre contas bancárias, construída com **Laravel 11**, **PHP 8.3**, **PostgreSQL** e **Redis**. Desenvolvida com foco em **segurança avançada**, **integridade transacional**, **arquitetura limpa** e **proteção contra vulnerabilidades OWASP API Top 10**.

---

## 🚀 Tecnologias & Stack
- **Framework:** Laravel 11 (PHP 8.3)
- **Autenticação:** Laravel Sanctum (Tokens Revogáveis Stateless)
- **Banco de Dados:** PostgreSQL 16 (Transações ACID + Pessimistic Locking)
- **Cache & Rate Limiting:** Redis 7
- **Conteinerização:** Docker & Docker Compose
- **Testes:** PHPUnit / Pest PHP
- **Padrões de Código:** PSR-12, Form Requests, Services, API Resources, Policies

---

## 🔒 Práticas de Segurança Implementadas (OWASP API Top 10)

1. **Proteção contra BOLA / IDOR (API1:2023):**
   - Implementação de `AccountPolicy` para garantir que um usuário autenticado só acesse e execute operações em suas próprias contas bancárias.
2. **Prevenção de Race Conditions & Concorrência (API8:2023):**
   - Uso de **Locks Pessimistas** (`lockForUpdate()`) em consultas SQL dentro de uma `DB::transaction()` atômica para impedir que requisições simultâneas efetuem saques ou transferências com saldo insuficiente.
3. **Valores Monetários sem Erro de Ponto Flutuante:**
   - Armazenamento de todos os valores e saldos em **centavos** (`integer` / `bigInteger`), eliminando imprecisões de arredondamento de float.
4. **Rate Limiting & Anti-Brute Force (API4:2023):**
   - Rate limiting restrito no login (`10 requisições/minuto`) e em transferências (`30 operações/minuto`), usando Redis.
5. **Autenticação e Revogação Segura (API2:2023):**
   - Tokens com tempo de expiração, revogação explícita no logout e hashes de senha com **Bcrypt/Argon2id**.
6. **Headers de Segurança HTTP (API7:2023):**
   - Middleware global injetando `X-Frame-Options`, `X-Content-Type-Options`, `Content-Security-Policy`, `Strict-Transport-Security` e `Referrer-Policy`.
7. **Idempotência de Transações:**
   - Suporte ao cabeçalho `X-Idempotency-Key` para evitar cobranças ou transferências duplicadas em retentativas de rede.

---

## 📁 Estrutura do Projeto

---

## ⚡ Instalação & Execução com Docker

### Pré-requisitos
- Docker & Docker Compose instalados.

### Passo a Passo

1. **Descompactar o arquivo v1.zip e acessar o diretório:**
   ```bash
   unzip v1.zip

   cd fintech-api-v1

   cp .env.example .env

   docker compose up -d --build

   docker compose exec app composer install

   docker compose exec app php artisan key:generate

   docker compose exec app php artisan migrate

   docker compose exec app php artisan test

   A API estará disponível em http://localhost:8080/api/v1.

📌 Principais Endpoints
🔑 Autenticação
POST /api/v1/auth/register — Registrar novo usuário + conta automática

POST /api/v1/auth/login — Autenticar e obter Bearer Token

POST /api/v1/auth/logout — Revogar token atual (Autenticado)

GET /api/v1/auth/me — Detalhes do usuário autenticado

🏦 Contas Bancárias
GET /api/v1/accounts — Listar contas do usuário logado

GET /api/v1/accounts/{account}/balance — Consultar saldo da conta (Requer autorização de Policy)

GET /api/v1/accounts/{account}/statement — Extrato de movimentações paginado

💸 Transações Financeiras
POST /api/v1/transactions/transfer — Realizar transferência atômica com Lock Pessimista

Headers recomendados: Authorization: Bearer <token>, X-Idempotency-Key: <uuid-v4>

Body JSON:

{
  "payer_account_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "payee_account_id": "9b1deb4d-4c8e-5cfe-0cee-3c1e8c4edc7e",
  "amount": 15000
}

*(Note: `15000` centavos = R$ 150,00)*
POST /api/v1/transactions/deposit — Simulação de depósito/aporte na conta

GET /api/v1/transactions/{transaction} — Consultar comprovante de transação

🧪 Testes Automatizados
O projeto conta com testes de integração cobrindo:

Registro e autenticação de usuários

Transferência bem-sucedida entre contas

Erro por saldo insuficiente (InsufficientBalanceException)

Proteção BOLA/IDOR (tentativa de transferência usando conta de outro usuário sem autorização)

Idempotência de transações

Para rodar os testes:

docker compose exec app php artisan test

📄 Licença
Zentix Engenharia & Tecnologia

Quer dar vida ao seu projeto?
Entre em contato conosco e solicite uma avaliação técnica ou orçamento sem compromisso.

📲 Falar no WhatsApp: (62) 99646-6033

📍 Av. Portugal, 1148, Quadra L29, Lote 1E, Sala C2501, Setor Marista, Goiânia - GO, CEP 74150-030

Zentix Engenharia & Tecnologia

© 2022. Todos os direitos reservados

Desenvolvido por: ZENTIX

