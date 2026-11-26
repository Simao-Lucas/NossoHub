from fastapi import APIRouter, Query

from app.config import get_settings
from app.schemas.gallery import GalleryAlbumOut, GalleryAssetOut, GalleryOut
from app.services.immich_service import ImmichService

router = APIRouter(tags=["api-gallery"])


@router.get("/gallery", response_model=GalleryOut)
def api_gallery(
    q: str = "",
    album: str = "",
    taken_after: str = "",
    taken_before: str = "",
    page: int = Query(1, ge=1),
):
    settings = get_settings()
    immich = ImmichService(settings)
    configured = immich.is_configured()
    if not configured:
        return GalleryOut(configured=False, error="Immich não configurado")

    albums_raw = immich.list_albums()
    album_error = immich.last_error
    albums = [
        GalleryAlbumOut(
            id=str(item.get("id", "")),
            name=str(item.get("albumName") or item.get("name") or "Álbum"),
        )
        for item in albums_raw
        if item.get("id")
    ]

    filters: dict = {}
    if q.strip():
        filters["query"] = q.strip()
    if taken_after:
        filters["takenAfter"] = f"{taken_after}T00:00:00.000Z"
    if taken_before:
        filters["takenBefore"] = f"{taken_before}T23:59:59.999Z"
    if album:
        filters["albumIds"] = [album]

    size = 48
    raw = immich.search_assets(filters, page=page, size=size)
    search_error = immich.last_error
    assets: list[GalleryAssetOut] = []
    for item in raw:
        asset_id = str(item.get("id", ""))
        if not asset_id:
            continue
        is_video = str(item.get("type", "")).lower() in {"video", "videos"}
        assets.append(
            GalleryAssetOut(
                id=asset_id,
                type="video" if is_video else "image",
                thumbnail=immich.app_thumbnail_url(asset_id, "thumbnail"),
                preview=(
                    immich.app_original_url(asset_id)
                    if is_video
                    else immich.app_thumbnail_url(asset_id, "preview")
                ),
                original=immich.app_original_url(asset_id),
            )
        )

    error = None
    if not assets:
        error = search_error or album_error
        if not error and not albums:
            if immich.ping() is None:
                error = (
                    f"Não foi possível falar com o Immich em {settings.immich_base}."
                )

    return GalleryOut(
        configured=True,
        assets=assets,
        albums=albums,
        page=page,
        has_more=len(raw) >= size,
        error=error,
    )
