#!/bin/bash

# 1. Definir o usuário do servidor web (comum: www-data, apache ou nginx)
# Tenta detectar automaticamente, se falhar, usa www-data
WEB_USER=$(ps aux | grep -E '[a]pache|[n]ginx|[h]ttpd' | grep -v root | head -1 | cut -d\  -f1)
WEB_USER=${WEB_USER:-www-data}

echo "--- Iniciando reparo de permissões e cache (Usuário: $WEB_USER) ---"

# 2. Criar diretórios de sistema se não existirem
echo "Verificando diretórios de storage..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# 3. Ajustar Permissões (Donos e Grupos)
echo "Ajustando propriedade dos arquivos..."
chown -R $USER:$WEB_USER .
chown -R $WEB_USER:$(id -gn $USER) storage bootstrap/cache

# 4. Ajustar Permissões de Escrita
echo "Definindo permissões 775..."
find storage -type d -exec chmod 775 {} +
find bootstrap/cache -type d -exec chmod 775 {} +
find storage -type f -exec chmod 664 {} +
find bootstrap/cache -type f -exec chmod 664 {} +

# 5. Limpeza de Cache do Laravel
echo "Limpando caches do Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "--- Reparo concluído com sucesso! ---"