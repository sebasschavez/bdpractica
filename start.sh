echo "🛒 Configurando Tienda Don Manolo..."
echo ""

GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' 

# Verificar Docker
if ! command -v docker &> /dev/null; then
    echo -e "${RED}❌ Docker no está instalado${NC}"
    echo "Por favor instala Docker desde: https://www.docker.com/get-started"
    exit 1
fi

echo -e "${GREEN}✓ Docker encontrado${NC}"

# Verificar docker-compose
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}❌ Docker Compose no está instalado${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Docker Compose encontrado${NC}"
echo ""

# Crear directorios
mkdir -p logs
mkdir -p backups

# Detener contenedores existentes
echo "🛑 Deteniendo contenedores existentes..."
docker-compose down 2>/dev/null

# Construir e iniciar contenedores
echo "🔨 Construyendo contenedores..."
docker-compose up -d --build

# Esperar a que MySQL esté listo
echo "⏳ Esperando a que MySQL esté listo..."
sleep 15

# Verificar estado
echo ""
echo "📊 Estado de contenedores:"
docker-compose ps

echo ""
echo -e "${GREEN}✅ ¡Instalación completada!${NC}"
echo ""
echo -e "${BLUE}📍 Accesos:${NC}"
echo "   🌐 Aplicación: http://localhost:8080/login.php"
echo "   🗄️  PhpMyAdmin: http://localhost:8081"
echo ""
echo -e "${BLUE}🔑 Credenciales:${NC}"
echo "   Usuario: donmanolo"
echo "   Password: admin123"
echo ""

