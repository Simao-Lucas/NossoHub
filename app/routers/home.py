from fastapi import APIRouter, Request
from fastapi.responses import HTMLResponse

from app.config import get_settings
from app.deps import template_context, templates

router = APIRouter(tags=["home"])


@router.get("/", response_class=HTMLResponse)
def home(request: Request):
    settings = get_settings()
    return templates.TemplateResponse(
        "home/index.html",
        template_context(request, since=settings.relationship_since, hide_navbar=True),
    )
