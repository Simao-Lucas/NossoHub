from __future__ import annotations

import re

from sqlalchemy.exc import IntegrityError
from sqlalchemy.orm import Session

from app.models import CalendarEmail
from app.repositories.calendar_email_repository import CalendarEmailRepository

EMAIL_RE = re.compile(r"^[^@\s]+@[^@\s]+\.[^@\s]+$")


class CalendarEmailService:
    def __init__(self, db: Session):
        self.repo = CalendarEmailRepository(db)
        self.db = db

    def list(self) -> list[CalendarEmail]:
        return self.repo.all_ordered()

    def active(self) -> list[CalendarEmail]:
        return self.repo.active_emails()

    def find(self, email_id: int) -> CalendarEmail | None:
        return self.repo.find(email_id)

    def create(self, email: str, label: str | None = None, is_active: bool = True) -> CalendarEmail:
        normalized = self._normalize_email(email)
        try:
            row = self.repo.create(
                {
                    "email": normalized,
                    "label": (label or "").strip() or None,
                    "is_active": is_active,
                }
            )
            self.db.commit()
            return row
        except IntegrityError as exc:
            self.db.rollback()
            raise ValueError("Este e-mail já está vinculado.") from exc

    def update(
        self,
        row: CalendarEmail,
        *,
        email: str | None = None,
        label: str | None = None,
        is_active: bool | None = None,
    ) -> CalendarEmail:
        data: dict = {}
        if email is not None:
            data["email"] = self._normalize_email(email)
        if label is not None:
            data["label"] = label.strip() or None
        if is_active is not None:
            data["is_active"] = is_active
        try:
            row = self.repo.update(row, data)
            self.db.commit()
            return row
        except IntegrityError as exc:
            self.db.rollback()
            raise ValueError("Este e-mail já está vinculado.") from exc

    def delete(self, row: CalendarEmail) -> None:
        self.repo.delete(row)
        self.db.commit()

    def toggle_active(self, row: CalendarEmail) -> CalendarEmail:
        return self.update(row, is_active=not row.is_active)

    @staticmethod
    def _normalize_email(email: str) -> str:
        value = (email or "").strip().lower()
        if not EMAIL_RE.match(value):
            raise ValueError("Informe um e-mail válido.")
        return value
