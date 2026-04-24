@echo off
echo ==========================================
echo Inicializando Terminais Eletronicos SGSA
echo ==========================================

echo Iniciando Painel da TV...
start cmd /c "cd tv && npm run dev"

echo Iniciando Painel do Totem...
start cmd /c "cd totem && npm run dev"

echo Iniciando Painel do Guiche...
start cmd /c "cd service-counter && npm run dev"

echo ==========================================
echo Os terminais serao abertos em instantes!
echo O backend Docker ja deve estar rodando.
echo ==========================================
pause
