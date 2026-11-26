from fastapi import APIRouter

from . import (
    appointments,
    calendar_emails,
    events,
    gallery,
    health,
    home,
    plans,
    timeline,
)

api_router = APIRouter(prefix="/api")
api_router.include_router(health.router)
api_router.include_router(home.router)
api_router.include_router(events.router)
api_router.include_router(timeline.router)
api_router.include_router(plans.router)
api_router.include_router(appointments.router)
api_router.include_router(calendar_emails.router)
api_router.include_router(gallery.router)
