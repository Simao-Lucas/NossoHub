from app.models import PlanItem


class PlanItemPolicy:
    """Open policy — mirrors Laravel PlanItemPolicy (no auth yet)."""

    def view_any(self, user=None) -> bool:
        return True

    def view(self, user=None, item: PlanItem | None = None) -> bool:
        return True

    def create(self, user=None) -> bool:
        return True

    def update(self, user=None, item: PlanItem | None = None) -> bool:
        return True

    def delete(self, user=None, item: PlanItem | None = None) -> bool:
        return True
