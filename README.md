# Nosso Hub (Python / FastAPI)

Hub pessoal para registrar a história de um relacionamento.

Stack: **FastAPI + Jinja2 + SQLAlchemy + MariaDB + Docker**.

A pasta `NossoHub-main/` é o legado Laravel e **não deve ser alterada**. Este app na raiz do repositório substitui o runtime PHP.

## Arquitetura

```text
Router → Policy → Service → Repository → Model
```

## Cutover (preservar o banco)

O volume Docker `nosso_hub_mariadb_data` é reutilizado. **Nunca** use `docker compose down -v`.

```bash
# 1) Parar o stack Laravel SEM apagar o volume
docker compose -f NossoHub-main/docker-compose.yml down

# 2) Copiar mídias de eventos (uma vez)
# PowerShell:
Copy-Item -Recurse -Force NossoHub-main\storage\app\public\events\* media\events\

# 3) Ambiente
copy .env.example .env
# Ajuste IMMICH_BASE_URL / IMMICH_API_KEY se usar a galeria

# 4) Rede Immich (homelab)
docker network create homelab

# 5) Subir o stack Python
docker compose up -d --build
```

App: [http://localhost:2807](http://localhost:2807)  
MariaDB no host: porta `3307`

## Módulos

| Rota | Descrição |
|------|-----------|
| `/` | Home — timer do relacionamento |
| `/linha-do-tempo` | Linha do Tempo |
| `/eventos` | CRUD de eventos + uploads locais |
| `/galeria` | Galeria Immich |
| `/planos` | Planos e categorias |
| `/calendario` | Agenda, ICS e link Google Calendar |

## Desenvolvimento local (sem Docker web)

Com MariaDB já rodando (compose só do `mariadb` ou stack completo):

```bash
python -m venv .venv
.venv\Scripts\activate
pip install -r requirements.txt
copy .env.example .env
# No .env local: DB_HOST=127.0.0.1 DB_PORT=3307 MEDIA_ROOT=./media
uvicorn app.main:app --reload --port 2807
```

## Notas

- Schema de domínio espelha o Laravel; a app **não** roda migrations destrutivas.
- `docker/ensure_schema.sql` só roda em volume MariaDB **novo** (initdb).
- Uploads ficam em `media/events/{id}/`.
- Policies estão abertas (sem auth), como no legado.

## Galeria Immich vazia

A URL em `IMMICH_BASE_URL` precisa ser alcançável **de dentro** do container `nosso_hub_web` (rede `homelab`), não só no browser do host.

```bash
# No homelab: teste DNS/HTTP a partir do container
docker compose exec web python -c "import os,httpx; u=os.environ['IMMICH_BASE_URL'].rstrip('/'); print(u); r=httpx.get(u+'/api/server/about', headers={'x-api-key':os.environ['IMMICH_API_KEY']}, timeout=10); print(r.status_code, r.text[:200])"
```

Se aparecer `Temporary failure in name resolution` para `immich.local`, o DNS do host não existe dentro do container. Ache o Immich na rede:

```bash
docker network inspect homelab --format '{{range .Containers}}{{.Name}} {{.IPv4Address}}{{println}}{{end}}'
```

No `.env`, use o **nome do container** Immich (geralmente algo como `immich_server` / `immich-server`):

```env
IMMICH_BASE_URL=http://immich_server:2283
```

(a porta pode ser `2283` ou `3001` — confira no compose do Immich)

```bash
docker compose up -d web
```

Na página `/galeria` o app mostra o erro real (rede/HTTP), não só “nenhuma mídia”.
