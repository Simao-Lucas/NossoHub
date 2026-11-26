from app.models import Appointment


class AppointmentPolicy:
    """Open policy — no auth yet."""

    def view_any(self, user=None) -> bool:
        return True

    def view(self, user=None, appointment: Appointment | None = None) -> bool:
        return True

    def create(self, user=None) -> bool:
        return True

    def update(self, user=None, appointment: Appointment | None = None) -> bool:
        return True

    def delete(self, user=None, appointment: Appointment | None = None) -> bool:
        return True
