from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session

from app.db import get_db
from app.schemas.calendar import CalendarEmailCreate, CalendarEmailOut
from app.schemas.common import MessageOut
from app.services.calendar_email_service import CalendarEmailService

router = APIRouter(prefix="/calendar-emails", tags=["api-calendar-emails"])


@router.get("", response_model=list[CalendarEmailOut])
def list_emails(db: Session = Depends(get_db)):
    return CalendarEmailService(db).list()


@router.post("", response_model=CalendarEmailOut, status_code=201)
def create_email(payload: CalendarEmailCreate, db: Session = Depends(get_db)):
    try:
        return CalendarEmailService(db).create(
            payload.email, label=payload.label, is_active=payload.is_active
        )
    except ValueError as exc:
        raise HTTPException(status_code=400, detail=str(exc)) from exc


@router.patch("/{email_id}/toggle", response_model=CalendarEmailOut)
def toggle_email(email_id: int, db: Session = Depends(get_db)):
    service = CalendarEmailService(db)
    row = service.find(email_id)
    if row is None:
        raise HTTPException(status_code=404, detail="E-mail não encontrado")
    return service.toggle_active(row)


@router.delete("/{email_id}", response_model=MessageOut)
def delete_email(email_id: int, db: Session = Depends(get_db)):
    service = CalendarEmailService(db)
    row = service.find(email_id)
    if row is None:
        raise HTTPException(status_code=404, detail="E-mail não encontrado")
    service.delete(row)
    return MessageOut(detail="E-mail removido.")
