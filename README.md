# 🛡️ FinTech API v1 - Laravel 11 (SaaS Backend)

API RESTful corporativa para gestão financeira e transferências entre contas bancárias, construída com **Laravel 11**, **PHP 8.3**, **PostgreSQL** e **Redis**.

## 📋 Sumário

* [Sobre o Projeto](#-sobre-o-projeto)
* [Tecnologias & Stack](#-tecnologias--stack)
* [Segurança (OWASP API Top 10)](#-segurança-owasp-api-top-10)
* [Estrutura do Projeto](#-estrutura-do-projeto)
* [Instalação & Execução](#-instalação--execução)
* [Documentação dos Endpoints](#-documentação-dos-endpoints)
* [Testes Automatizados](#-testes-automatizados)
* [Licença & Contato](#-licença--contato)

## 📖 Sobre o Projeto

A **FinTech API v1** é uma solução backend projetada com foco em **segurança avançada**, **integridade transacional (ACID)** e **arquitetura limpa**. O sistema garante consistência financeira em cenários de alta concorrência e implementa defesas nativas contra as principais vulnerabilidades listadas no **OWASP API Security Top 10**.

## 🚀 Tecnologias & Stack

| Camada | Tecnologia | Finalidade | 
| ----- | ----- | ----- | 
| **Linguagem** | PHP 8.3 | Tipagem estrita, performance e recursos modernos | 
| **Framework** | Laravel 11 | Arquitetura MVC, Eloquent ORM, Services e Policies | 
| **Autenticação** | Laravel Sanctum | Tokens revogáveis *stateless* com expiração | 
| **Banco de Dados** | PostgreSQL 16 | Transações ACID e *Pessimistic Locking* | 
| **Cache & Filas** | Redis 7 | *Rate Limiting* distribuído e chaves de idempotência | 
| **Infraestrutura** | Docker & Nginx | Ambiente isolado, reproduzível e pronto para produção | 
| **Qualidade** | PHPUnit / Pest & PSR-12 | Testes automatizados e padronização de código | 

## 🔒 Segurança (OWASP API Top 10)

1. **Proteção contra BOLA / IDOR (`API1:2023`)**
   * Implementação de `AccountPolicy` garantindo que usuários autenticados acessem e movimentem exclusivamente suas próprias contas.
2. **Prevenção de Race Conditions & Concorrência (`API8:2023`)**
   * Uso de **Locks Pessimistas** (`lockForUpdate()`) dentro de transações atômicas (`DB::transaction()`), bloqueando saques ou transferências simultâneas com saldo insuficiente.
3. **Precisão Monetária (Zero Floating-Point Errors)**
   * Todos os valores e saldos são armazenados e processados em **centavos** (`bigInteger`), eliminando falhas de arredondamento.
4. **Rate Limiting & Anti-Brute Force (`API4:2023`)**
   * Controle de taxa via Redis: máximo de **10 req/min** no login e **30 req/min** em operações de transferência.
5. **Gestão Segura de Sessões (`API2:2023`)**
   * Tokens com expiração configurada, revogação imediata no logout e senhas protegidas com **Bcrypt / Argon2id**.
6. **Security Headers HTTP (`API7:2023`)**
   * Middleware global injetando `X-Frame-Options`, `X-Content-Type-Options`, `Content-Security-Policy`, `Strict-Transport-Security` e `Referrer-Policy`.
7. **Idempotência de Transações**
   * Suporte ao cabeçalho `X-Idempotency-Key` para impedir duplicidade de cobranças em retentativas de rede.

## 📁 Estrutura do Projeto

```text
fintech-api-v1/
├── app/
│   ├── Exceptions/
│   │   ├── InsufficientBalanceException.php
│   │   └── InvalidTransactionException.php
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── AccountController.php
│   │   │   └── TransactionController.php
│   │   ├── Middleware/
│   │   │   └── SecurityHeaders.php
│   │   ├── Requests/
│   │   │   ├── RegisterRequest.php
│   │   │   ├── LoginRequest.php
│   │   │   └── TransferRequest.php
│   │   └── Resources/
│   │       ├── AccountResource.php
│   │       ├── TransactionResource.php
│   │       └── UserResource.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Account.php
│   │   └── Transaction.php
│   ├── Policies/
│   │   └── AccountPolicy.php
│   └── Services/
│       └── TransferService.php
├── bootstrap/
│   └── app.php
├── config/
│   ├── auth.php
│   ├── cors.php
│   └── sanctum.php
├── database/
│   └── migrations/
│       ├── 0001_01_01_000000_create_users_table.php
│       ├── 0001_01_01_000001_create_personal_access_tokens_table.php
│       ├── 2026_01_01_000000_create_accounts_table.php
│       └── 2026_01_01_000001_create_transactions_table.php
├── routes/
│   └── api.php
├── tests/
│   ├── Feature/
│   │   ├── AuthTest.php
│   │   └── TransferTest.php
│   └── TestCase.php
├── docker-compose.yml
├── Dockerfile
├── nginx.conf
├── .env.example
├── composer.json
└── README.md
```

## ⚡ Instalação & Execução

### Pré-requisitos

* **Docker** e **Docker Compose** instalados na máquina.

### Passo a Passo

1. **Clone o repositório e acesse o diretório:**
   ```bash
   git clone https://github.com/marcio-dev-fullstack/Fintech.git
   cd Fintech
   ```

2. **Configure as variáveis de ambiente:**
   ```bash
   cp .env.example .env
   ```

3. **Suba os containers (PHP 8.3, PostgreSQL, Redis e Nginx):**
   ```bash
   docker compose up -d --build
   ```

4. **Instale as dependências e gere a chave da aplicação:**
   ```bash
   docker compose exec app composer install
   docker compose exec app php artisan key:generate
   ```

5. **Execute as migrations do banco de dados:**
   ```bash
   docker compose exec app php artisan migrate
   ```

> **🌐 Base URL da API:** `http://localhost:8080/api/v1`

## 📌 Documentação dos Endpoints

### 🔑 Autenticação

| Método | Endpoint | Descrição | Auth | 
| ----- | ----- | ----- | ----- | 
| `POST` | `/api/v1/auth/register` | Registra um novo usuário e cria sua conta automaticamente | Não | 
| `POST` | `/api/v1/auth/login` | Autentica o usuário e retorna o *Bearer Token* | Não | 
| `POST` | `/api/v1/auth/logout` | Revoga o token de acesso atual | Sim | 
| `GET` | `/api/v1/auth/me` | Retorna os dados do usuário autenticado | Sim | 

### 🏦 Contas Bancárias

| Método | Endpoint | Descrição | Auth | 
| ----- | ----- | ----- | ----- | 
| `GET` | `/api/v1/accounts` | Lista todas as contas pertencentes ao usuário logado | Sim | 
| `GET` | `/api/v1/accounts/{account}/balance` | Consulta o saldo atual da conta (validado via `AccountPolicy`) | Sim | 
| `GET` | `/api/v1/accounts/{account}/statement` | Retorna o extrato paginado de movimentações | Sim | 

### 💸 Transações Financeiras

| Método | Endpoint | Descrição | Auth | 
| ----- | ----- | ----- | ----- | 
| `POST` | `/api/v1/transactions/transfer` | Executa transferência atômica entre contas | Sim | 
| `POST` | `/api/v1/transactions/deposit` | Realiza aporte/depósito na conta informada | Sim | 
| `GET` | `/api/v1/transactions/{transaction}` | Exibe o comprovante detalhado de uma transação | Sim | 

#### Exemplo de Requisição — Transferência (`POST /api/v1/transactions/transfer`)

**Headers:**

```http
Authorization: Bearer <seu_token_aqui>
X-Idempotency-Key: 550e8400-e29b-41d4-a716-446655440000
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**

```json
{
  "payer_account_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "payee_account_id": "9b1deb4d-4c8e-5cfe-0cee-3c1e8c4edc7e",
  "amount": 15000
}
```

> *Nota: O campo `amount` representa valores em centavos (`15000` = **R\$ 150,00**).*

## 🧪 Testes Automatizados

A suíte de testes de integração valida os fluxos críticos da aplicação:

* ✅ Registro e autenticação de usuários
* ✅ Transferência bem-sucedida entre contas distintas
* ✅ Bloqueio de transação por saldo insuficiente (`InsufficientBalanceException`)
* ✅ Proteção contra **BOLA/IDOR** (tentativa de movimentar conta de terceiros)
* ✅ Garantia de idempotência em requisições repetidas

Para executar os testes:

```bash
docker compose exec app php artisan test
```

## 📄 Licença & Contato

**Licença:** Zentix Engenharia & Tecnologia

### Quer dar vida ao seu projeto?

Entre em contato conosco e solicite uma avaliação técnica ou orçamento sem compromisso.

- 📲 **WhatsApp:** <a href="https://wa.me/5562996466033" target="_blank" rel="noopener noreferrer">(62) 99646-6033</a>
- 📍 **Endereço:** Av. Portugal, 1148, Quadra L29, Lote 1E, Sala C2501, Setor Marista, Goiânia - GO, CEP 74150-030
- 🌐 **Website:** <a href="http://zentix.eng.br/" target="_blank" rel="noopener noreferrer">zentix.eng.br</a>

---

<p align="center">
  <strong>Zentix Engenharia & Tecnologia</strong><br>
  © 2022–2026. Todos os direitos reservados.<br>
  Desenvolvido por: <a href="http://zentix.eng.br/" target="_blank" rel="noopener noreferrer"><strong>ZENTIX</strong></a>
</p>