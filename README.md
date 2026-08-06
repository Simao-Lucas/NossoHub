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

Desenvolvimento/edição pode ser no Windows. A aplicação sobe no Docker do servidor.

```bash
cp .env.example .env
# Ajuste APP_URL, DB_* e Immich:
# IMMICH_BASE_URL=https://seu-immich
# IMMICH_API_KEY=sua-chave

docker compose up -d --build
```

App: [http://localhost:2807](http://localhost:2807)  
MariaDB no host: porta `3307`

Na primeira subida o container `php` roda `composer install`, gera `APP_KEY` (se vazio) e `migrate`. O container `node` compila os assets Vite.

## Módulos

| Rota | Descrição |
|------|-----------|
| `/` | Linha do Tempo |
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
