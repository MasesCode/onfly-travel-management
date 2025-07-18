#!/bin/bash

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 Iniciando setup do projeto Onfly Travel Management${NC}"

# Verifica se o Docker está rodando
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Docker não está rodando. Por favor, inicie o Docker primeiro.${NC}"
    exit 1
fi

# Builda e sobe os containers
echo -e "${YELLOW}🏗️  Construindo containers...${NC}"
docker-compose up -d --build

# Aguarda o banco de dados ficar pronto
echo -e "${YELLOW}⏳ Aguardando banco de dados ficar pronto...${NC}"
sleep 30

# Executa as migrations
echo -e "${YELLOW}🗄️  Executando migrations...${NC}"
docker-compose exec backend php artisan migrate --force

# Executa os seeders
echo -e "${YELLOW}🌱 Executando seeders...${NC}"
docker-compose exec backend php artisan db:seed --force

# Limpa o cache
echo -e "${YELLOW}🧹 Limpando cache...${NC}"
docker-compose exec backend php artisan config:clear
docker-compose exec backend php artisan cache:clear
docker-compose exec backend php artisan view:clear

echo -e "${GREEN}✅ Setup concluído com sucesso!${NC}"
echo -e "${GREEN}🌐 Frontend: http://localhost:3000${NC}"
echo -e "${GREEN}🔧 Backend: http://localhost:8000${NC}"
echo -e "${GREEN}🗄️  Banco de dados: localhost:3306${NC}"
echo ""
echo -e "${YELLOW}Para parar os containers: docker-compose down${NC}"
echo -e "${YELLOW}Para ver os logs: docker-compose logs -f${NC}"
