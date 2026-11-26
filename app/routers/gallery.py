from fastapi import APIRouter, Request
from fastapi.responses import HTMLResponse

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
    immich = ImmichService()
    albums = immich.list_albums() if immich.is_configured() else []
    assets: list[dict] = []
    configured = immich.is_configured()
    has_more = False

    if configured:
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
        has_more = len(raw) >= size
        for item in raw:
            asset_id = str(item.get("id", ""))
            if not asset_id:
                continue
            is_video = str(item.get("type", "")).lower() == "video"
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

    return templates.TemplateResponse(
        "gallery/index.html",
        template_context(
            request,
            assets=assets,
            albums=albums,
            configured=configured,
            q=q,
            album=album,
            taken_after=taken_after,
            taken_before=taken_before,
            page=page,
            has_more=has_more,
            active="gallery",
        ),
    )
