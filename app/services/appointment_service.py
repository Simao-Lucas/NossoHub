from datetime import datetime

from sqlalchemy.orm import Session

from app.models import Appointment
from app.repositories.appointment_repository import AppointmentRepository


class AppointmentService:
    def __init__(self, db: Session):
        self.repo = AppointmentRepository(db)
        self.db = db

    def find(self, appointment_id: int) -> Appointment | None:
        return self.repo.find(appointment_id)

    def between(self, start: datetime, end: datetime) -> list[Appointment]:
        return self.repo.between(start, end)

    def create(self, data: dict) -> Appointment:
        self._assert_date_range(data.get("starts_at"), data.get("ends_at"))
        appointment = self.repo.create(data)
        self.db.commit()
        return appointment

    def update(self, appointment: Appointment, data: dict) -> Appointment:
        merged = {
            "starts_at": data.get("starts_at", appointment.starts_at),
            "ends_at": data.get("ends_at", appointment.ends_at),
        }
        self._assert_date_range(merged["starts_at"], merged["ends_at"])
        appointment = self.repo.update(appointment, data)
        self.db.commit()
        return appointment

    def delete(self, appointment: Appointment) -> None:
        self.repo.delete(appointment)
        self.db.commit()

    @staticmethod
    def _assert_date_range(starts_at, ends_at) -> None:
        if starts_at is not None and ends_at is not None and ends_at < starts_at:
            raise ValueError("O término deve ser depois do início.")
