from sqlalchemy import func, select
from sqlalchemy.orm import Session

from app.models import PlanCategory, PlanItem


class PlanCategoryRepository:
    def __init__(self, db: Session):
        self.db = db

    def all_ordered(self) -> list[tuple[PlanCategory, int]]:
        plans_count = (
            select(func.count(PlanItem.id))
            .where(PlanItem.plan_category_id == PlanCategory.id)
            .correlate(PlanCategory)
            .scalar_subquery()
        )
        stmt = (
            select(PlanCategory, plans_count.label("plans_count"))
            .order_by(PlanCategory.sort_order.asc(), PlanCategory.name.asc())
        )
        return list(self.db.execute(stmt).all())

    def options(self) -> list[tuple[int, str]]:
        stmt = select(PlanCategory.id, PlanCategory.name).order_by(
            PlanCategory.sort_order.asc(), PlanCategory.name.asc()
        )
        return list(self.db.execute(stmt).all())

    def find(self, category_id: int) -> PlanCategory | None:
        return self.db.get(PlanCategory, category_id)

    def create(self, data: dict) -> PlanCategory:
        if "sort_order" not in data or data["sort_order"] is None:
            max_order = self.db.scalar(select(func.coalesce(func.max(PlanCategory.sort_order), 0)))
            data["sort_order"] = int(max_order) + 1
        category = PlanCategory(**data)
        self.db.add(category)
        self.db.flush()
        return category

    def plans_count(self, category_id: int) -> int:
        stmt = select(func.count(PlanItem.id)).where(PlanItem.plan_category_id == category_id)
        return int(self.db.scalar(stmt) or 0)

    def delete(self, category: PlanCategory) -> None:
        self.db.delete(category)
        self.db.flush()

    def slug_exists(self, slug: str) -> bool:
        stmt = select(PlanCategory.id).where(PlanCategory.slug == slug).limit(1)
        return self.db.scalar(stmt) is not None
