package com.nossouhub.app.ui.screens

import android.net.Uri
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.aspectRatio
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.Button
import androidx.compose.material3.Checkbox
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateListOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import com.nossouhub.app.data.api.ApiClient
import com.nossouhub.app.data.model.EventDetailDto
import com.nossouhub.app.data.model.EventMediaDto
import com.nossouhub.app.ui.theme.Muted
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.toRequestBody
import java.time.LocalDate

@Composable
fun EventDetailScreen(
    baseUrl: String,
    eventId: Int,
    onEdit: () -> Unit,
    onBack: () -> Unit,
) {
    var detail by remember { mutableStateOf<EventDetailDto?>(null) }
    var error by remember { mutableStateOf<String?>(null) }
    val scope = rememberCoroutineScope()

    LaunchedEffect(eventId, baseUrl) {
        try {
            detail = withContext(Dispatchers.IO) { ApiClient.get(baseUrl).event(eventId) }
        } catch (t: Throwable) {
            error = prettyError(t)
        }
    }

    Column(Modifier.fillMaxSize()) {
        ScreenHeader(detail?.event?.title ?: "Evento", detail?.event?.occurred_at) {
            TextButton(onClick = onEdit) { Text("Editar") }
            TextButton(onClick = {
                scope.launch {
                    withContext(Dispatchers.IO) { ApiClient.get(baseUrl).deleteEvent(eventId) }
                    onBack()
                }
            }) { Text("Excluir") }
        }
        when {
            error != null -> ErrorBox(error!!)
            detail == null -> LoadingBox()
            else -> {
                val d = detail!!
                Column(Modifier.verticalScroll(rememberScrollState()).padding(16.dp)) {
                    d.event.description?.let { Text(it, modifier = Modifier.padding(bottom = 12.dp)) }
                    (d.photos + d.videos).forEach { item ->
                        AsyncImage(
                            model = ApiClient.absoluteUrl(item.url).also { ApiClient.get(baseUrl) },
                            contentDescription = item.original_name,
                            contentScale = ContentScale.Crop,
                            modifier = Modifier
                                .fillMaxWidth()
                                .aspectRatio(1f)
                                .padding(vertical = 4.dp),
                        )
                    }
                }
            }
        }
    }
}

@Composable
fun EventFormScreen(baseUrl: String, eventId: Int?, onDone: () -> Unit) {
    val context = LocalContext.current
    var title by remember { mutableStateOf("") }
    var description by remember { mutableStateOf("") }
    var occurredAt by remember { mutableStateOf(LocalDate.now().toString()) }
    var existing by remember { mutableStateOf<List<EventMediaDto>>(emptyList()) }
    val removed = remember { mutableStateListOf<Int>() }
    val picked = remember { mutableStateListOf<Uri>() }
    var error by remember { mutableStateOf<String?>(null) }
    var loading by remember { mutableStateOf(false) }
    val scope = rememberCoroutineScope()

    val picker = rememberLauncherForActivityResult(ActivityResultContracts.GetMultipleContents()) { uris ->
        picked.clear()
        picked.addAll(uris)
    }

    LaunchedEffect(eventId, baseUrl) {
        if (eventId != null) {
            val detail = withContext(Dispatchers.IO) { ApiClient.get(baseUrl).event(eventId) }
            title = detail.event.title
            description = detail.event.description.orEmpty()
            occurredAt = detail.event.occurred_at
            existing = detail.photos + detail.videos
        }
    }

    Column(Modifier.fillMaxSize().verticalScroll(rememberScrollState()).padding(16.dp)) {
        ScreenHeader(if (eventId == null) "Novo evento" else "Editar evento")
        HubField("Título", title, { title = it })
        HubField("Data (YYYY-MM-DD)", occurredAt, { occurredAt = it })
        HubField("Descrição", description, { description = it }, singleLine = false)

        existing.forEach { media ->
            Row(verticalAlignment = Alignment.CenterVertically) {
                Checkbox(
                    checked = removed.contains(media.id),
                    onCheckedChange = {
                        if (it) removed.add(media.id) else removed.remove(media.id)
                    },
                )
                Text("Remover #${media.id} (${media.type})")
            }
        }

        Button(onClick = { picker.launch("*/*") }, modifier = Modifier.padding(vertical = 8.dp)) {
            Text(if (picked.isEmpty()) "Anexar mídias" else "${picked.size} arquivo(s)")
        }
        error?.let { Text(it, color = MaterialTheme.colorScheme.error) }

        Button(
            enabled = !loading,
            onClick = {
                scope.launch {
                    loading = true
                    error = null
                    try {
                        withContext(Dispatchers.IO) {
                            val api = ApiClient.get(baseUrl)
                            val parts = picked.mapIndexed { index, uri ->
                                val bytes = context.contentResolver.openInputStream(uri)!!.use { it.readBytes() }
                                val mime = context.contentResolver.getType(uri) ?: "application/octet-stream"
                                val body = bytes.toRequestBody(mime.toMediaTypeOrNull())
                                MultipartBody.Part.createFormData("uploads", "file_$index", body)
                            }
                            if (eventId == null) {
                                api.createEvent(
                                    textBody(title),
                                    textBody(description),
                                    textBody(occurredAt),
                                    parts.ifEmpty { null },
                                )
                            } else {
                                api.updateEvent(
                                    eventId,
                                    textBody(title),
                                    textBody(description),
                                    textBody(occurredAt),
                                    removed.map { textBody(it.toString()) }.ifEmpty { null },
                                    parts.ifEmpty { null },
                                )
                            }
                        }
                        onDone()
                    } catch (t: Throwable) {
                        error = prettyError(t)
                    } finally {
                        loading = false
                    }
                }
            },
            modifier = Modifier.fillMaxWidth(),
        ) { Text(if (loading) "Salvando..." else "Salvar") }
        TextButton(onClick = onDone) { Text("Cancelar") }
    }
}
