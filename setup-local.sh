#!/bin/bash

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 Setup local do Onfly Travel Management${NC}"

# Verifica se o PHP está instalado
if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP não está instalado. Por favor, instale o PHP 8.2 ou superior.${NC}"
    exit 1
fi

# Verifica se o Composer está instalado
if ! command -v composer &> /dev/null; then
    echo -e "${RED}❌ Composer não está instalado. Por favor, instale o Composer.${NC}"
    exit 1
fi

# Verifica se o Node.js está instalado
if ! command -v node &> /dev/null; then
    echo -e "${RED}❌ Node.js não está instalado. Por favor, instale o Node.js 18 ou superior.${NC}"
    exit 1
fi

# Setup do Backend
echo -e "${YELLOW}🔧 Configurando backend...${NC}"
cd onfly-api

if [ ! -f ".env" ]; then
    cp .env.example .env
    echo -e "${YELLOW}📋 Arquivo .env criado. Configure suas credenciais do banco de dados.${NC}"
fi

composer install
npm install

php artisan key:generate
php artisan storage:link

echo -e "${YELLOW}🗄️  Execute as migrations e seeders:${NC}"
echo "php artisan migrate"
echo "php artisan db:seed"

cd ..

# Setup do Frontend
echo -e "${YELLOW}🎨 Configurando frontend...${NC}"
cd onfly-front

npm install

if [ ! -f ".env" ]; then
    echo "VITE_API_URL=http://localhost:8000" > .env
    echo -e "${YELLOW}📋 Arquivo .env do frontend criado.${NC}"
fi

cd ..

echo -e "${GREEN}✅ Setup local concluído!${NC}"
echo ""
echo -e "${YELLOW}Para iniciar o backend:${NC}"
echo "cd onfly-api && php artisan serve"
echo ""
echo -e "${YELLOW}Para iniciar o frontend:${NC}"
echo "cd onfly-front && npm run dev"
