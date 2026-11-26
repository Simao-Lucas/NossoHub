package com.nossouhub.app.data.api

import com.nossouhub.app.data.model.AppointmentDto
import com.nossouhub.app.data.model.AppointmentWriteDto
import com.nossouhub.app.data.model.CalendarEmailCreateDto
import com.nossouhub.app.data.model.CalendarEmailDto
import com.nossouhub.app.data.model.EventDetailDto
import com.nossouhub.app.data.model.EventDto
import com.nossouhub.app.data.model.GalleryDto
import com.nossouhub.app.data.model.HomeDto
import com.nossouhub.app.data.model.MessageDto
import com.nossouhub.app.data.model.PlanCategoryCreateDto
import com.nossouhub.app.data.model.PlanCategoryDto
import com.nossouhub.app.data.model.PlanDto
import com.nossouhub.app.data.model.PlanWriteDto
import com.nossouhub.app.data.model.TimelineItemDto
import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.http.Body
import retrofit2.http.DELETE
import retrofit2.http.GET
import retrofit2.http.Multipart
import retrofit2.http.PATCH
import retrofit2.http.POST
import retrofit2.http.PUT
import retrofit2.http.Part
import retrofit2.http.Path
import retrofit2.http.Query

interface NossoHubApi {
    @GET("api/health")
    suspend fun health(): Map<String, String>

    @GET("api/home")
    suspend fun home(): HomeDto

    @GET("api/timeline")
    suspend fun timeline(): List<TimelineItemDto>

    @GET("api/events")
    suspend fun events(): List<EventDto>

    @GET("api/events/{id}")
    suspend fun event(@Path("id") id: Int): EventDetailDto

    @Multipart
    @POST("api/events")
    suspend fun createEvent(
        @Part("title") title: RequestBody,
        @Part("description") description: RequestBody,
        @Part("occurred_at") occurredAt: RequestBody,
        @Part uploads: List<MultipartBody.Part>?,
    ): EventDto

    @Multipart
    @PUT("api/events/{id}")
    suspend fun updateEvent(
        @Path("id") id: Int,
        @Part("title") title: RequestBody,
        @Part("description") description: RequestBody,
        @Part("occurred_at") occurredAt: RequestBody,
        @Part("removed_media_ids") removedMediaIds: List<RequestBody>?,
        @Part uploads: List<MultipartBody.Part>?,
    ): EventDto

    @DELETE("api/events/{id}")
    suspend fun deleteEvent(@Path("id") id: Int): MessageDto

    @GET("api/plans")
    suspend fun plans(
        @Query("search") search: String? = null,
        @Query("category") category: Int? = null,
        @Query("status") status: String? = null,
        @Query("priority") priority: String? = null,
    ): List<PlanDto>

    @GET("api/plans/{id}")
    suspend fun plan(@Path("id") id: Int): PlanDto

    @POST("api/plans")
    suspend fun createPlan(@Body body: PlanWriteDto): PlanDto

    @PUT("api/plans/{id}")
    suspend fun updatePlan(@Path("id") id: Int, @Body body: PlanWriteDto): PlanDto

    @DELETE("api/plans/{id}")
    suspend fun deletePlan(@Path("id") id: Int): MessageDto

    @GET("api/plan-categories")
    suspend fun planCategories(): List<PlanCategoryDto>

    @POST("api/plan-categories")
    suspend fun createPlanCategory(@Body body: PlanCategoryCreateDto): PlanCategoryDto

    @DELETE("api/plan-categories/{id}")
    suspend fun deletePlanCategory(@Path("id") id: Int): MessageDto

    @GET("api/appointments")
    suspend fun appointments(
        @Query("year") year: Int? = null,
        @Query("month") month: Int? = null,
    ): List<AppointmentDto>

    @GET("api/appointments/{id}")
    suspend fun appointment(@Path("id") id: Int): AppointmentDto

    @POST("api/appointments")
    suspend fun createAppointment(@Body body: AppointmentWriteDto): AppointmentDto

    @PUT("api/appointments/{id}")
    suspend fun updateAppointment(@Path("id") id: Int, @Body body: AppointmentWriteDto): AppointmentDto

    @DELETE("api/appointments/{id}")
    suspend fun deleteAppointment(@Path("id") id: Int): MessageDto

    @GET("api/calendar-emails")
    suspend fun calendarEmails(): List<CalendarEmailDto>

    @POST("api/calendar-emails")
    suspend fun createCalendarEmail(@Body body: CalendarEmailCreateDto): CalendarEmailDto

    @PATCH("api/calendar-emails/{id}/toggle")
    suspend fun toggleCalendarEmail(@Path("id") id: Int): CalendarEmailDto

    @DELETE("api/calendar-emails/{id}")
    suspend fun deleteCalendarEmail(@Path("id") id: Int): MessageDto

    @GET("api/gallery")
    suspend fun gallery(
        @Query("q") q: String? = null,
        @Query("album") album: String? = null,
        @Query("taken_after") takenAfter: String? = null,
        @Query("taken_before") takenBefore: String? = null,
        @Query("page") page: Int = 1,
    ): GalleryDto
}
