from fastapi import APIRouter

router = APIRouter(tags=["api-health"])


@router.get("/health")
def api_health():
    return {"status": "ok"}
