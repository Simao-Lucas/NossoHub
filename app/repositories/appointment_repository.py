from datetime import datetime

from sqlalchemy import select
from sqlalchemy.orm import Session

from app.models import Appointment
from app.models.enums import AppointmentStatus


class AppointmentRepository:
    def __init__(self, db: Session):
        self.db = db

    def between(self, start: datetime, end: datetime) -> list[Appointment]:
        stmt = (
            select(Appointment)
            .where(
                Appointment.starts_at < end,
                Appointment.ends_at > start,
                Appointment.status != AppointmentStatus.CANCELLED,
            )
            .order_by(Appointment.starts_at.asc())
        )
        return list(self.db.scalars(stmt).all())

    def find(self, appointment_id: int) -> Appointment | None:
        return self.db.get(Appointment, appointment_id)

    def create(self, data: dict) -> Appointment:
        appointment = Appointment(**data)
        self.db.add(appointment)
        self.db.flush()
        return appointment

    def update(self, appointment: Appointment, data: dict) -> Appointment:
        for key, value in data.items():
            setattr(appointment, key, value)
        self.db.flush()
        return appointment

    def delete(self, appointment: Appointment) -> None:
        self.db.delete(appointment)
        self.db.flush()
