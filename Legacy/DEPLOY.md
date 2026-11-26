# Nosso Hub — Deploy no Homelab (Docker)

## Pré-requisitos

- Docker Engine + Docker Compose plugin
- Pasta do projeto (ex.: `~/docker/NossoHub`)
- Arquivo `.env` baseado no `.env.example`

## Subida limpa (recomendado se algo quebrou)

```bash
cd ~/docker/NossoHub

# 1) Parar tudo e limpar containers órfãos
docker compose down --remove-orphans

# 2) Garantir .env
cp -n .env.example .env || true
# Edite IMMICH_* e senhas DB se quiser

# 3) Limpar cache de build antigo (só se o build PHP continuar falhando)
# docker builder prune -f

# 4) Build da imagem PHP (progresso detalhado)
docker compose build --no-cache --progress=plain php

# 5) Sobe MariaDB + PHP + Nginx
docker compose up -d

# 6) Assets front-end (Tailwind/Vite) — uma vez
docker compose --profile assets run --rm node

# 7) Status
docker compose ps
docker compose logs php --tail=80
docker compose logs nginx --tail=40
docker compose logs mariadb --tail=40
```

App: **http://SEU_IP:2807**

## Variáveis de banco (.env)

```env
DB_CONNECTION=mysql
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=nosso_hub
DB_USERNAME=nosso_hub
DB_PASSWORD=secret
DB_ROOT_PASSWORD=rootsecret
```

`DB_HOST` deve ser **`mariadb`** (nome do serviço), nunca `localhost` dentro do container PHP.

## Redes

- `nosso_hub_net` — rede interna do app (nginx ↔ php ↔ mariadb)
- `homelab` — rede externa do homelab (PHP acessa o Immich). Precisa existir antes:

```bash
docker network create homelab   # se ainda não existir
```

| Serviço  | Porta no host |
|----------|---------------|
| App HTTP | 2807          |
| MariaDB  | 3307          |

## Comandos úteis

```bash
# Logs ao vivo
docker compose logs -f php

# Rodar migrate manualmente
docker compose exec php php artisan migrate --force

# Rebuild só do PHP após mudar o Dockerfile
docker compose build --no-cache php && docker compose up -d php nginx

# Reset TOTAL do banco (APAGA DADOS)
docker compose down -v
docker compose up -d
```

## O que a imagem PHP inclui

Extensões: `pdo_mysql`, `mbstring`, `bcmath`, `zip`, `intl`, `opcache`.  
Sem Redis/GD (não usados pelo app — deixam o build mais estável em homelab).
