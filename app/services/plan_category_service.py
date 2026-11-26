from __future__ import annotations

import re
import unicodedata

from sqlalchemy.orm import Session

from app.models import PlanCategory
from app.repositories.plan_category_repository import PlanCategoryRepository


class PlanCategoryService:
    def __init__(self, db: Session):
        self.repo = PlanCategoryRepository(db)
        self.db = db

    def list(self) -> list[tuple[PlanCategory, int]]:
        return self.repo.all_ordered()

    def options(self) -> list[tuple[int, str]]:
        return self.repo.options()

    def find(self, category_id: int) -> PlanCategory | None:
        return self.repo.find(category_id)

    def create(self, name: str) -> PlanCategory:
        name = (name or "").strip()
        if not name:
            raise ValueError("Informe um nome para a categoria.")
        slug = self.unique_slug(name)
        category = self.repo.create({"name": name, "slug": slug})
        self.db.commit()
        return category

    def delete(self, category: PlanCategory) -> None:
        if self.repo.plans_count(category.id) > 0:
            raise ValueError("Não é possível excluir uma categoria com planos.")
        self.repo.delete(category)
        self.db.commit()

    def unique_slug(self, name: str) -> str:
        base = self._slugify(name) or "categoria"
        slug = base
        i = 2
        while self.repo.slug_exists(slug):
            slug = f"{base}-{i}"
            i += 1
        return slug

    @staticmethod
    def _slugify(value: str) -> str:
        normalized = unicodedata.normalize("NFKD", value)
        ascii_text = normalized.encode("ascii", "ignore").decode("ascii")
        slug = re.sub(r"[^a-z0-9]+", "-", ascii_text.lower()).strip("-")
        return slug
