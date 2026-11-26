package com.nossouhub.app.data.model

data class HomeDto(
    val app_name: String,
    val since: String,
    val timezone: String,
)

data class MessageDto(val detail: String)

data class EventMediaDto(
    val id: Int,
    val event_id: Int,
    val path: String,
    val original_name: String? = null,
    val mime_type: String? = null,
    val size: Long? = null,
    val type: String,
    val sort_order: Int = 0,
    val url: String,
)

data class EventDto(
    val id: Int,
    val title: String,
    val description: String? = null,
    val occurred_at: String,
    val created_at: String? = null,
    val updated_at: String? = null,
    val media: List<EventMediaDto> = emptyList(),
)

data class EventDetailDto(
    val event: EventDto,
    val photos: List<EventMediaDto> = emptyList(),
    val videos: List<EventMediaDto> = emptyList(),
)

data class TimelineItemDto(
    val event: EventDto,
    val spacer_px: Int = 0,
)

data class PlanCategoryDto(
    val id: Int,
    val name: String,
    val slug: String,
    val sort_order: Int = 0,
    val plans_count: Int = 0,
)

data class PlanDto(
    val id: Int,
    val title: String,
    val description: String? = null,
    val plan_category_id: Int,
    val priority: String,
    val status: String,
    val link: String? = null,
    val notes: String? = null,
    val created_at: String? = null,
    val updated_at: String? = null,
    val category: PlanCategoryDto? = null,
)

data class PlanWriteDto(
    val title: String,
    val description: String? = null,
    val plan_category_id: Int,
    val priority: String = "medium",
    val status: String = "pending",
    val link: String? = null,
    val notes: String? = null,
)

data class PlanCategoryCreateDto(val name: String)

data class AppointmentDto(
    val id: Int,
    val summary: String,
    val description: String? = null,
    val location: String? = null,
    val starts_at: String,
    val ends_at: String,
    val all_day: Boolean = false,
    val timezone: String = "America/Sao_Paulo",
    val status: String = "confirmed",
    val visibility: String = "default",
    val transparency: String = "opaque",
    val created_at: String? = null,
    val updated_at: String? = null,
    val google_url: String? = null,
    val ics_url: String? = null,
)

data class AppointmentWriteDto(
    val summary: String,
    val description: String? = null,
    val location: String? = null,
    val starts_at: String,
    val ends_at: String,
    val all_day: Boolean = false,
    val timezone: String = "America/Sao_Paulo",
    val status: String = "confirmed",
    val visibility: String = "default",
    val transparency: String = "opaque",
)

data class CalendarEmailDto(
    val id: Int,
    val email: String,
    val label: String? = null,
    val is_active: Boolean = true,
)

data class CalendarEmailCreateDto(
    val email: String,
    val label: String? = null,
    val is_active: Boolean = true,
)

data class GalleryAssetDto(
    val id: String,
    val type: String,
    val thumbnail: String,
    val preview: String,
    val original: String,
)

data class GalleryAlbumDto(
    val id: String,
    val name: String,
)

data class GalleryDto(
    val configured: Boolean,
    val assets: List<GalleryAssetDto> = emptyList(),
    val albums: List<GalleryAlbumDto> = emptyList(),
    val page: Int = 1,
    val has_more: Boolean = false,
    val error: String? = null,
)
