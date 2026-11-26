from fastapi import APIRouter, Depends, Form, Request
from fastapi.responses import HTMLResponse
from sqlalchemy.orm import Session

from app.db import get_db
from app.deps import redirect_with_flash, template_context, templates
from app.models.enums import PlanPriority, PlanStatus
from app.policies.plan_policy import PlanItemPolicy
from app.services.plan_category_service import PlanCategoryService
from app.services.plan_service import PlanService

router = APIRouter(prefix="/planos", tags=["plans"])


@router.get("", response_class=HTMLResponse)
def index(
    request: Request,
    search: str = "",
    category: str = "",
    status: str = "",
    priority: str = "",
    db: Session = Depends(get_db),
):
    if not PlanItemPolicy().view_any():
        return HTMLResponse("Forbidden", status_code=403)
    filters = {
        "search": search or None,
        "category": category or None,
        "status": status or None,
        "priority": priority or None,
    }
    plans = PlanService(db).list(filters)
    categories = PlanCategoryService(db).options()
    return templates.TemplateResponse(
        "plans/index.html",
        template_context(
            request,
            plans=plans,
            categories=categories,
            filters=filters,
            statuses=PlanStatus.options(),
            priorities=PlanPriority.options(),
            active="plans",
        ),
    )


@router.get("/criar", response_class=HTMLResponse)
def create_form(request: Request, db: Session = Depends(get_db)):
    if not PlanItemPolicy().create():
        return HTMLResponse("Forbidden", status_code=403)
    categories = PlanCategoryService(db).options()
    return templates.TemplateResponse(
        "plans/form.html",
        template_context(
            request,
            plan=None,
            categories=categories,
            statuses=PlanStatus.options(),
            priorities=PlanPriority.options(),
            active="plans",
        ),
    )


@router.post("/criar")
def create_submit(
    request: Request,
    title: str = Form(...),
    description: str = Form(""),
    plan_category_id: int = Form(...),
    priority: str = Form("medium"),
    status: str = Form("pending"),
    link: str = Form(""),
    notes: str = Form(""),
    db: Session = Depends(get_db),
):
    if not PlanItemPolicy().create():
        return HTMLResponse("Forbidden", status_code=403)
    item = PlanService(db).create(
        {
            "title": title,
            "description": description,
            "plan_category_id": plan_category_id,
            "priority": priority,
            "status": status,
            "link": link,
            "notes": notes,
        }
    )
    return redirect_with_flash(f"/planos/{item.id}", request, "Plano criado.")


@router.get("/categorias", response_class=HTMLResponse)
def categories(request: Request, db: Session = Depends(get_db)):
    rows = PlanCategoryService(db).list()
    return templates.TemplateResponse(
        "plans/categories.html",
        template_context(request, categories=rows, active="plans"),
    )


@router.post("/categorias")
def categories_create(
    request: Request,
    name: str = Form(...),
    db: Session = Depends(get_db),
):
    try:
        PlanCategoryService(db).create(name)
        return redirect_with_flash("/planos/categorias", request, "Categoria criada.")
    except ValueError as exc:
        rows = PlanCategoryService(db).list()
        return templates.TemplateResponse(
            "plans/categories.html",
            template_context(request, categories=rows, error=str(exc), active="plans"),
            status_code=400,
        )


@router.post("/categorias/{category_id}/excluir")
def categories_delete(category_id: int, request: Request, db: Session = Depends(get_db)):
    service = PlanCategoryService(db)
    category = service.find(category_id)
    if category is None:
        return HTMLResponse("Categoria não encontrada", status_code=404)
    try:
        service.delete(category)
        return redirect_with_flash("/planos/categorias", request, "Categoria excluída.")
    except ValueError as exc:
        return redirect_with_flash("/planos/categorias", request, str(exc), "error")


@router.get("/{plan_id}", response_class=HTMLResponse)
def show(plan_id: int, request: Request, db: Session = Depends(get_db)):
    item = PlanService(db).find(plan_id)
    if item is None:
        return HTMLResponse("Plano não encontrado", status_code=404)
    if not PlanItemPolicy().view(item=item):
        return HTMLResponse("Forbidden", status_code=403)
    return templates.TemplateResponse(
        "plans/show.html",
        template_context(request, plan=item, active="plans"),
    )


@router.get("/{plan_id}/editar", response_class=HTMLResponse)
def edit_form(plan_id: int, request: Request, db: Session = Depends(get_db)):
    item = PlanService(db).find(plan_id)
    if item is None:
        return HTMLResponse("Plano não encontrado", status_code=404)
    if not PlanItemPolicy().update(item=item):
        return HTMLResponse("Forbidden", status_code=403)
    categories = PlanCategoryService(db).options()
    return templates.TemplateResponse(
        "plans/form.html",
        template_context(
            request,
            plan=item,
            categories=categories,
            statuses=PlanStatus.options(),
            priorities=PlanPriority.options(),
            active="plans",
        ),
    )


@router.post("/{plan_id}/editar")
def edit_submit(
    plan_id: int,
    request: Request,
    title: str = Form(...),
    description: str = Form(""),
    plan_category_id: int = Form(...),
    priority: str = Form("medium"),
    status: str = Form("pending"),
    link: str = Form(""),
    notes: str = Form(""),
    db: Session = Depends(get_db),
):
    service = PlanService(db)
    item = service.find(plan_id)
    if item is None:
        return HTMLResponse("Plano não encontrado", status_code=404)
    if not PlanItemPolicy().update(item=item):
        return HTMLResponse("Forbidden", status_code=403)
    item = service.update(
        item,
        {
            "title": title,
            "description": description,
            "plan_category_id": plan_category_id,
            "priority": priority,
            "status": status,
            "link": link,
            "notes": notes,
        },
    )
    return redirect_with_flash(f"/planos/{item.id}", request, "Plano atualizado.")


@router.post("/{plan_id}/excluir")
def destroy(plan_id: int, request: Request, db: Session = Depends(get_db)):
    service = PlanService(db)
    item = service.find(plan_id)
    if item is None:
        return HTMLResponse("Plano não encontrado", status_code=404)
    if not PlanItemPolicy().delete(item=item):
        return HTMLResponse("Forbidden", status_code=403)
    service.delete(item)
    return redirect_with_flash("/planos", request, "Plano excluído.")
