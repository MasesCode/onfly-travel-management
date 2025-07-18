#!/bin/bash

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}🧪 Executando testes do Onfly Travel Management${NC}"

# Verifica se o Docker está rodando
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Docker não está rodando. Por favor, inicie o Docker primeiro.${NC}"
    exit 1
fi

# Verifica se os containers estão rodando
if [ ! "$(docker-compose ps -q backend)" ]; then
    echo -e "${RED}❌ Containers não estão rodando. Execute ./setup.sh primeiro.${NC}"
    exit 1
fi

echo -e "${YELLOW}🔧 Preparando ambiente de teste...${NC}"

# Executa os testes
echo -e "${YELLOW}🧪 Executando testes unitários...${NC}"
docker-compose exec backend php artisan test --testsuite=Unit

echo -e "${YELLOW}🧪 Executando testes de feature...${NC}"
docker-compose exec backend php artisan test --testsuite=Feature

echo -e "${GREEN}✅ Testes concluídos!${NC}"
