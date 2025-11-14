#!/bin/bash

echo "🔄 Actualizando Tienda Don Manolo..."
echo ""

# Colores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}⚠️  Este script actualizará:${NC}"
echo "   1. Dockerfile (para soporte UTF-8)"
echo "   2. login.php (sin avisos)"
echo "   3. reportes.php (completo y funcional)"
echo ""
echo "¿Deseas continuar? (s/n)"
read -r respuesta

if [ "$respuesta" != "s" ]; then
    echo "Actualización cancelada"
    exit 0
fi

echo ""
echo "📋 Paso 1: Creando respaldos..."
cp Dockerfile Dockerfile.backup 2>/dev/null
cp login.php login.php.backup 2>/dev/null
cp reportes.php reportes.php.backup 2>/dev/null
echo -e "${GREEN}✓ Respaldos creados${NC}"

echo ""
echo "📋 Paso 2: Actualizando archivos..."
echo "   Por favor, actualiza manualmente los siguientes archivos:"
echo "   - Dockerfile (copia el contenido del artifact actualizado)"
echo "   - login.php (copia el contenido del artifact actualizado)"
echo "   - reportes.php (copia el contenido del artifact actualizado)"
echo ""

echo "📋 Paso 3: Reconstruyendo contenedores..."
echo -e "${BLUE}   Deteniendo contenedores...${NC}"
docker-compose down

echo -e "${BLUE}   Reconstruyendo con soporte UTF-8...${NC}"
docker-compose build --no-cache

echo -e "${BLUE}   Iniciando contenedores...${NC}"
docker-compose up -d

echo ""
echo "⏳ Esperando a que los servicios estén listos..."
sleep 20

echo ""
echo -e "${GREEN}✅ Actualización completada!${NC}"
echo ""
echo "📍 Verifica los cambios en:"
echo "   http://localhost:8080/login.php"
echo "   http://localhost:8080/reportes.php"
echo ""
echo "💡 Si los acentos aún no se ven bien:"
echo "   1. Asegúrate que tus archivos PHP estén guardados con codificación UTF-8"
echo "   2. Verifica con: file -i archivo.php"
echo "   3. Convierte si es necesario: iconv -f ISO-8859-1 -t UTF-8 archivo.php > archivo_utf8.php"
echo ""
