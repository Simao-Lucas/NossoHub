from functools import lru_cache
from pathlib import Path

from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(
        env_file=".env",
        env_file_encoding="utf-8",
        extra="ignore",
    )

    app_name: str = "Nosso Hub"
    app_url: str = "http://localhost:2807"
    app_debug: bool = True
    app_timezone: str = "America/Sao_Paulo"

    db_host: str = "mariadb"
    db_port: int = 3306
    db_database: str = "nosso_hub"
    db_username: str = "nosso_hub"
    db_password: str = "secret"

    media_root: Path = Path("/app/media")

    immich_base_url: str = "http://immich.local:2283"
    immich_api_key: str = ""
    immich_timeout: int = 30

    relationship_since: str = "2025-07-28T20:30:00-03:00"

    @property
    def database_url(self) -> str:
        return (
            f"mysql+pymysql://{self.db_username}:{self.db_password}"
            f"@{self.db_host}:{self.db_port}/{self.db_database}"
            "?charset=utf8mb4"
        )

    @property
    def immich_base(self) -> str:
        return self.immich_base_url.rstrip("/")


@lru_cache
def get_settings() -> Settings:
    return Settings()
