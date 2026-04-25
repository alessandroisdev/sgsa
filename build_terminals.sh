#!/bin/bash

echo "======================================"
echo "    SGSA Terminals Build Script"
echo "======================================"
echo "Selecione o formato de build desejado para os terminais (Service-Counter, Totem, TV):"
echo "1) Portable (Diretório descompactado via build:unpack)"
echo "2) Windows (Instalador via build:win)"
echo "3) Linux (AppImage, deb, snap via build:linux)"
echo "======================================"
read -p "Opção (1/2/3): " OPTION

case $OPTION in
    1)
        TARGET="build:unpack"
        ;;
    2)
        TARGET="build:win"
        ;;
    3)
        TARGET="build:linux"
        ;;
    *)
        echo "Opção inválida. Abortando."
        exit 1
        ;;
esac

echo ""
echo "Iniciando processo de build com o target: $TARGET"
echo "---------------------------------------------------"

APPS=("service-counter" "totem" "tv")

for APP in "${APPS[@]}"; do
    echo ">> [${APP}] Iniciando processo..."
    if [ -d "$APP" ]; then
        cd "$APP" || exit
        
        # Verifica e instala as dependências se necessário
        if [ ! -d "node_modules" ]; then
            echo ">> [${APP}] Instalando dependências (npm install)..."
            npm install
        fi

        echo ">> [${APP}] Executando: npm run ${TARGET}..."
        npm run "$TARGET"
        
        cd .. || exit
        echo ">> [${APP}] Build finalizado com sucesso!"
        echo "---------------------------------------------------"
    else
        echo ">> [${APP}] AVISO: Diretório não encontrado! Pulando..."
    fi
done

echo "Todos os builds selecionados foram concluídos!"
