from __future__ import annotations

import shutil
import uuid
from pathlib import Path

from fastapi import UploadFile
from sqlalchemy.orm import Session

from app.config import get_settings
from app.models import Event, EventMedia
from app.models.enums import MediaType
from app.repositories.event_repository import EventRepository

ALLOWED_IMAGE = {"image/jpeg", "image/png", "image/webp", "image/gif"}
ALLOWED_VIDEO = {"video/mp4", "video/quicktime", "video/webm"}
ALLOWED_MIME = ALLOWED_IMAGE | ALLOWED_VIDEO
MAX_UPLOADS = 40
MAX_BYTES = 102400 * 1024  # 100 MB


class EventService:
    def __init__(self, db: Session):
        self.db = db
        self.repo = EventRepository(db)
        self.settings = get_settings()

    def timeline(self, ascending: bool = False) -> list[Event]:
        return self.repo.all_chronological(ascending=ascending)

    def find(self, event_id: int) -> Event | None:
        return self.repo.find(event_id)

    def create(
        self,
        *,
        title: str,
        description: str | None,
        occurred_at,
        uploads: list[UploadFile] | None = None,
    ) -> Event:
        event = self.repo.create(
            {
                "title": title.strip(),
                "description": (description or "").strip() or None,
                "occurred_at": occurred_at,
            }
        )
        if uploads:
            self._store_uploads(event, uploads)
        self.db.commit()
        self.db.refresh(event)
        return self.repo.find(event.id) or event

    def update(
        self,
        event: Event,
        *,
        title: str,
        description: str | None,
        occurred_at,
        uploads: list[UploadFile] | None = None,
        removed_media_ids: list[int] | None = None,
    ) -> Event:
        self.repo.update(
            event,
            {
                "title": title.strip(),
                "description": (description or "").strip() or None,
                "occurred_at": occurred_at,
            },
        )
        if removed_media_ids:
            self._remove_media(event, list(dict.fromkeys(removed_media_ids)))
        if uploads:
            self._store_uploads(event, uploads)
        self.db.commit()
        return self.repo.find(event.id) or event

    def delete(self, event: Event) -> None:
        event_id = event.id
        for media in list(event.media):
            self._delete_file(media)
        self.repo.delete(event)
        event_dir = self.settings.media_root / "events" / str(event_id)
        if event_dir.exists():
            shutil.rmtree(event_dir, ignore_errors=True)
        self.db.commit()

    def present(self, event: Event) -> dict:
        photos = []
        videos = []
        for media in event.media:
            item = {
                "id": media.id,
                "type": media.type,
                "url": f"/midia/{media.id}",
                "original_name": media.original_name,
                "mime_type": media.mime_type,
            }
            if media.type == MediaType.PHOTO:
                photos.append(item)
            else:
                videos.append(item)
        return {"event": event, "photos": photos, "videos": videos}

    def absolute_path(self, media: EventMedia) -> Path:
        return self.settings.media_root / media.path

    def _store_uploads(self, event: Event, uploads: list[UploadFile]) -> None:
        files = [u for u in uploads if u and u.filename]
        if len(files) > MAX_UPLOADS:
            raise ValueError(f"Máximo de {MAX_UPLOADS} arquivos por vez.")

        dest_dir = self.settings.media_root / "events" / str(event.id)
        dest_dir.mkdir(parents=True, exist_ok=True)
        sort_order = self.repo.next_sort_order(event.id)

        for upload in files:
            content_type = (upload.content_type or "").lower()
            if content_type not in ALLOWED_MIME:
                raise ValueError(f"Tipo não permitido: {content_type or upload.filename}")

            data = upload.file.read()
            if len(data) > MAX_BYTES:
                raise ValueError(f"Arquivo muito grande: {upload.filename}")

            ext = Path(upload.filename or "file").suffix.lower() or self._ext_for(content_type)
            filename = f"{uuid.uuid4().hex}{ext}"
            relative = f"events/{event.id}/{filename}"
            absolute = self.settings.media_root / relative
            absolute.write_bytes(data)

            media_type = (
                MediaType.VIDEO if content_type in ALLOWED_VIDEO else MediaType.PHOTO
            )
            self.repo.add_media(
                event,
                {
                    "path": relative.replace("\\", "/"),
                    "disk": "public",
                    "original_name": upload.filename,
                    "mime_type": content_type,
                    "size": len(data),
                    "type": media_type.value,
                    "sort_order": sort_order,
                },
            )
            sort_order += 1

    def _remove_media(self, event: Event, ids: list[int]) -> None:
        for media in self.repo.media_by_ids(event, ids):
            self._delete_file(media)
            self.db.delete(media)
        self.db.flush()

    def _delete_file(self, media: EventMedia) -> None:
        path = self.absolute_path(media)
        if path.exists():
            path.unlink(missing_ok=True)

    @staticmethod
    def _ext_for(content_type: str) -> str:
        return {
            "image/jpeg": ".jpg",
            "image/png": ".png",
            "image/webp": ".webp",
            "image/gif": ".gif",
            "video/mp4": ".mp4",
            "video/quicktime": ".mov",
            "video/webm": ".webm",
        }.get(content_type, "")
