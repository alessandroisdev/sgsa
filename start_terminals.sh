#!/bin/bash

echo "=========================================="
echo "Inicializando Terminais Eletronicos SGSA"
echo "=========================================="

echo "Iniciando Painel da TV..."
cd tv && npm run dev &

echo "Iniciando Painel do Totem..."
cd totem && npm run dev &

echo "Iniciando Painel do Guiche..."
cd service-counter && npm run dev &

echo "=========================================="
echo "Os terminais serao abertos em instantes!"
echo "Para encerrar todos os paineis pressione CTRL+C"
echo "=========================================="

# Wait for all background processes
wait
