from __future__ import annotations

from app.models import Appointment, Event, EventMedia, PlanCategory, PlanItem
from app.schemas.calendar import AppointmentOut
from app.schemas.event import EventMediaOut, EventOut
from app.schemas.plan import PlanCategoryOut, PlanOut


def media_out(media: EventMedia) -> EventMediaOut:
    return EventMediaOut(
        id=media.id,
        event_id=media.event_id,
        path=media.path,
        original_name=media.original_name,
        mime_type=media.mime_type,
        size=media.size,
        type=media.type,
        sort_order=media.sort_order,
        url=f"/midia/{media.id}",
    )


def event_out(event: Event) -> EventOut:
    return EventOut(
        id=event.id,
        title=event.title,
        description=event.description,
        occurred_at=event.occurred_at,
        created_at=event.created_at,
        updated_at=event.updated_at,
        media=[media_out(m) for m in (event.media or [])],
    )


def category_out(category: PlanCategory, plans_count: int = 0) -> PlanCategoryOut:
    return PlanCategoryOut(
        id=category.id,
        name=category.name,
        slug=category.slug,
        sort_order=category.sort_order,
        plans_count=plans_count,
    )


def plan_out(item: PlanItem) -> PlanOut:
    category = None
    if item.category is not None:
        category = category_out(item.category)
    return PlanOut(
        id=item.id,
        title=item.title,
        description=item.description,
        plan_category_id=item.plan_category_id,
        priority=item.priority,
        status=item.status,
        link=item.link,
        notes=item.notes,
        created_at=item.created_at,
        updated_at=item.updated_at,
        category=category,
    )


def appointment_out(
    appointment: Appointment,
    *,
    google_url: str | None = None,
    ics_url: str | None = None,
) -> AppointmentOut:
    return AppointmentOut(
        id=appointment.id,
        summary=appointment.summary,
        description=appointment.description,
        location=appointment.location,
        starts_at=appointment.starts_at,
        ends_at=appointment.ends_at,
        all_day=appointment.all_day,
        timezone=appointment.timezone,
        status=appointment.status,
        visibility=appointment.visibility,
        transparency=appointment.transparency,
        created_at=appointment.created_at,
        updated_at=appointment.updated_at,
        google_url=google_url,
        ics_url=ics_url,
    )
