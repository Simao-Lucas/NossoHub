from enum import StrEnum


class MediaType(StrEnum):
    PHOTO = "photo"
    VIDEO = "video"

    @property
    def label(self) -> str:
        return {"photo": "Foto", "video": "Vídeo"}[self.value]


class PlanStatus(StrEnum):
    PENDING = "pending"
    IN_PROGRESS = "in_progress"
    COMPLETED = "completed"

    @property
    def label(self) -> str:
        return {
            "pending": "Pendente",
            "in_progress": "Em andamento",
            "completed": "Concluído",
        }[self.value]

    @classmethod
    def options(cls) -> dict[str, str]:
        return {item.value: item.label for item in cls}


class PlanPriority(StrEnum):
    LOW = "low"
    MEDIUM = "medium"
    HIGH = "high"

    @property
    def label(self) -> str:
        return {"low": "Baixa", "medium": "Média", "high": "Alta"}[self.value]

    @classmethod
    def options(cls) -> dict[str, str]:
        return {item.value: item.label for item in cls}


class AppointmentStatus(StrEnum):
    CONFIRMED = "confirmed"
    TENTATIVE = "tentative"
    CANCELLED = "cancelled"

    @property
    def label(self) -> str:
        return {
            "confirmed": "Confirmado",
            "tentative": "Provisório",
            "cancelled": "Cancelado",
        }[self.value]

    @classmethod
    def options(cls) -> dict[str, str]:
        return {item.value: item.label for item in cls}


class AppointmentVisibility(StrEnum):
    DEFAULT = "default"
    PUBLIC = "public"
    PRIVATE = "private"

    @property
    def label(self) -> str:
        return {
            "default": "Padrão",
            "public": "Público",
            "private": "Privado",
        }[self.value]

    @classmethod
    def options(cls) -> dict[str, str]:
        return {item.value: item.label for item in cls}


class AppointmentTransparency(StrEnum):
    OPAQUE = "opaque"
    TRANSPARENT = "transparent"

    @property
    def label(self) -> str:
        return {"opaque": "Ocupado", "transparent": "Livre"}[self.value]

    @classmethod
    def options(cls) -> dict[str, str]:
        return {item.value: item.label for item in cls}
