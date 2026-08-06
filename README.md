# Nosso Hub

Hub pessoal para registrar a história de um relacionamento.

Mídia fica no **Immich**. O Laravel guarda apenas metadados e IDs de assets.

## Stack

- Laravel 12 + PHP 8.4
- Livewire 3 + Tailwind CSS + Alpine.js (via Livewire)
- MariaDB
- Docker Compose (nginx, php-fpm, mariadb, node)
- Immich API (instância externa)

## Homelab (Docker)

Guia completo: **[DEPLOY.md](DEPLOY.md)**

```bash
cp .env.example .env
# Ajuste APP_URL, DB_* e Immich (IMMICH_BASE_URL / IMMICH_API_KEY)

docker compose down --remove-orphans
docker compose build --no-cache --progress=plain php
docker compose up -d
docker compose --profile assets run --rm node
```

App: [http://localhost:2807](http://localhost:2807)  
MariaDB no host: porta `3307`

## Módulos

| Rota | Descrição |
|------|-----------|
| `/` | Home — timer do relacionamento + atalhos |
| `/linha-do-tempo` | Linha do Tempo |
| `/eventos` | CRUD de eventos |
| `/eventos/{id}` | Página do evento |
| `/galeria` | Nossa Galeria (Immich) |
| `/wishlist` | Wishlist compartilhada |

## Arquitetura

- Controllers / Form Requests
- Services (`EventService`, `WishlistService`, `ImmichService`)
- Repositories
- Policies (abertas na 1ª entrega — sem auth)
- Livewire para formulários e galeria

Toda comunicação HTTP com Immich passa só por `App\Services\ImmichService`.
