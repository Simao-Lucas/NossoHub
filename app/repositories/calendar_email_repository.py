from sqlalchemy import select
from sqlalchemy.orm import Session

from app.models import CalendarEmail


class CalendarEmailRepository:
    def __init__(self, db: Session):
        self.db = db

    def all_ordered(self) -> list[CalendarEmail]:
        stmt = select(CalendarEmail).order_by(
            CalendarEmail.is_active.desc(), CalendarEmail.email.asc()
        )
        return list(self.db.scalars(stmt).all())

    def active_emails(self) -> list[CalendarEmail]:
        stmt = (
            select(CalendarEmail)
            .where(CalendarEmail.is_active.is_(True))
            .order_by(CalendarEmail.email.asc())
        )
        return list(self.db.scalars(stmt).all())

    def find(self, email_id: int) -> CalendarEmail | None:
        return self.db.get(CalendarEmail, email_id)

    def create(self, data: dict) -> CalendarEmail:
        row = CalendarEmail(**data)
        self.db.add(row)
        self.db.flush()
        return row

    def update(self, row: CalendarEmail, data: dict) -> CalendarEmail:
        for key, value in data.items():
            setattr(row, key, value)
        self.db.flush()
        return row

    def delete(self, row: CalendarEmail) -> None:
        self.db.delete(row)
        self.db.flush()
