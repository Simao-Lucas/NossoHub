from sqlalchemy import func, select
from sqlalchemy.orm import Session, selectinload

from app.models import Event, EventMedia
from app.models.enums import MediaType


class EventRepository:
    def __init__(self, db: Session):
        self.db = db

    def all_chronological(self, ascending: bool = False) -> list[Event]:
        order = Event.occurred_at.asc() if ascending else Event.occurred_at.desc()
        id_order = Event.id.asc() if ascending else Event.id.desc()
        stmt = (
            select(Event)
            .options(selectinload(Event.media))
            .order_by(order, id_order)
        )
        return list(self.db.scalars(stmt).all())

    def find(self, event_id: int) -> Event | None:
        stmt = (
            select(Event)
            .options(selectinload(Event.media))
            .where(Event.id == event_id)
        )
        return self.db.scalars(stmt).first()

    def create(self, data: dict) -> Event:
        event = Event(**data)
        self.db.add(event)
        self.db.flush()
        return event

    def update(self, event: Event, data: dict) -> Event:
        for key, value in data.items():
            setattr(event, key, value)
        self.db.flush()
        return event

    def delete(self, event: Event) -> None:
        self.db.delete(event)
        self.db.flush()

    def add_media(self, event: Event, attrs: dict) -> EventMedia:
        media = EventMedia(event_id=event.id, **attrs)
        self.db.add(media)
        self.db.flush()
        return media

    def media_by_ids(self, event: Event, ids: list[int]) -> list[EventMedia]:
        if not ids:
            return []
        stmt = select(EventMedia).where(
            EventMedia.event_id == event.id,
            EventMedia.id.in_(ids),
        )
        return list(self.db.scalars(stmt).all())

    def next_sort_order(self, event_id: int) -> int:
        stmt = select(func.coalesce(func.max(EventMedia.sort_order), -1)).where(
            EventMedia.event_id == event_id
        )
        current = self.db.scalar(stmt)
        return int(current) + 1

    def photo_count(self, event: Event) -> int:
        return sum(1 for m in event.media if m.type == MediaType.PHOTO)

    def video_count(self, event: Event) -> int:
        return sum(1 for m in event.media if m.type == MediaType.VIDEO)
