from __future__ import annotations

from pydantic import BaseModel, Field


class GalleryAssetOut(BaseModel):
    id: str
    type: str
    thumbnail: str
    preview: str
    original: str


class GalleryAlbumOut(BaseModel):
    id: str
    name: str


class GalleryOut(BaseModel):
    configured: bool
    assets: list[GalleryAssetOut] = Field(default_factory=list)
    albums: list[GalleryAlbumOut] = Field(default_factory=list)
    page: int = 1
    has_more: bool = False
    error: str | None = None
