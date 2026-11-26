from __future__ import annotations

from pydantic import BaseModel


class HomeOut(BaseModel):
    app_name: str
    since: str
    timezone: str
