from __future__ import annotations

import logging
from typing import Any

import httpx

from app.config import Settings, get_settings

logger = logging.getLogger(__name__)


class ImmichService:
    def __init__(self, settings: Settings | None = None):
        self.settings = settings or get_settings()
        self.last_error: str | None = None

    def is_configured(self) -> bool:
        return bool(self.settings.immich_base and self.api_key)

    @property
    def api_key(self) -> str:
        return (self.settings.immich_api_key or "").strip().strip('"').strip("'")

    def list_assets(self, page: int = 1, size: int = 50) -> list[dict[str, Any]]:
        return self.search_assets({}, page=page, size=size)

    def search_assets(
        self,
        filters: dict[str, Any] | None = None,
        page: int = 1,
        size: int = 50,
    ) -> list[dict[str, Any]]:
        filters = filters or {}
        payload: dict[str, Any] = {
            "page": page,
            "size": size,
            "withExif": True,
            # Immich recente: restringe à timeline por padrão em alguns fluxos
            "visibility": "timeline",
        }
        if filters.get("query"):
            payload["query"] = filters["query"]
        if filters.get("takenAfter"):
            payload["takenAfter"] = filters["takenAfter"]
        if filters.get("takenBefore"):
            payload["takenBefore"] = filters["takenBefore"]
        if filters.get("albumIds"):
            payload["albumIds"] = list(filters["albumIds"])

        endpoint = (
            "/api/search/smart" if filters.get("query") else "/api/search/metadata"
        )
        data = self._post_json(endpoint, payload)

        # Fallback sem visibility (Immich mais antigo rejeita o campo)
        if data is None and self.last_error and "visibility" in (self.last_error or "").lower():
            payload.pop("visibility", None)
            data = self._post_json(endpoint, payload)
        elif data is None and endpoint.endswith("/metadata"):
            # Segunda tentativa: payload mínimo (compat)
            minimal = {"page": page, "size": size}
            data = self._post_json(endpoint, minimal)

        return self._extract_assets(data)

    def get_asset_by_id(self, asset_id: str) -> dict[str, Any] | None:
        if not asset_id:
            return None
        return self._get_json(f"/api/assets/{asset_id}")

    def list_albums(self) -> list[dict[str, Any]]:
        data = self._get_json("/api/albums")
        return data if isinstance(data, list) else []

    def ping(self) -> dict[str, Any] | None:
        """Cheap connectivity check used by the gallery diagnostics."""
        return self._get_json("/api/server/about") or self._get_json("/api/server-info/version")

    def fetch_thumbnail(self, asset_id: str, size: str = "thumbnail") -> tuple[bytes, str]:
        url = f"/api/assets/{asset_id}/thumbnail"
        return self._get_bytes(url, params={"size": size})

    def fetch_original(self, asset_id: str) -> tuple[bytes, str]:
        return self._get_bytes(f"/api/assets/{asset_id}/original")

    def app_thumbnail_url(self, asset_id: str, size: str = "thumbnail") -> str:
        return f"/immich/assets/{asset_id}/thumbnail?size={size}"

    def app_original_url(self, asset_id: str) -> str:
        return f"/immich/assets/{asset_id}/original"

    def _client(self) -> httpx.Client:
        return httpx.Client(
            base_url=self.settings.immich_base,
            timeout=self.settings.immich_timeout,
            headers={
                "x-api-key": self.api_key,
                "Accept": "application/json",
                "Content-Type": "application/json",
            },
            follow_redirects=True,
        )

    def _post_json(self, path: str, payload: dict) -> Any:
        self.last_error = None
        if not self.is_configured():
            self.last_error = "Immich não está configurado (IMMICH_BASE_URL / IMMICH_API_KEY)."
            logger.warning(self.last_error)
            return None
        try:
            with self._client() as client:
                response = client.post(path, json=payload)
                if response.is_success:
                    return response.json()
                self.last_error = (
                    f"POST {path} → HTTP {response.status_code}: {response.text[:300]}"
                )
                self._log_failure(path, response.status_code, response.text)
        except Exception as exc:
            self.last_error = (
                f"Falha ao conectar em {self.settings.immich_base}{path}: {exc}"
            )
            logger.warning("Immich request failed (%s): %s", path, exc)
        return None

    def _get_json(self, path: str) -> Any:
        self.last_error = None
        if not self.is_configured():
            self.last_error = "Immich não está configurado (IMMICH_BASE_URL / IMMICH_API_KEY)."
            logger.warning(self.last_error)
            return None
        try:
            with self._client() as client:
                response = client.get(path)
                if response.is_success:
                    return response.json()
                self.last_error = (
                    f"GET {path} → HTTP {response.status_code}: {response.text[:300]}"
                )
                self._log_failure(path, response.status_code, response.text)
        except Exception as exc:
            self.last_error = (
                f"Falha ao conectar em {self.settings.immich_base}{path}: {exc}"
            )
            logger.warning("Immich request failed (%s): %s", path, exc)
        return None

    def _get_bytes(self, path: str, params: dict | None = None) -> tuple[bytes, str]:
        if not self.is_configured():
            raise RuntimeError("Immich is not configured.")
        with self._client() as client:
            response = client.get(path, params=params)
            if not response.is_success:
                raise RuntimeError(f"Immich fetch failed: {response.status_code}")
            content_type = response.headers.get("content-type", "application/octet-stream")
            return response.content, content_type

    @staticmethod
    def _extract_assets(payload: Any) -> list[dict[str, Any]]:
        if payload is None:
            return []
        if isinstance(payload, list):
            return [item for item in payload if isinstance(item, dict)]
        if not isinstance(payload, dict):
            return []

        assets = payload.get("assets")
        if isinstance(assets, dict) and isinstance(assets.get("items"), list):
            return [item for item in assets["items"] if isinstance(item, dict)]
        if isinstance(payload.get("items"), list):
            return [item for item in payload["items"] if isinstance(item, dict)]
        if isinstance(assets, list):
            return [item for item in assets if isinstance(item, dict)]
        return []

    @staticmethod
    def _log_failure(action: str, status: int, body: str) -> None:
        logger.warning("Immich %s failed (%s): %s", action, status, body[:300])
