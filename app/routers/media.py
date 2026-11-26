from fastapi import APIRouter, Depends, HTTPException
from fastapi.responses import FileResponse
from sqlalchemy.orm import Session

from app.db import get_db
from app.models import EventMedia
from app.services.event_service import EventService

router = APIRouter(tags=["media"])


@router.get("/midia/{media_id}")
def show_media(media_id: int, db: Session = Depends(get_db)):
    media = db.get(EventMedia, media_id)
    if media is None:
        raise HTTPException(status_code=404, detail="Mídia não encontrada")

    path = EventService(db).absolute_path(media)
    if not path.exists():
        raise HTTPException(status_code=404, detail="Arquivo não encontrado")

    return FileResponse(
        path,
        media_type=media.mime_type or "application/octet-stream",
        filename=media.original_name or path.name,
        headers={"Cache-Control": "public, max-age=604800"},
    )
