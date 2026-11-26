from calendar import Calendar
from datetime import date, datetime, time, timedelta

from fastapi import APIRouter, Depends, Form, Request
from fastapi.responses import HTMLResponse, Response
from sqlalchemy.orm import Session

from app.db import get_db
from app.deps import redirect_with_flash, template_context, templates
from app.models.enums import (
    AppointmentStatus,
    AppointmentTransparency,
    AppointmentVisibility,
)
from app.policies.appointment_policy import AppointmentPolicy
from app.services.appointment_service import AppointmentService
from app.services.calendar_email_service import CalendarEmailService
from app.services.google_calendar_export_service import GoogleCalendarExportService

router = APIRouter(prefix="/calendario", tags=["calendar"])


def _parse_month(year: int | None, month: int | None) -> date:
    today = date.today()
    y = year or today.year
    m = month or today.month
    return date(y, m, 1)


def _month_grid(anchor: date, appointments: list) -> list[dict]:
    cal = Calendar(firstweekday=0)  # Monday
    weeks = cal.monthdatescalendar(anchor.year, anchor.month)
    by_day: dict[date, list] = {}
    for appt in appointments:
        start_day = appt.starts_at.date()
        end_day = appt.ends_at.date()
        day = start_day
        while day <= end_day:
            by_day.setdefault(day, []).append(appt)
            day += timedelta(days=1)

    grid = []
    for week in weeks:
        row = []
        for day in week:
            row.append(
                {
                    "date": day,
                    "in_month": day.month == anchor.month,
                    "appointments": by_day.get(day, []),
                }
            )
        grid.append(row)
    return grid


@router.get("", response_class=HTMLResponse)
def index(
    request: Request,
    year: int | None = None,
    month: int | None = None,
    db: Session = Depends(get_db),
):
    if not AppointmentPolicy().view_any():
        return HTMLResponse("Forbidden", status_code=403)
    anchor = _parse_month(year, month)
    # Monday-start grid range
    cal = Calendar(firstweekday=0)
    weeks = cal.monthdatescalendar(anchor.year, anchor.month)
    grid_start = datetime.combine(weeks[0][0], time.min)
    grid_end = datetime.combine(weeks[-1][-1] + timedelta(days=1), time.min)
    appointments = AppointmentService(db).between(grid_start, grid_end)
    prev_month = (anchor.replace(day=1) - timedelta(days=1)).replace(day=1)
    if anchor.month == 12:
        next_month = date(anchor.year + 1, 1, 1)
    else:
        next_month = date(anchor.year, anchor.month + 1, 1)

    months_pt = [
        "",
        "janeiro",
        "fevereiro",
        "março",
        "abril",
        "maio",
        "junho",
        "julho",
        "agosto",
        "setembro",
        "outubro",
        "novembro",
        "dezembro",
    ]
    return templates.TemplateResponse(
        "calendar/index.html",
        template_context(
            request,
            anchor=anchor,
            month_label=f"{months_pt[anchor.month]} {anchor.year}",
            grid=_month_grid(anchor, appointments),
            prev_month=prev_month,
            next_month=next_month,
            active="calendar",
        ),
    )


@router.get("/criar", response_class=HTMLResponse)
def create_form(request: Request):
    if not AppointmentPolicy().create():
        return HTMLResponse("Forbidden", status_code=403)
    now = datetime.now().replace(minute=0, second=0, microsecond=0) + timedelta(hours=1)
    return templates.TemplateResponse(
        "calendar/form.html",
        template_context(
            request,
            appointment=None,
            starts_at=now.strftime("%Y-%m-%dT%H:%M"),
            ends_at=(now + timedelta(hours=1)).strftime("%Y-%m-%dT%H:%M"),
            statuses=AppointmentStatus.options(),
            visibilities=AppointmentVisibility.options(),
            transparencies=AppointmentTransparency.options(),
            active="calendar",
        ),
    )


@router.post("/criar")
def create_submit(
    request: Request,
    summary: str = Form(...),
    description: str = Form(""),
    location: str = Form(""),
    starts_at: str = Form(...),
    ends_at: str = Form(...),
    all_day: str | None = Form(None),
    timezone: str = Form("America/Sao_Paulo"),
    status: str = Form("confirmed"),
    visibility: str = Form("default"),
    transparency: str = Form("opaque"),
    db: Session = Depends(get_db),
):
    if not AppointmentPolicy().create():
        return HTMLResponse("Forbidden", status_code=403)
    is_all_day = all_day in {"true", "on", "1", "yes"}
    try:
        start_dt, end_dt = _parse_range(starts_at, ends_at, is_all_day)
        appointment = AppointmentService(db).create(
            {
                "summary": summary.strip(),
                "description": description.strip() or None,
                "location": location.strip() or None,
                "starts_at": start_dt,
                "ends_at": end_dt,
                "all_day": is_all_day,
                "timezone": timezone,
                "status": status,
                "visibility": visibility,
                "transparency": transparency,
            }
        )
    except ValueError as exc:
        return templates.TemplateResponse(
            "calendar/form.html",
            template_context(
                request,
                appointment=None,
                starts_at=starts_at,
                ends_at=ends_at,
                error=str(exc),
                statuses=AppointmentStatus.options(),
                visibilities=AppointmentVisibility.options(),
                transparencies=AppointmentTransparency.options(),
                active="calendar",
            ),
            status_code=400,
        )
    return redirect_with_flash(f"/calendario/{appointment.id}", request, "Compromisso criado.")


@router.get("/emails", response_class=HTMLResponse)
def emails(request: Request, db: Session = Depends(get_db)):
    rows = CalendarEmailService(db).list()
    return templates.TemplateResponse(
        "calendar/emails.html",
        template_context(request, emails=rows, active="calendar"),
    )


@router.post("/emails")
def emails_create(
    request: Request,
    email: str = Form(...),
    label: str = Form(""),
    db: Session = Depends(get_db),
):
    try:
        CalendarEmailService(db).create(email, label=label, is_active=True)
        return redirect_with_flash("/calendario/emails", request, "E-mail adicionado.")
    except ValueError as exc:
        rows = CalendarEmailService(db).list()
        return templates.TemplateResponse(
            "calendar/emails.html",
            template_context(request, emails=rows, error=str(exc), active="calendar"),
            status_code=400,
        )


@router.post("/emails/{email_id}/toggle")
def emails_toggle(email_id: int, request: Request, db: Session = Depends(get_db)):
    service = CalendarEmailService(db)
    row = service.find(email_id)
    if row is None:
        return HTMLResponse("E-mail não encontrado", status_code=404)
    service.toggle_active(row)
    return redirect_with_flash("/calendario/emails", request, "E-mail atualizado.")


@router.post("/emails/{email_id}/excluir")
def emails_delete(email_id: int, request: Request, db: Session = Depends(get_db)):
    service = CalendarEmailService(db)
    row = service.find(email_id)
    if row is None:
        return HTMLResponse("E-mail não encontrado", status_code=404)
    service.delete(row)
    return redirect_with_flash("/calendario/emails", request, "E-mail removido.")


@router.get("/{appointment_id}/ics")
def ics(appointment_id: int, db: Session = Depends(get_db)):
    appointment = AppointmentService(db).find(appointment_id)
    if appointment is None:
        return HTMLResponse("Compromisso não encontrado", status_code=404)
    exporter = GoogleCalendarExportService(db)
    content = exporter.ics(appointment)
    filename = exporter.ics_filename(appointment)
    return Response(
        content=content,
        media_type="text/calendar; charset=utf-8",
        headers={"Content-Disposition": f'attachment; filename="{filename}"'},
    )


@router.get("/{appointment_id}/editar", response_class=HTMLResponse)
def edit_form(appointment_id: int, request: Request, db: Session = Depends(get_db)):
    appointment = AppointmentService(db).find(appointment_id)
    if appointment is None:
        return HTMLResponse("Compromisso não encontrado", status_code=404)
    if not AppointmentPolicy().update(appointment=appointment):
        return HTMLResponse("Forbidden", status_code=403)
    if appointment.all_day:
        starts = appointment.starts_at.strftime("%Y-%m-%d")
        ends = appointment.ends_at.strftime("%Y-%m-%d")
    else:
        starts = appointment.starts_at.strftime("%Y-%m-%dT%H:%M")
        ends = appointment.ends_at.strftime("%Y-%m-%dT%H:%M")
    return templates.TemplateResponse(
        "calendar/form.html",
        template_context(
            request,
            appointment=appointment,
            starts_at=starts,
            ends_at=ends,
            statuses=AppointmentStatus.options(),
            visibilities=AppointmentVisibility.options(),
            transparencies=AppointmentTransparency.options(),
            active="calendar",
        ),
    )


@router.post("/{appointment_id}/editar")
def edit_submit(
    appointment_id: int,
    request: Request,
    summary: str = Form(...),
    description: str = Form(""),
    location: str = Form(""),
    starts_at: str = Form(...),
    ends_at: str = Form(...),
    all_day: str | None = Form(None),
    timezone: str = Form("America/Sao_Paulo"),
    status: str = Form("confirmed"),
    visibility: str = Form("default"),
    transparency: str = Form("opaque"),
    db: Session = Depends(get_db),
):
    service = AppointmentService(db)
    appointment = service.find(appointment_id)
    if appointment is None:
        return HTMLResponse("Compromisso não encontrado", status_code=404)
    if not AppointmentPolicy().update(appointment=appointment):
        return HTMLResponse("Forbidden", status_code=403)
    is_all_day = all_day in {"true", "on", "1", "yes"}
    try:
        start_dt, end_dt = _parse_range(starts_at, ends_at, is_all_day)
        appointment = service.update(
            appointment,
            {
                "summary": summary.strip(),
                "description": description.strip() or None,
                "location": location.strip() or None,
                "starts_at": start_dt,
                "ends_at": end_dt,
                "all_day": is_all_day,
                "timezone": timezone,
                "status": status,
                "visibility": visibility,
                "transparency": transparency,
            },
        )
    except ValueError as exc:
        return templates.TemplateResponse(
            "calendar/form.html",
            template_context(
                request,
                appointment=appointment,
                starts_at=starts_at,
                ends_at=ends_at,
                error=str(exc),
                statuses=AppointmentStatus.options(),
                visibilities=AppointmentVisibility.options(),
                transparencies=AppointmentTransparency.options(),
                active="calendar",
            ),
            status_code=400,
        )
    return redirect_with_flash(
        f"/calendario/{appointment.id}", request, "Compromisso atualizado."
    )


@router.get("/{appointment_id}", response_class=HTMLResponse)
def show(appointment_id: int, request: Request, db: Session = Depends(get_db)):
    appointment = AppointmentService(db).find(appointment_id)
    if appointment is None:
        return HTMLResponse("Compromisso não encontrado", status_code=404)
    if not AppointmentPolicy().view(appointment=appointment):
        return HTMLResponse("Forbidden", status_code=403)
    google_url = GoogleCalendarExportService(db).template_url(appointment)
    return templates.TemplateResponse(
        "calendar/show.html",
        template_context(
            request,
            appointment=appointment,
            google_url=google_url,
            active="calendar",
        ),
    )


@router.post("/{appointment_id}/excluir")
def destroy(appointment_id: int, request: Request, db: Session = Depends(get_db)):
    service = AppointmentService(db)
    appointment = service.find(appointment_id)
    if appointment is None:
        return HTMLResponse("Compromisso não encontrado", status_code=404)
    if not AppointmentPolicy().delete(appointment=appointment):
        return HTMLResponse("Forbidden", status_code=403)
    service.delete(appointment)
    return redirect_with_flash("/calendario", request, "Compromisso excluído.")


def _parse_range(starts_at: str, ends_at: str, all_day: bool) -> tuple[datetime, datetime]:
    if all_day:
        start = datetime.strptime(starts_at[:10], "%Y-%m-%d")
        end = datetime.strptime(ends_at[:10], "%Y-%m-%d")
        return start, end
    start = datetime.strptime(starts_at.replace("T", " ")[:16], "%Y-%m-%d %H:%M")
    end = datetime.strptime(ends_at.replace("T", " ")[:16], "%Y-%m-%d %H:%M")
    return start, end
