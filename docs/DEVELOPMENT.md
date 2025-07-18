# 🚀 Guia de Desenvolvimento

## Configuração do Ambiente

### 1. Com Docker (Recomendado)

```bash
# Clone o repositório
git clone https://github.com/MasesCode/onfly-travel-management.git
cd onfly-travel-management

# Execute o setup
./setup.sh

# Ou use o Makefile
make setup
```

### 2. Sem Docker

```bash
# Setup local
./setup-local.sh

# Configure o banco de dados no .env
# Execute migrations
cd onfly-api && php artisan migrate
cd onfly-api && php artisan db:seed
```

## Fluxo de Desenvolvimento

### 1. Desenvolvimento do Backend

```bash
# Acessar container
docker-compose exec backend bash

# Ou localmente
cd onfly-api

# Comandos úteis
php artisan make:model NomeModel
php artisan make:controller NomeController
php artisan make:migration create_nome_table
php artisan migrate
php artisan db:seed
```

### 2. Desenvolvimento do Frontend

```bash
# Acessar container
docker-compose exec frontend sh

# Ou localmente
cd onfly-front

# Comandos úteis
npm run dev
npm run build
npm run lint
npm run type-check
```

### 3. Testes

```bash
# Com Docker
./run-tests.sh

# Ou usar Make
make test

# Localmente
cd onfly-api && php artisan test
```

## Estrutura de Branches

```
main              # Produção
├── develop       # Desenvolvimento
├── feature/*     # Novas funcionalidades
├── hotfix/*      # Correções urgentes
└── release/*     # Preparação para release
```

## Convenções de Código

### Backend (Laravel)

- PSR-12 para formatação
- Nomes de classes em PascalCase
- Nomes de métodos em camelCase
- Nomes de variáveis em snake_case
- Migrations descritivas

### Frontend (Vue.js)

- Composables em camelCase
- Componentes em PascalCase
- Props tipadas com TypeScript
- Stores organizadas por domínio

## Comandos Úteis

### Makefile

```bash
make help         # Ver todos os comandos
make setup        # Setup completo
make up           # Subir containers
make down         # Parar containers
make test         # Executar testes
make logs         # Ver logs
make clean        # Limpar tudo
```

### Docker Compose

```bash
docker-compose up -d            # Subir em background
docker-compose down             # Parar containers
docker-compose logs -f          # Ver logs
docker-compose exec backend bash # Acessar backend
docker-compose exec frontend sh  # Acessar frontend
```

### Laravel Artisan

```bash
php artisan migrate              # Executar migrations
php artisan db:seed             # Executar seeders
php artisan make:model Model    # Criar model
php artisan make:controller Ctrl # Criar controller
php artisan test                # Executar testes
php artisan cache:clear         # Limpar cache
```

## Debugging

### Backend

```bash
# Logs do Laravel
tail -f storage/logs/laravel.log

# Debug com Tinker
php artisan tinker
```

### Frontend

```bash
# Console do navegador
# Vue DevTools
# Network tab para APIs
```

## Performance

### Backend

- Use eager loading para relacionamentos
- Implemente cache quando necessário
- Otimize queries N+1
- Use jobs para tarefas pesadas

### Frontend

- Use lazy loading para componentes
- Implemente paginação
- Otimize imagens
- Use composables para lógica reutilizável

## Deployment

### Staging

```bash
# Build para staging
docker-compose -f docker-compose.staging.yml up -d
```

### Production

```bash
# Build para produção
docker-compose -f docker-compose.prod.yml up -d
```
