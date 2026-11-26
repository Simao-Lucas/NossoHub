package com.nossouhub.app.data.api

import com.squareup.moshi.Moshi
import com.squareup.moshi.kotlin.reflect.KotlinJsonAdapterFactory
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.moshi.MoshiConverterFactory
import java.util.concurrent.TimeUnit

object ApiClient {
    @Volatile
    private var api: NossoHubApi? = null

    @Volatile
    private var baseUrl: String = ""

    fun get(baseUrl: String): NossoHubApi {
        val normalized = normalizeBaseUrl(baseUrl)
        val current = api
        if (current != null && this.baseUrl == normalized) return current
        synchronized(this) {
            if (api != null && this.baseUrl == normalized) return api!!
            this.baseUrl = normalized
            api = build(normalized)
            return api!!
        }
    }

    fun currentBaseUrl(): String = baseUrl

    fun absoluteUrl(path: String): String {
        if (path.startsWith("http://") || path.startsWith("https://")) return path
        val base = baseUrl.trimEnd('/')
        return if (path.startsWith("/")) "$base$path" else "$base/$path"
    }

    fun reset() {
        synchronized(this) {
            api = null
            baseUrl = ""
        }
    }

    private fun build(baseUrl: String): NossoHubApi {
        val logging = HttpLoggingInterceptor().apply {
            level = HttpLoggingInterceptor.Level.BASIC
        }
        val client = OkHttpClient.Builder()
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(60, TimeUnit.SECONDS)
            .writeTimeout(60, TimeUnit.SECONDS)
            .addInterceptor(logging)
            .build()
        val moshi = Moshi.Builder()
            .add(KotlinJsonAdapterFactory())
            .build()
        return Retrofit.Builder()
            .baseUrl(baseUrl)
            .client(client)
            .addConverterFactory(MoshiConverterFactory.create(moshi))
            .build()
            .create(NossoHubApi::class.java)
    }

    fun normalizeBaseUrl(raw: String): String {
        var value = raw.trim().trimEnd('/')
        if (!value.startsWith("http://") && !value.startsWith("https://")) {
            value = "http://$value"
        }
        return "$value/"
    }
}
