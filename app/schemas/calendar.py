from __future__ import annotations

from datetime import datetime

from pydantic import BaseModel, Field

from app.schemas.common import ORMModel


class AppointmentOut(ORMModel):
    id: int
    summary: str
    description: str | None = None
    location: str | None = None
    starts_at: datetime
    ends_at: datetime
    all_day: bool = False
    timezone: str = "America/Sao_Paulo"
    status: str = "confirmed"
    visibility: str = "default"
    transparency: str = "opaque"
    created_at: datetime | None = None
    updated_at: datetime | None = None
    google_url: str | None = None
    ics_url: str | None = None


class AppointmentWrite(BaseModel):
    summary: str = Field(min_length=1, max_length=255)
    description: str | None = None
    location: str | None = None
    starts_at: datetime
    ends_at: datetime
    all_day: bool = False
    timezone: str = "America/Sao_Paulo"
    status: str = "confirmed"
    visibility: str = "default"
    transparency: str = "opaque"


class CalendarEmailOut(ORMModel):
    id: int
    email: str
    label: str | None = None
    is_active: bool = True


class CalendarEmailCreate(BaseModel):
    email: str
    label: str | None = None
    is_active: bool = True
