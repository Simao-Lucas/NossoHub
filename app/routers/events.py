from datetime import date
from typing import Annotated

from fastapi import APIRouter, Depends, File, Form, Request, UploadFile
from fastapi.responses import HTMLResponse
from sqlalchemy.orm import Session

from app.db import get_db
from app.deps import redirect_with_flash, template_context, templates
from app.policies.event_policy import EventPolicy
from app.services.event_service import EventService

router = APIRouter(prefix="/eventos", tags=["events"])


def _normalize_uploads(uploads: list[UploadFile] | None) -> list[UploadFile]:
    if not uploads:
        return []
    return [u for u in uploads if u is not None and u.filename]


@router.get("", response_class=HTMLResponse)
def index(request: Request, db: Session = Depends(get_db)):
    policy = EventPolicy()
    if not policy.view_any():
        return HTMLResponse("Forbidden", status_code=403)
    events = EventService(db).timeline(ascending=False)
    return templates.TemplateResponse(
        "events/index.html",
        template_context(request, events=events, active="events"),
    )


@router.get("/criar", response_class=HTMLResponse)
def create_form(request: Request):
    if not EventPolicy().create():
        return HTMLResponse("Forbidden", status_code=403)
    return templates.TemplateResponse(
        "events/form.html",
        template_context(
            request,
            event=None,
            existing_media=[],
            occurred_at=date.today().isoformat(),
            active="events",
        ),
    )


@router.post("/criar")
async def create_submit(
    request: Request,
    title: str = Form(...),
    description: str = Form(""),
    occurred_at: date = Form(...),
    uploads: Annotated[list[UploadFile] | None, File()] = None,
    db: Session = Depends(get_db),
):
    if not EventPolicy().create():
        return HTMLResponse("Forbidden", status_code=403)
    service = EventService(db)
    try:
        event = service.create(
            title=title,
            description=description,
            occurred_at=occurred_at,
            uploads=_normalize_uploads(uploads),
        )
    except ValueError as exc:
        return templates.TemplateResponse(
            "events/form.html",
            template_context(
                request,
                event=None,
                existing_media=[],
                occurred_at=occurred_at.isoformat(),
                title=title,
                description=description,
                error=str(exc),
                active="events",
            ),
            status_code=400,
        )
    return redirect_with_flash(f"/eventos/{event.id}", request, "Evento criado.")


@router.get("/{event_id}", response_class=HTMLResponse)
def show(event_id: int, request: Request, db: Session = Depends(get_db)):
    service = EventService(db)
    event = service.find(event_id)
    if event is None:
        return HTMLResponse("Evento não encontrado", status_code=404)
    if not EventPolicy().view(event=event):
        return HTMLResponse("Forbidden", status_code=403)
    presented = service.present(event)
    return templates.TemplateResponse(
        "events/show.html",
        template_context(request, **presented, active="events"),
    )


@router.get("/{event_id}/editar", response_class=HTMLResponse)
def edit_form(event_id: int, request: Request, db: Session = Depends(get_db)):
    service = EventService(db)
    event = service.find(event_id)
    if event is None:
        return HTMLResponse("Evento não encontrado", status_code=404)
    if not EventPolicy().update(event=event):
        return HTMLResponse("Forbidden", status_code=403)
    presented = service.present(event)
    existing = presented["photos"] + presented["videos"]
    return templates.TemplateResponse(
        "events/form.html",
        template_context(
            request,
            event=event,
            existing_media=existing,
            occurred_at=event.occurred_at.isoformat(),
            active="events",
        ),
    )


@router.post("/{event_id}/editar")
async def edit_submit(
    event_id: int,
    request: Request,
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
        return HTMLResponse("Evento não encontrado", status_code=404)
    if not EventPolicy().update(event=event):
        return HTMLResponse("Forbidden", status_code=403)
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
        presented = service.present(event)
        existing = presented["photos"] + presented["videos"]
        return templates.TemplateResponse(
            "events/form.html",
            template_context(
                request,
                event=event,
                existing_media=existing,
                occurred_at=occurred_at.isoformat(),
                error=str(exc),
                active="events",
            ),
            status_code=400,
        )
    return redirect_with_flash(f"/eventos/{event.id}", request, "Evento atualizado.")


@router.post("/{event_id}/excluir")
def destroy(event_id: int, request: Request, db: Session = Depends(get_db)):
    service = EventService(db)
    event = service.find(event_id)
    if event is None:
        return HTMLResponse("Evento não encontrado", status_code=404)
    if not EventPolicy().delete(event=event):
        return HTMLResponse("Forbidden", status_code=403)
    service.delete(event)
    return redirect_with_flash("/eventos", request, "Evento excluído.")
