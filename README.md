
# Onfly Travel Management

Sistema Full Stack para gestão de pedidos de viagem corporativa, desenvolvido em Laravel (API REST) e Vue.js (frontend, com Breeze e TypeScript).

## Sumário
- [Visão Geral](#visão-geral)
- [Funcionalidades](#funcionalidades)
- [Como rodar o projeto](#como-rodar-o-projeto)
- [Configuração do ambiente](#configuração-do-ambiente)
- [Testes](#testes)
- [Documentação das APIs](#documentação-das-apis)
- [Funcionalidade extra: Viagem (Travel)](#funcionalidade-extra-viagem-travel)
- [Tecnologias e boas práticas](#tecnologias-e-boas-práticas)
- [Logs e Auditoria](#logs-e-auditoria)
- [Contato](#contato)

---

## Visão Geral
Este projeto permite que empresas gerenciem pedidos de viagem, aprovações, notificações e logística de envio, com autenticação, controle de acesso e rastreabilidade total.

## Funcionalidades
- Cadastro, consulta, listagem e atualização de pedidos de viagem
- Filtros por status, destino e período
- Aprovação/cancelamento de pedidos (apenas admin)
- Notificações por e-mail e sistema
- Cadastro e atualização de viagens (endereços, destinatário, dimensões, etc.)
- Geração automática de viagem após aprovação do pedido
- Envio próprio automático após 24h sem agendamento
- Logs de todas as ações relevantes

## Como rodar o projeto

### 1. Instale as dependências
```bash
composer install
npm install
```

### 2. Configure o ambiente
- Copie `.env.example` para `.env` e ajuste as variáveis (DB, mail, etc.)
- Gere a key do Laravel:
```bash
php artisan key:generate
```

### 3. Suba com Docker (recomendado)
```bash
docker-compose up -d
```

### 4. Rode as migrations e seeders
```bash
php artisan migrate --seed
```

### 5. Inicie o frontend
```bash
npm run dev
```

## Configuração do ambiente
- Banco SQLite já configurado para testes rápidos
- Para e-mail, use Mailtrap ou SMTP real (ajuste no `.env`)
- Variáveis importantes: `DB_CONNECTION`, `MAIL_MAILER`, `MAIL_HOST`, etc.

## Testes
- Testes unitários e de feature no backend:
```bash
php artisan test
```
- (Opcional) Testes no frontend: `npm run test`

## Documentação das APIs
- Todas as rotas estão protegidas por autenticação Sanctum
- Veja exemplos de uso e payloads em `docs/viagem.md` para a funcionalidade de viagem
- Principais endpoints:
	- `POST /orders` — Cria pedido
	- `GET /orders` — Lista pedidos (filtros: status, destino, datas)
	- `PATCH /orders/{id}/status` — Atualiza status (admin)
	- `POST /orders/{orderId}/travel` — Cria viagem
	- `PUT /orders/{orderId}/travel` — Atualiza viagem

## Funcionalidade extra: Viagem (Travel)
Veja detalhes completos em [`docs/viagem.md`](docs/viagem.md).
- Permite agendar retirada/entrega após aprovação do pedido
- Notifica usuário para agendar viagem
- Marca envio próprio após 24h sem agendamento

## Tecnologias e boas práticas
- **Backend:** Laravel 10+, PHP 8+, Sanctum, Spatie Activity Log, Notificações
- **Frontend:** Vue 3, TypeScript, Breeze, Tailwind
- **Logs:** Todas as ações relevantes são auditadas
- **Validação:** Tipagem forte no backend e frontend
- **Docker:** Facilita setup e execução

## Logs e Auditoria
- Todas as ações de criação, atualização, deleção e status são logadas (Spatie Activity Log)
- Logs podem ser consultados para auditoria e rastreabilidade

## Contato
Dúvidas ou sugestões? Abra uma issue ou envie e-mail para o responsável pelo repositório.

---

> Projeto desenvolvido para o desafio Onfly. Qualquer funcionalidade extra está documentada neste README e em `docs/viagem.md`.
