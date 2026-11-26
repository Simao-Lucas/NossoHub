from fastapi import Request
from fastapi.templating import Jinja2Templates
from starlette.responses import RedirectResponse

from app.config import get_settings

templates = Jinja2Templates(directory="app/templates")
settings = get_settings()


def flash(request: Request, message: str, category: str = "success") -> None:
    request.session.setdefault("_flashes", []).append({"message": message, "category": category})


def get_flashes(request: Request) -> list[dict]:
    return request.session.pop("_flashes", [])


def redirect_with_flash(url: str, request: Request, message: str, category: str = "success"):
    flash(request, message, category)
    return RedirectResponse(url=url, status_code=303)


def template_context(request: Request, **extra):
    return {
        "request": request,
        "app_name": settings.app_name,
        "flashes": get_flashes(request),
        **extra,
    }
