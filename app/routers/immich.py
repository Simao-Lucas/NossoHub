from fastapi import APIRouter, HTTPException, Query
from fastapi.responses import Response

from app.services.immich_service import ImmichService

router = APIRouter(prefix="/immich", tags=["immich"])


@router.get("/assets/{asset_id}/thumbnail")
def thumbnail(asset_id: str, size: str = Query("thumbnail")):
    if size not in {"thumbnail", "preview", "fullsize"}:
        size = "thumbnail"
    try:
        content, content_type = ImmichService().fetch_thumbnail(asset_id, size=size)
    except RuntimeError:
        raise HTTPException(status_code=404, detail="Asset não encontrado") from None
    return Response(
        content=content,
        media_type=content_type,
        headers={"Cache-Control": "private, max-age=86400"},
    )


@router.get("/assets/{asset_id}/original")
def original(asset_id: str):
    try:
        content, content_type = ImmichService().fetch_original(asset_id)
    except RuntimeError:
        raise HTTPException(status_code=404, detail="Asset não encontrado") from None
    return Response(
        content=content,
        media_type=content_type,
        headers={"Cache-Control": "private, max-age=86400"},
    )
