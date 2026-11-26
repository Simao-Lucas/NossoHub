from app.models import Event


class EventPolicy:
    """Open policy — mirrors Laravel EventPolicy (no auth yet)."""

    def view_any(self, user=None) -> bool:
        return True

    def view(self, user=None, event: Event | None = None) -> bool:
        return True

    def create(self, user=None) -> bool:
        return True

    def update(self, user=None, event: Event | None = None) -> bool:
        return True

    def delete(self, user=None, event: Event | None = None) -> bool:
        return True
