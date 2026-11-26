package com.nossouhub.app.data.repo

import android.content.Context
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.map

private val Context.dataStore by preferencesDataStore("nossouhub_settings")

class SettingsRepository(private val context: Context) {
    private val baseUrlKey = stringPreferencesKey("base_url")

    val baseUrl: Flow<String> = context.dataStore.data.map { prefs ->
        prefs[baseUrlKey].orEmpty()
    }

    suspend fun setBaseUrl(url: String) {
        context.dataStore.edit { prefs ->
            prefs[baseUrlKey] = url.trim().trimEnd('/')
        }
    }
}
