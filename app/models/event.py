from __future__ import annotations

from datetime import date, datetime
from typing import TYPE_CHECKING

from sqlalchemy import Date, DateTime, String, Text, func
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.db import Base

if TYPE_CHECKING:
    from app.models.event_media import EventMedia


class Event(Base):
    __tablename__ = "events"

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    title: Mapped[str] = mapped_column(String(255), nullable=False)
    description: Mapped[str | None] = mapped_column(Text, nullable=True)
    occurred_at: Mapped[date] = mapped_column(Date, nullable=False, index=True)
    created_at: Mapped[datetime | None] = mapped_column(DateTime, server_default=func.now())
    updated_at: Mapped[datetime | None] = mapped_column(
        DateTime, server_default=func.now(), onupdate=func.now()
    )

    media: Mapped[list[EventMedia]] = relationship(
        "EventMedia",
        back_populates="event",
        cascade="all, delete-orphan",
        order_by="EventMedia.sort_order",
    )

    def short_description(self, limit: int = 140) -> str:
        if not self.description:
            return ""
        text = self.description.strip()
        if len(text) <= limit:
            return text
        return text[: limit - 1].rstrip() + "…"
