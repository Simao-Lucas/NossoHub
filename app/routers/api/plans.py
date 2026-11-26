from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session

from app.db import get_db
from app.policies.plan_policy import PlanItemPolicy
from app.routers.api.serializers import category_out, plan_out
from app.schemas.common import MessageOut
from app.schemas.plan import PlanCategoryCreate, PlanCategoryOut, PlanOut, PlanWrite
from app.services.plan_category_service import PlanCategoryService
from app.services.plan_service import PlanService

router = APIRouter(tags=["api-plans"])


@router.get("/plans", response_model=list[PlanOut])
def list_plans(
    search: str | None = None,
    category: int | None = None,
    status: str | None = None,
    priority: str | None = None,
    db: Session = Depends(get_db),
):
    if not PlanItemPolicy().view_any():
        raise HTTPException(status_code=403, detail="Forbidden")
    filters = {
        "search": search,
        "category": category,
        "status": status,
        "priority": priority,
    }
    return [plan_out(p) for p in PlanService(db).list(filters)]


@router.get("/plans/{plan_id}", response_model=PlanOut)
def get_plan(plan_id: int, db: Session = Depends(get_db)):
    item = PlanService(db).find(plan_id)
    if item is None:
        raise HTTPException(status_code=404, detail="Plano não encontrado")
    if not PlanItemPolicy().view(item=item):
        raise HTTPException(status_code=403, detail="Forbidden")
    return plan_out(item)


@router.post("/plans", response_model=PlanOut, status_code=201)
def create_plan(payload: PlanWrite, db: Session = Depends(get_db)):
    if not PlanItemPolicy().create():
        raise HTTPException(status_code=403, detail="Forbidden")
    item = PlanService(db).create(payload.model_dump())
    return plan_out(item)


@router.put("/plans/{plan_id}", response_model=PlanOut)
def update_plan(plan_id: int, payload: PlanWrite, db: Session = Depends(get_db)):
    service = PlanService(db)
    item = service.find(plan_id)
    if item is None:
        raise HTTPException(status_code=404, detail="Plano não encontrado")
    if not PlanItemPolicy().update(item=item):
        raise HTTPException(status_code=403, detail="Forbidden")
    item = service.update(item, payload.model_dump())
    return plan_out(item)


@router.delete("/plans/{plan_id}", response_model=MessageOut)
def delete_plan(plan_id: int, db: Session = Depends(get_db)):
    service = PlanService(db)
    item = service.find(plan_id)
    if item is None:
        raise HTTPException(status_code=404, detail="Plano não encontrado")
    if not PlanItemPolicy().delete(item=item):
        raise HTTPException(status_code=403, detail="Forbidden")
    service.delete(item)
    return MessageOut(detail="Plano excluído.")


@router.get("/plan-categories", response_model=list[PlanCategoryOut])
def list_categories(db: Session = Depends(get_db)):
    rows = PlanCategoryService(db).list()
    return [category_out(cat, count) for cat, count in rows]


@router.post("/plan-categories", response_model=PlanCategoryOut, status_code=201)
def create_category(payload: PlanCategoryCreate, db: Session = Depends(get_db)):
    try:
        category = PlanCategoryService(db).create(payload.name)
    except ValueError as exc:
        raise HTTPException(status_code=400, detail=str(exc)) from exc
    return category_out(category, 0)


@router.delete("/plan-categories/{category_id}", response_model=MessageOut)
def delete_category(category_id: int, db: Session = Depends(get_db)):
    service = PlanCategoryService(db)
    category = service.find(category_id)
    if category is None:
        raise HTTPException(status_code=404, detail="Categoria não encontrada")
    try:
        service.delete(category)
    except ValueError as exc:
        raise HTTPException(status_code=400, detail=str(exc)) from exc
    return MessageOut(detail="Categoria excluída.")
