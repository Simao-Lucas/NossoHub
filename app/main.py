from pathlib import Path

from fastapi import FastAPI
from fastapi.staticfiles import StaticFiles
from starlette.middleware.sessions import SessionMiddleware

from app.config import get_settings
from app.routers import calendar, events, gallery, home, immich, media, plans, timeline

settings = get_settings()

app = FastAPI(title=settings.app_name, docs_url="/docs" if settings.app_debug else None)

app.add_middleware(
    SessionMiddleware,
    secret_key=settings.db_password or "nosso-hub-dev-secret",
    same_site="lax",
)

static_dir = Path(__file__).resolve().parent / "static"
app.mount("/static", StaticFiles(directory=str(static_dir)), name="static")

app.include_router(home.router)
app.include_router(timeline.router)
app.include_router(events.router)
app.include_router(media.router)
app.include_router(plans.router)
app.include_router(calendar.router)
app.include_router(gallery.router)
app.include_router(immich.router)


@app.get("/up")
def health():
    return {"status": "ok"}
