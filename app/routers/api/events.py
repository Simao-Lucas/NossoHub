from __future__ import annotations

from datetime import date
from typing import Annotated

from fastapi import APIRouter, Depends, File, Form, HTTPException, UploadFile
from sqlalchemy.orm import Session

from app.db import get_db
from app.models.enums import MediaType
from app.policies.event_policy import EventPolicy
from app.routers.api.serializers import event_out, media_out
from app.schemas.common import MessageOut
from app.schemas.event import EventDetailOut, EventOut
from app.services.event_service import EventService

router = APIRouter(prefix="/events", tags=["api-events"])


def _normalize_uploads(uploads: list[UploadFile] | None) -> list[UploadFile]:
    if not uploads:
        return []
    return [u for u in uploads if u is not None and u.filename]


@router.get("", response_model=list[EventOut])
def list_events(db: Session = Depends(get_db)):
    if not EventPolicy().view_any():
        raise HTTPException(status_code=403, detail="Forbidden")
    events = EventService(db).timeline(ascending=False)
    return [event_out(e) for e in events]


@router.get("/{event_id}", response_model=EventDetailOut)
def get_event(event_id: int, db: Session = Depends(get_db)):
    service = EventService(db)
    event = service.find(event_id)
    if event is None:
        raise HTTPException(status_code=404, detail="Evento não encontrado")
    if not EventPolicy().view(event=event):
        raise HTTPException(status_code=403, detail="Forbidden")
    presented = service.present(event)
    photos = [media_out(m) for m in event.media if m.type == MediaType.PHOTO]
    videos = [media_out(m) for m in event.media if m.type == MediaType.VIDEO]
    # present() returns dicts; rebuild from ORM for consistency
    _ = presented
    return EventDetailOut(event=event_out(event), photos=photos, videos=videos)


@router.post("", response_model=EventOut, status_code=201)
async def create_event(
    title: str = Form(...),
    description: str = Form(""),
    occurred_at: date = Form(...),
    uploads: Annotated[list[UploadFile] | None, File()] = None,
    db: Session = Depends(get_db),
):
    if not EventPolicy().create():
        raise HTTPException(status_code=403, detail="Forbidden")
    try:
        event = EventService(db).create(
            title=title,
            description=description,
            occurred_at=occurred_at,
            uploads=_normalize_uploads(uploads),
        )
    except ValueError as exc:
        raise HTTPException(status_code=400, detail=str(exc)) from exc
    return event_out(event)


@router.put("/{event_id}", response_model=EventOut)
async def update_event(
    event_id: int,
    title: str = Form(...),
    description: str = Form(""),
    occurred_at: date = Form(...),
    removed_media_ids: Annotated[list[int] | None, Form()] = None,
    uploads: Annotated[list[UploadFile] | None, File()] = None,
    db: Session = Depends(get_db),
):
    service = EventService(db)
    event = service.find(event_id)
    if event is None:
        raise HTTPException(status_code=404, detail="Evento não encontrado")
    if not EventPolicy().update(event=event):
        raise HTTPException(status_code=403, detail="Forbidden")
    try:
        event = service.update(
            event,
            title=title,
            description=description,
            occurred_at=occurred_at,
            uploads=_normalize_uploads(uploads),
            removed_media_ids=removed_media_ids or [],
        )
    except ValueError as exc:
        raise HTTPException(status_code=400, detail=str(exc)) from exc
    return event_out(event)


@router.delete("/{event_id}", response_model=MessageOut)
def delete_event(event_id: int, db: Session = Depends(get_db)):
    service = EventService(db)
    event = service.find(event_id)
    if event is None:
        raise HTTPException(status_code=404, detail="Evento não encontrado")
    if not EventPolicy().delete(event=event):
        raise HTTPException(status_code=403, detail="Forbidden")
    service.delete(event)
    return MessageOut(detail="Evento excluído.")
