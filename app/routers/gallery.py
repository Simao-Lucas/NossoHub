from fastapi import APIRouter, Request
from fastapi.responses import HTMLResponse

from app.config import get_settings
from app.deps import template_context, templates
from app.services.immich_service import ImmichService

router = APIRouter(tags=["gallery"])


@router.get("/galeria", response_class=HTMLResponse)
def gallery(
    request: Request,
    q: str = "",
    album: str = "",
    taken_after: str = "",
    taken_before: str = "",
    page: int = 1,
):
    settings = get_settings()
    immich = ImmichService(settings)
    configured = immich.is_configured()
    albums: list[dict] = []
    assets: list[dict] = []
    has_more = False
    error: str | None = None

    if configured:
        albums = immich.list_albums()
        album_error = immich.last_error

        filters: dict = {}
        if q.strip():
            filters["query"] = q.strip()
        if taken_after:
            filters["takenAfter"] = f"{taken_after}T00:00:00.000Z"
        if taken_before:
            filters["takenBefore"] = f"{taken_before}T23:59:59.999Z"
        if album:
            filters["albumIds"] = [album]

        page = max(1, page)
        size = 48
        raw = immich.search_assets(filters, page=page, size=size)
        search_error = immich.last_error
        has_more = len(raw) >= size

        for item in raw:
            asset_id = str(item.get("id", ""))
            if not asset_id:
                continue
            asset_type = str(item.get("type", "")).lower()
            is_video = asset_type in {"video", "videos"}
            assets.append(
                {
                    "id": asset_id,
                    "type": "video" if is_video else "image",
                    "thumbnail": immich.app_thumbnail_url(asset_id, "thumbnail"),
                    "preview": (
                        immich.app_original_url(asset_id)
                        if is_video
                        else immich.app_thumbnail_url(asset_id, "preview")
                    ),
                    "original": immich.app_original_url(asset_id),
                }
            )

        if not assets:
            error = search_error or album_error
            if not error and not albums:
                # Likely unreachable host even if key is present
                ping = immich.ping()
                if ping is None:
                    error = (
                        f"Não foi possível falar com o Immich em {settings.immich_base}. "
                        "Do container `nosso_hub_web`, a URL precisa resolver "
                        "(ex.: nome do serviço na rede `homelab`, não só DNS do host)."
                    )

    return templates.TemplateResponse(
        "gallery/index.html",
        template_context(
            request,
            assets=assets,
            albums=albums,
            configured=configured,
            error=error,
            q=q,
            album=album,
            taken_after=taken_after,
            taken_before=taken_before,
            page=page,
            has_more=has_more,
            active="gallery",
        ),
    )
