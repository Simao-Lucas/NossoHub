from sqlalchemy import case, or_, select
from sqlalchemy.orm import Session, selectinload

from app.models import PlanItem


class PlanRepository:
    def __init__(self, db: Session):
        self.db = db

    def filtered(self, filters: dict | None = None) -> list[PlanItem]:
        filters = filters or {}
        stmt = select(PlanItem).options(selectinload(PlanItem.category))

        if category := filters.get("category"):
            stmt = stmt.where(PlanItem.plan_category_id == int(category))
        if status := filters.get("status"):
            stmt = stmt.where(PlanItem.status == status)
        if priority := filters.get("priority"):
            stmt = stmt.where(PlanItem.priority == priority)
        if search := (filters.get("search") or "").strip():
            like = f"%{search}%"
            stmt = stmt.where(
                or_(
                    PlanItem.title.ilike(like),
                    PlanItem.description.ilike(like),
                    PlanItem.notes.ilike(like),
                )
            )

        priority_order = case(
            (PlanItem.priority == "high", 1),
            (PlanItem.priority == "medium", 2),
            (PlanItem.priority == "low", 3),
            else_=4,
        )
        stmt = stmt.order_by(priority_order, PlanItem.updated_at.desc())
        return list(self.db.scalars(stmt).all())

    def find(self, plan_id: int) -> PlanItem | None:
        stmt = (
            select(PlanItem)
            .options(selectinload(PlanItem.category))
            .where(PlanItem.id == plan_id)
        )
        return self.db.scalars(stmt).first()

    def create(self, data: dict) -> PlanItem:
        item = PlanItem(**data)
        self.db.add(item)
        self.db.flush()
        self.db.refresh(item, attribute_names=["category"])
        return item

    def update(self, item: PlanItem, data: dict) -> PlanItem:
        for key, value in data.items():
            setattr(item, key, value)
        self.db.flush()
        self.db.refresh(item, attribute_names=["category"])
        return item

    def delete(self, item: PlanItem) -> None:
        self.db.delete(item)
        self.db.flush()
