from __future__ import annotations

from sqlalchemy.orm import Session

from app.models import PlanItem
from app.repositories.plan_repository import PlanRepository


class PlanService:
    def __init__(self, db: Session):
        self.repo = PlanRepository(db)
        self.db = db

    def list(self, filters: dict | None = None) -> list[PlanItem]:
        return self.repo.filtered(filters)

    def find(self, plan_id: int) -> PlanItem | None:
        return self.repo.find(plan_id)

    def create(self, data: dict) -> PlanItem:
        item = self.repo.create(self._normalize(data))
        self.db.commit()
        return self.repo.find(item.id) or item

    def update(self, item: PlanItem, data: dict) -> PlanItem:
        item = self.repo.update(item, self._normalize(data))
        self.db.commit()
        return self.repo.find(item.id) or item

    def delete(self, item: PlanItem) -> None:
        self.repo.delete(item)
        self.db.commit()

    @staticmethod
    def _normalize(data: dict) -> dict:
        cleaned = dict(data)
        for key in ("description", "link", "notes"):
            if key in cleaned:
                value = cleaned[key]
                cleaned[key] = (value or "").strip() or None
        if "title" in cleaned:
            cleaned["title"] = cleaned["title"].strip()
        return cleaned
