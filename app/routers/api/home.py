from fastapi import APIRouter

from app.config import get_settings
from app.schemas.home import HomeOut

router = APIRouter(tags=["api-home"])


@router.get("/home", response_model=HomeOut)
def api_home():
    settings = get_settings()
    return HomeOut(
        app_name=settings.app_name,
        since=settings.relationship_since,
        timezone=settings.app_timezone,
    )
