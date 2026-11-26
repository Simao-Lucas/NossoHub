from __future__ import annotations

import re
import unicodedata
from datetime import datetime, timedelta, timezone
from urllib.parse import urlencode, urlparse

from sqlalchemy.orm import Session

from app.config import get_settings
from app.models import Appointment
from app.repositories.calendar_email_repository import CalendarEmailRepository


class GoogleCalendarExportService:
    def __init__(self, db: Session):
        self.emails = CalendarEmailRepository(db)
        self.settings = get_settings()

    def template_url(self, appointment: Appointment) -> str:
        params = {
            "action": "TEMPLATE",
            "text": appointment.summary,
            "details": appointment.description or "",
            "location": appointment.location or "",
            "dates": self._google_dates(appointment),
        }
        attendees = ",".join(
            e.email for e in self.emails.active_emails() if e.email
        )
        if attendees:
            params["add"] = attendees
        return "https://calendar.google.com/calendar/render?" + urlencode(params)

    def ics(self, appointment: Appointment) -> str:
        host = urlparse(self.settings.app_url).hostname or "localhost"
        uid = f"appointment-{appointment.id}@{host}"
        stamp = datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")
        summary = self._escape_ics(appointment.summary or "")
        description = self._escape_ics(appointment.description or "")
        location = self._escape_ics(appointment.location or "")
        status = (appointment.status or "confirmed").upper()
        transp = (appointment.transparency or "opaque").upper()
        visibility = appointment.visibility or "default"
        class_value = {
            "public": "PUBLIC",
            "private": "PRIVATE",
        }.get(visibility, "PUBLIC")

        if appointment.all_day:
            dt_start = "DTSTART;VALUE=DATE:" + appointment.starts_at.strftime("%Y%m%d")
            dt_end = "DTEND;VALUE=DATE:" + (
                appointment.ends_at + timedelta(days=1)
            ).strftime("%Y%m%d")
        else:
            start = self._as_utc(appointment.starts_at)
            end = self._as_utc(appointment.ends_at)
            dt_start = "DTSTART:" + start.strftime("%Y%m%dT%H%M%SZ")
            dt_end = "DTEND:" + end.strftime("%Y%m%dT%H%M%SZ")

        lines = [
            "BEGIN:VCALENDAR",
            "VERSION:2.0",
            "PRODID:-//Nosso Hub//Calendar//PT",
            "CALSCALE:GREGORIAN",
            "METHOD:PUBLISH",
            "BEGIN:VEVENT",
            f"UID:{uid}",
            f"DTSTAMP:{stamp}",
            dt_start,
            dt_end,
            f"SUMMARY:{summary}",
            f"DESCRIPTION:{description}",
            f"LOCATION:{location}",
            f"STATUS:{status}",
            f"TRANSP:{transp}",
            f"CLASS:{class_value}",
            "END:VEVENT",
            "END:VCALENDAR",
        ]
        return "\r\n".join(lines) + "\r\n"

    def ics_filename(self, appointment: Appointment) -> str:
        slug = self._slugify(appointment.summary or "") or "compromisso"
        return f"{slug}-{appointment.id}.ics"

    def _google_dates(self, appointment: Appointment) -> str:
        if appointment.all_day:
            start = appointment.starts_at.strftime("%Y%m%d")
            end = (appointment.ends_at + timedelta(days=1)).strftime("%Y%m%d")
            return f"{start}/{end}"
        start = self._as_utc(appointment.starts_at).strftime("%Y%m%dT%H%M%SZ")
        end = self._as_utc(appointment.ends_at).strftime("%Y%m%dT%H%M%SZ")
        return f"{start}/{end}"

    @staticmethod
    def _as_utc(value: datetime) -> datetime:
        if value.tzinfo is None:
            # Stored as America/Sao_Paulo wall time without tz in MariaDB.
            from zoneinfo import ZoneInfo

            value = value.replace(tzinfo=ZoneInfo("America/Sao_Paulo"))
        return value.astimezone(timezone.utc)

    @staticmethod
    def _escape_ics(value: str) -> str:
        return (
            value.replace("\\", "\\\\")
            .replace(";", "\\;")
            .replace(",", "\\,")
            .replace("\n", "\\n")
            .replace("\r", "")
        )

    @staticmethod
    def _slugify(value: str) -> str:
        normalized = unicodedata.normalize("NFKD", value)
        ascii_text = normalized.encode("ascii", "ignore").decode("ascii")
        return re.sub(r"[^a-z0-9]+", "-", ascii_text.lower()).strip("-")
