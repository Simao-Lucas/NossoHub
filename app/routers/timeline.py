from fastapi import APIRouter, Depends, Request
from fastapi.responses import HTMLResponse
from sqlalchemy.orm import Session

from app.db import get_db
from app.deps import template_context, templates
from app.policies.event_policy import EventPolicy
from app.services.event_service import EventService

router = APIRouter(tags=["timeline"])
PX_PER_DAY = 4


@router.get("/linha-do-tempo", response_class=HTMLResponse)
def timeline(request: Request, db: Session = Depends(get_db)):
    policy = EventPolicy()
    if not policy.view_any():
        return HTMLResponse("Forbidden", status_code=403)

    events = EventService(db).timeline(ascending=True)
    items = []
    previous = None
    for event in events:
        spacer_px = 0
        if previous is not None:
            gap_days = (event.occurred_at - previous.occurred_at).days
            spacer_px = max(0, gap_days) * PX_PER_DAY
        items.append({"event": event, "spacer_px": spacer_px})
        previous = event

    return templates.TemplateResponse(
        "timeline/index.html",
        template_context(request, items=items, active="timeline"),
    )
