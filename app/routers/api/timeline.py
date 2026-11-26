from fastapi import APIRouter, Depends
from sqlalchemy.orm import Session

from app.db import get_db
from app.policies.event_policy import EventPolicy
from app.routers.api.serializers import event_out
from app.schemas.event import TimelineItemOut
from app.services.event_service import EventService

router = APIRouter(tags=["api-timeline"])
PX_PER_DAY = 4


@router.get("/timeline", response_model=list[TimelineItemOut])
def api_timeline(db: Session = Depends(get_db)):
    if not EventPolicy().view_any():
        return []
    events = EventService(db).timeline(ascending=True)
    items: list[TimelineItemOut] = []
    previous = None
    for event in events:
        spacer_px = 0
        if previous is not None:
            gap_days = (event.occurred_at - previous.occurred_at).days
            spacer_px = max(0, gap_days) * PX_PER_DAY
        items.append(TimelineItemOut(event=event_out(event), spacer_px=spacer_px))
        previous = event
    return items
