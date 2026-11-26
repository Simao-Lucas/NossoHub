from __future__ import annotations

from datetime import datetime

from pydantic import BaseModel, Field

from app.schemas.common import ORMModel


class PlanCategoryOut(ORMModel):
    id: int
    name: str
    slug: str
    sort_order: int = 0
    plans_count: int = 0


class PlanCategoryCreate(BaseModel):
    name: str = Field(min_length=1, max_length=100)


class PlanOut(ORMModel):
    id: int
    title: str
    description: str | None = None
    plan_category_id: int
    priority: str
    status: str
    link: str | None = None
    notes: str | None = None
    created_at: datetime | None = None
    updated_at: datetime | None = None
    category: PlanCategoryOut | None = None


class PlanWrite(BaseModel):
    title: str = Field(min_length=1, max_length=255)
    description: str | None = None
    plan_category_id: int
    priority: str = "medium"
    status: str = "pending"
    link: str | None = None
    notes: str | None = None
