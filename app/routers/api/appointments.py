from datetime import date, datetime

from fastapi import APIRouter, Depends, HTTPException, Query
from sqlalchemy.orm import Session

from app.db import get_db
from app.policies.appointment_policy import AppointmentPolicy
from app.routers.api.serializers import appointment_out
from app.schemas.calendar import AppointmentOut, AppointmentWrite
from app.schemas.common import MessageOut
from app.services.appointment_service import AppointmentService
from app.services.google_calendar_export_service import GoogleCalendarExportService

router = APIRouter(prefix="/appointments", tags=["api-appointments"])


@router.get("", response_model=list[AppointmentOut])
def list_appointments(
    year: int | None = None,
    month: int | None = None,
    from_dt: datetime | None = Query(None, alias="from"),
    to_dt: datetime | None = Query(None, alias="to"),
    db: Session = Depends(get_db),
):
    if not AppointmentPolicy().view_any():
        raise HTTPException(status_code=403, detail="Forbidden")

    if from_dt and to_dt:
        start, end = from_dt, to_dt
    else:
        today = date.today()
        y = year or today.year
        m = month or today.month
        start = datetime(y, m, 1)
        if m == 12:
            end = datetime(y + 1, 1, 1)
        else:
            end = datetime(y, m + 1, 1)

    rows = AppointmentService(db).between(start, end)
    return [
        appointment_out(
            a,
            google_url=GoogleCalendarExportService(db).template_url(a),
            ics_url=f"/calendario/{a.id}/ics",
        )
        for a in rows
    ]


@router.get("/{appointment_id}", response_model=AppointmentOut)
def get_appointment(appointment_id: int, db: Session = Depends(get_db)):
    appointment = AppointmentService(db).find(appointment_id)
    if appointment is None:
        raise HTTPException(status_code=404, detail="Compromisso não encontrado")
    if not AppointmentPolicy().view(appointment=appointment):
        raise HTTPException(status_code=403, detail="Forbidden")
    return appointment_out(
        appointment,
        google_url=GoogleCalendarExportService(db).template_url(appointment),
        ics_url=f"/calendario/{appointment.id}/ics",
    )


@router.post("", response_model=AppointmentOut, status_code=201)
def create_appointment(payload: AppointmentWrite, db: Session = Depends(get_db)):
    if not AppointmentPolicy().create():
        raise HTTPException(status_code=403, detail="Forbidden")
    try:
        appointment = AppointmentService(db).create(payload.model_dump())
    except ValueError as exc:
        raise HTTPException(status_code=400, detail=str(exc)) from exc
    return appointment_out(
        appointment,
        google_url=GoogleCalendarExportService(db).template_url(appointment),
        ics_url=f"/calendario/{appointment.id}/ics",
    )


@router.put("/{appointment_id}", response_model=AppointmentOut)
def update_appointment(
    appointment_id: int,
    payload: AppointmentWrite,
    db: Session = Depends(get_db),
):
    service = AppointmentService(db)
    appointment = service.find(appointment_id)
    if appointment is None:
        raise HTTPException(status_code=404, detail="Compromisso não encontrado")
    if not AppointmentPolicy().update(appointment=appointment):
        raise HTTPException(status_code=403, detail="Forbidden")
    try:
        appointment = service.update(appointment, payload.model_dump())
    except ValueError as exc:
        raise HTTPException(status_code=400, detail=str(exc)) from exc
    return appointment_out(
        appointment,
        google_url=GoogleCalendarExportService(db).template_url(appointment),
        ics_url=f"/calendario/{appointment.id}/ics",
    )


@router.delete("/{appointment_id}", response_model=MessageOut)
def delete_appointment(appointment_id: int, db: Session = Depends(get_db)):
    service = AppointmentService(db)
    appointment = service.find(appointment_id)
    if appointment is None:
        raise HTTPException(status_code=404, detail="Compromisso não encontrado")
    if not AppointmentPolicy().delete(appointment=appointment):
        raise HTTPException(status_code=403, detail="Forbidden")
    service.delete(appointment)
    return MessageOut(detail="Compromisso excluído.")
