# 🚀 Onfly Travel Management

Sistema de gerenciamento de viagens corporativas desenvolvido com Laravel (Backend) e Vue.js (Frontend).

## 📋 Pré-requisitos

### Para execução com Docker (Recomendado):
- Docker
- Docker Compose

### Para execução sem Docker:
- PHP 8.2 ou superior
- Composer
- Node.js 18 ou superior
- NPM
- MySQL 8.0 ou superior

## 🐳 Instalação e Execução com Docker

### 1. Clone o repositório
```bash
git clone https://github.com/MasesCode/onfly-travel-management.git
cd onfly-travel-management
```

### 2. Execute o script de setup automatizado
```bash
# Opção 1: Script bash direto
./setup.sh

# Opção 2: Usando Makefile (mais prático)
make setup
```

Este script irá:
- Configurar o ambiente automaticamente
- Construir e iniciar todos os containers
- Executar as migrations do banco de dados
- Executar os seeders para popular o banco
- Configurar as chaves da aplicação

### 3. Acesse o sistema
- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000
- **Banco de dados**: localhost:3306

### Comandos úteis do Docker

```bash
# Parar todos os containers
docker-compose down

# Ver logs em tempo real
docker-compose logs -f

# Acessar o container do backend
docker-compose exec backend bash

# Acessar o container do frontend
docker-compose exec frontend sh

# Executar comandos Artisan
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan db:seed
docker-compose exec backend php artisan cache:clear
```

### Usando o Makefile (Recomendado)

Para facilitar o uso, você pode usar os comandos do Makefile (mais prático que os comandos Docker):

```bash
# Ver todos os comandos disponíveis
make help

# Setup completo
make setup

# Subir containers
make up

# Parar containers
make down

# Ver logs
make logs

# Executar testes
make test

# Limpar cache
make cache-clear
```

## 🔧 Instalação e Execução sem Docker

### Backend (Laravel)

1. **Navegue para o diretório do backend**
```bash
cd onfly-api
```

2. **Instale as dependências do PHP**
```bash
composer install
```

3. **Instale as dependências do Node.js**
```bash
npm install
```

4. **Configure o ambiente**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure o banco de dados**
Edite o arquivo `.env` com suas configurações do MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=onfly
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

6. **Execute as migrations e seeders**
```bash
php artisan migrate
php artisan db:seed
```

7. **Inicie o servidor**
```bash
php artisan serve
```

O backend estará disponível em: http://localhost:8000

### Frontend (Vue.js)

1. **Navegue para o diretório do frontend**
```bash
cd onfly-front
```

2. **Instale as dependências**
```bash
npm install
```

3. **Configure o ambiente**
Crie um arquivo `.env` na raiz do projeto frontend:
```env
VITE_API_URL=http://localhost:8000
```

4. **Inicie o servidor de desenvolvimento**
```bash
npm run dev
```

O frontend estará disponível em: http://localhost:3000

## 🗄️ Banco de Dados

### Estrutura Principal
- **users**: Usuários do sistema
- **travels**: Viagens
- **orders**: Pedidos/Solicitações
- **order_status**: Status dos pedidos
- **notifications**: Notificações

### Seeders
O projeto inclui seeders para popular o banco com dados de exemplo. Execute:
```bash
php artisan db:seed
```

## 🧪 Testes

O projeto inclui testes unitários e de integração desenvolvidos com PHPUnit.

### Executar testes com Docker
```bash
docker-compose exec backend php artisan test
```

### Executar testes sem Docker
```bash
cd onfly-api
php artisan test
```

### Estrutura de Testes
- **tests/Feature/**: Testes de funcionalidades e integração
- **tests/Unit/**: Testes unitários
- **tests/TestCase.php**: Classe base para testes

## 🔑 Funcionalidades Principais

- **Autenticação e Autorização**: Sistema de login com Sanctum
- **Gerenciamento de Usuários**: CRUD completo de usuários
- **Sistema de Pedidos**: Fluxo completo de solicitações
- **Notificações**: Sistema de notificações para usuários
- **Logs de Atividade**: Rastreamento de ações com Spatie Activity Log

## 📁 Estrutura do Projeto

```
onfly-travel-management/
├── onfly-api/              # Backend Laravel
│   ├── app/
│   │   ├── Http/Controllers/
│   │   ├── Models/
│   │   ├── Notifications/
│   │   └── Observers/
│   ├── database/
│   │   ├── migrations/
│   │   ├── seeders/
│   │   └── factories/
│   ├── tests/
│   └── routes/
├── onfly-front/            # Frontend Vue.js
│   ├── src/
│   │   ├── components/
│   │   ├── views/
│   │   ├── stores/
│   │   └── services/
│   └── public/
├── docker-compose.yml      # Configuração Docker
└── setup.sh               # Script de setup automatizado
```

## 🛠️ Tecnologias Utilizadas

### Backend
- **Laravel 12**: Framework PHP
- **PHP 8.2**: Linguagem de programação
- **MySQL 8.0**: Banco de dados
- **Sanctum**: Autenticação API
- **Spatie Activity Log**: Logs de atividade

### Frontend
- **Vue.js 3**: Framework JavaScript
- **TypeScript**: Tipagem estática
- **Tailwind CSS**: Framework CSS
- **Vite**: Build tool
- **Pinia**: Gerenciamento de estado
- **Node Version: 20**: Linguagem de programação (utlizar esta para melhor aceitaçao e garantia da funcionalidade perfeita das dependencias)

### DevOps
- **Docker**: Containerização
- **Docker Compose**: Orquestração de containers

## 📝 Scripts Disponíveis

### Backend
```bash
composer run dev      # Ambiente de desenvolvimento completo
composer run test     # Executar testes
php artisan serve     # Servidor de desenvolvimento
php artisan migrate   # Executar migrations
php artisan db:seed   # Executar seeders
```

### Frontend
```bash
npm run dev           # Servidor de desenvolvimento
npm run build         # Build para produção
npm run preview       # Preview do build
npm run lint          # Linter
npm run type-check    # Verificação de tipos
```

## 🎯 Resumo de Comandos Principais

### Setup Inicial (escolha uma das opções)
```bash
# Opção 1: Makefile (mais prático)
make setup

# Opção 2: Script bash direto
./setup.sh
```

### Comandos do Dia a Dia
```bash
# Subir/parar containers
make up / make down

# Ver logs
make logs

# Executar testes
make test

# Limpar cache
make cache-clear

# Ver todos os comandos disponíveis
make help
```

## 🤝 Contribuindo

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -m 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

## 💡 Suporte

Para dúvidas ou problemas, abra uma issue no GitHub ou entre em contato.

---

**Desenvolvido com ❤️ por Miguel: [MasesCode](https://github.com/MasesCode) [Miguelpessoal](https://github.com/Miguelpessoal)**
