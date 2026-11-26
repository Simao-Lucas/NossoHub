from __future__ import annotations

from datetime import date, datetime

from pydantic import BaseModel, Field

from app.schemas.common import ORMModel


class EventMediaOut(ORMModel):
    id: int
    event_id: int
    path: str
    original_name: str | None = None
    mime_type: str | None = None
    size: int | None = None
    type: str
    sort_order: int = 0
    url: str


class EventOut(ORMModel):
    id: int
    title: str
    description: str | None = None
    occurred_at: date
    created_at: datetime | None = None
    updated_at: datetime | None = None
    media: list[EventMediaOut] = Field(default_factory=list)


class EventDetailOut(BaseModel):
    event: EventOut
    photos: list[EventMediaOut] = Field(default_factory=list)
    videos: list[EventMediaOut] = Field(default_factory=list)


class TimelineItemOut(BaseModel):
    event: EventOut
    spacer_px: int = 0


class EventWrite(BaseModel):
    title: str = Field(min_length=1, max_length=255)
    description: str | None = None
    occurred_at: date
