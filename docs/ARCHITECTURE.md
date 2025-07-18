# 🏗️ Arquitetura do Projeto Onfly

## Visão Geral

O projeto Onfly Travel Management foi desenvolvido seguindo uma arquitetura de microserviços com:

- **Backend**: API REST em Laravel
- **Frontend**: SPA em Vue.js
- **Banco de Dados**: MySQL
- **Containerização**: Docker

## Estrutura do Backend (Laravel)

### Camadas da Aplicação

```
app/
├── Http/
│   ├── Controllers/     # Controladores da API
│   ├── Middleware/      # Middlewares customizados
│   └── Requests/        # Validações de entrada
├── Models/              # Modelos Eloquent
├── Notifications/       # Notificações do sistema
├── Observers/           # Observadores de eventos
└── Providers/           # Provedores de serviços
```

### Principais Componentes

#### Models
- **User**: Gerenciamento de usuários
- **Travel**: Viagens corporativas
- **Order**: Pedidos/Solicitações
- **OrderStatus**: Status dos pedidos
- **Notification**: Notificações

#### Controllers
- API RESTful seguindo padrões REST
- Validação de entrada via Form Requests
- Autorização via middleware

#### Observers
- **OrderObserver**: Monitora mudanças nos pedidos
- Dispara notificações automaticamente

## Estrutura do Frontend (Vue.js)

### Organização dos Componentes

```
src/
├── components/          # Componentes reutilizáveis
├── views/              # Páginas da aplicação
├── stores/             # Gerenciamento de estado (Pinia)
├── services/           # Serviços de API
├── types/              # Tipos TypeScript
└── router/             # Configuração de rotas
```

### Tecnologias Utilizadas

- **Vue 3**: Framework principal
- **TypeScript**: Tipagem estática
- **Pinia**: Gerenciamento de estado
- **Tailwind CSS**: Estilização
- **Axios**: Comunicação HTTP

## Banco de Dados

### Estrutura Principal

```sql
-- Usuários
users
├── id
├── name
├── email
├── password
└── timestamps

-- Viagens
travels
├── id
├── user_id
├── destination
├── start_date
├── end_date
├── status
└── timestamps

-- Pedidos
orders
├── id
├── user_id
├── travel_id
├── status_id
├── amount
└── timestamps

-- Status dos Pedidos
order_statuses
├── id
├── name
├── description
└── timestamps
```

### Relacionamentos

- User `hasMany` Travel
- User `hasMany` Order
- Travel `belongsTo` User
- Order `belongsTo` User
- Order `belongsTo` Travel
- Order `belongsTo` OrderStatus

## Fluxo de Dados

### Autenticação
1. Login via API `/api/login`
2. Recebimento de token Sanctum
3. Armazenamento no localStorage
4. Envio em headers das requisições

### Operações CRUD
1. Frontend faz requisição HTTP
2. Backend valida dados
3. Observer dispara eventos
4. Notificações são criadas
5. Resposta é enviada ao frontend

## Configuração de Desenvolvimento

### Variáveis de Ambiente

#### Backend (.env)
```env
APP_NAME="Onfly Travel Management"
APP_ENV=local
DB_CONNECTION=mysql
DB_HOST=db
DB_DATABASE=onfly
```

#### Frontend (.env)
```env
VITE_API_URL=http://localhost:8000
```

## Testes

### Estrutura de Testes

```
tests/
├── Feature/             # Testes de integração
├── Unit/               # Testes unitários
└── TestCase.php        # Classe base
```

### Tipos de Testes

- **Unit**: Testes de componentes isolados
- **Feature**: Testes de funcionalidades completas
- **Integration**: Testes de integração com banco

## Deployment

### Ambientes

1. **Development**: `docker-compose.yml`
2. **Production**: `docker-compose.prod.yml`

### CI/CD

O projeto está preparado para:
- Testes automatizados
- Build automático
- Deploy via Docker

## Monitoramento

### Logs

- **Laravel**: `storage/logs/laravel.log`
- **Docker**: `docker-compose logs`

### Métricas

- Activity Log para auditoria
- Logs de erros e exceções
- Monitoramento de performance

## Segurança

### Medidas Implementadas

- Autenticação via Sanctum
- Validação de entrada
- Proteção CSRF
- Sanitização de dados
- Headers de segurança

### Boas Práticas

- Senhas hasheadas
- Tokens com expiração
- Validação server-side
- Princípio do menor privilégio
