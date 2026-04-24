# SGSA - Sistema de Gestão de Senhas e Atendimento

Bem-vindo ao SGSA, um ecossistema *Omnichannel* corporativo de gerenciamento de filas de atendimento. 
Este projeto substitui painéis monolíticos antigos por uma arquitetura leve, moderna e reativa, dividida em um núcleo administrativo (API) e terminais clientes independentes.

---

## 🏗️ Arquitetura do Sistema

O ecossistema é formado por 4 componentes principais:

1.  **Backend & Admin (`./www`)**:
    *   Construído em **Laravel 13** e PHP 8.4.
    *   Painel administrativo customizado (Vite + TypeScript + SCSS + Bootstrap 5).
    *   **Motor de Filas Ponderado (WFQ)**: Resolve empates e gerencia prioridades com Locks Atômicos no Redis para evitar concorrência.
    *   **SSE (Server-Sent Events)**: Transmite eventos em tempo real para os terminais com *zero* overhead usando Pub/Sub do Redis.
2.  **Terminal Totem (`./totem`)**:
    *   Aplicativo **Electron + React** para telas *Touchscreen*.
    *   Fluxo de autoatendimento à prova de idiotas: Escolha Prioridade > Escolha Serviço > Receba a senha impressa.
    *   Reset automático por inatividade (Kiosk Mode).
3.  **Terminal TV (`./tv`)**:
    *   Aplicativo **Electron + React** projetado para as salas de espera.
    *   Design de alto contraste com Web Audio API nativa para o toque (sino) e animações CSS (Flash).
    *   Ouve passivamente a fila de eventos SSE (`/api/v1/tv/stream`).
4.  **Terminal do Guichê (`./service-counter`)**:
    *   Aplicativo **Electron + React** para os colaboradores.
    *   Requer Autenticação (Login).
    *   Permite selecionar o guichê físico e controlar todo o fluxo do paciente (Chamar Próximo, Rechamar, Iniciar, Finalizar, Marcar Ausência).

---

## 🔒 Segurança Dupla

Adotamos duas estratégias diferentes de segurança para os endpoints da API (`/api/v1`):
*   **DeviceAuthMiddleware:** Autenticação via Header `X-Device-ID` para Terminais Passivos (TV e Totem). O sistema valida se aquele equipamento específico tem permissão para ler ou gravar dados daquela Unidade Hospitalar sem precisar que alguém faça login manual nele toda manhã.
*   **Sanctum (Bearer Token):** Autenticação clássica para o Guichê do Atendente. Exige credenciais reais (E-mail/Senha) para gerar logs de auditoria de *quem* chamou *qual* senha.

---

## 🚀 Como Iniciar o Projeto (Ambiente Local)

### Passo 1: Iniciar o Backend Docker
O projeto requer o Docker Desktop rodando.
```bash
docker compose -f .docker/docker-compose.yml up -d
```
*Isso vai baixar o PHP 8.4, MariaDB, Nginx e Redis e subir os containers.*

### Passo 2: Inicializar o Laravel e Popular o Banco de Dados
Ainda na raiz do projeto, instale as dependências e rode o Seeder para gerar um ambiente "Plug and Play".
```bash
docker compose -f .docker/docker-compose.yml exec app composer install
docker compose -f .docker/docker-compose.yml exec app php artisan migrate --seed
```

### Passo 3: Inicializar os Terminais Eletrônicos
Como os terminais não dependem do Docker (são instalados localmente nas máquinas), nós criamos um atalho mágico para você testá-los em modo Dev simultaneamente:
**No Windows:** Dê um duplo-clique no arquivo `start_terminals.bat` localizado na raiz do projeto.
*   Ele fará o build paralelo da TV, Totem e Guichê, abrindo as 3 janelas na sua tela.

### Credenciais de Teste (Pós-Seeder)
Para logar no Guichê (Service-Counter) utilize:
*   **E-mail:** `admin@sgsa.com`
*   **Senha:** `password`

---

## 📚 Documentação da API (Swagger / OpenAPI)

A documentação viva de todos os endpoints REST está disponível no endereço:
👉 **[http://localhost:8084/docs/api](http://localhost:8084/docs/api)**

Esta página é autogerada pelo `dedoc/scramble` e sempre refletirá as rotas e regras de validação atuais do Laravel. Use-a para testar os endpoints isoladamente ou consultar os schemas de retorno.
