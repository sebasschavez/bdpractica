#!/bin/bash

echo "🔧 Corrigiendo codificación UTF-8 en archivos PHP..."
echo ""

# Colores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Verificar que existan los comandos necesarios
if ! command -v iconv &> /dev/null; then
    echo -e "${RED}❌ El comando 'iconv' no está disponible${NC}"
    exit 1
fi

# Crear directorio de respaldo
mkdir -p backup_original
echo -e "${YELLOW}📁 Creando respaldos en ./backup_original/${NC}"

# Lista de archivos PHP a convertir
archivos=(
    "config.php"
    "login.php"
    "dashboard.php"
    "productos.php"
    "ventas.php"
    "proveedores.php"
    "reportes.php"
)

# Convertir cada archivo
for archivo in "${archivos[@]}"; do
    if [ -f "$archivo" ]; then
        echo -n "   Procesando $archivo... "
        
        # Hacer respaldo
        cp "$archivo" "backup_original/${archivo}.bak"
        
        # Detectar codificación actual
        encoding=$(file -b --mime-encoding "$archivo")
        echo -n "($encoding → UTF-8) "
        
        # Convertir a UTF-8
        if [ "$encoding" != "utf-8" ] && [ "$encoding" != "us-ascii" ]; then
            iconv -f "$encoding" -t UTF-8 "$archivo" > "${archivo}.tmp" 2>/dev/null
            if [ $? -eq 0 ]; then
                mv "${archivo}.tmp" "$archivo"
                echo -e "${GREEN}✓${NC}"
            else
                # Si falla, intentar con ISO-8859-1
                iconv -f ISO-8859-1 -t UTF-8 "$archivo" > "${archivo}.tmp" 2>/dev/null
                if [ $? -eq 0 ]; then
                    mv "${archivo}.tmp" "$archivo"
                    echo -e "${GREEN}✓${NC}"
                else
                    rm -f "${archivo}.tmp"
                    echo -e "${RED}✗ (error)${NC}"
                fi
            fi
        else
            echo -e "${GREEN}✓ (ya es UTF-8)${NC}"
        fi
    else
        echo -e "${YELLOW}   ⚠ $archivo no encontrado${NC}"
    fi
done

echo ""
echo -e "${GREEN}✅ Conversión completada${NC}"
echo ""
echo "📋 Siguiente paso: Reconstruir contenedores"
echo "   docker-compose down"
echo "   docker-compose build --no-cache"
echo "   docker-compose up -d"
echo ""
