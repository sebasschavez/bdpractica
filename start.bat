@echo off
chcp 65001 >nul
color 0A

echo.
echo ============================================
echo    🛒 Tienda Don Manolo - Inicio Rápido
echo ============================================
echo.

REM Verificar Docker
docker --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Docker no está instalado o no está en el PATH
    echo Por favor instala Docker Desktop desde:
    echo https://www.docker.com/products/docker-desktop
    pause
    exit /b 1
)

echo ✓ Docker encontrado
echo.

REM Verificar docker-compose
docker-compose --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Docker Compose no está disponible
    pause
    exit /b 1
)

echo ✓ Docker Compose encontrado
echo.

REM Detener contenedores previos
echo 🛑 Deteniendo contenedores previos...
docker-compose down >nul 2>&1

REM Iniciar contenedores
echo 🔨 Iniciando contenedores...
docker-compose up -d --build

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ❌ Error al iniciar los contenedores
    echo Verifica los logs con: docker-compose logs
    pause
    exit /b 1
)

echo.
echo ⏳ Esperando a que los servicios estén listos...
timeout /t 15 /nobreak >nul

REM Verificar estado
echo.
echo 📊 Estado de contenedores:
docker-compose ps

echo.
echo ============================================
echo    ✅ ¡Instalación Completada!
echo ============================================
echo.
echo 📍 Accesos:
echo    🌐 Aplicación:  http://localhost:8080/login.php
echo    🗄️  PhpMyAdmin:  http://localhost:8081
echo.
echo 🔑 Credenciales por defecto:
echo    Usuario:   donmanolo
echo    Password:  admin123
echo.
echo 📝 Comandos útiles:
echo    Ver logs:        docker-compose logs -f
echo    Detener:         docker-compose down
echo    Reiniciar:       docker-compose restart
echo.
echo Presiona cualquier tecla para abrir la aplicación...
pause >nul

REM Abrir navegador
start http://localhost:8080/login.php

exit /b 0