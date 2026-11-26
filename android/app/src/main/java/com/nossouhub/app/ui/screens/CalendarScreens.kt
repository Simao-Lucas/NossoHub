package com.nossouhub.app.ui.screens

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
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
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.nossouhub.app.data.api.ApiClient
import com.nossouhub.app.data.model.AppointmentDto
import com.nossouhub.app.data.model.AppointmentWriteDto
import com.nossouhub.app.data.model.CalendarEmailCreateDto
import com.nossouhub.app.data.model.CalendarEmailDto
import com.nossouhub.app.ui.theme.Muted
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import java.time.LocalDate
import java.time.LocalDateTime
import java.time.format.DateTimeFormatter

@Composable
fun CalendarScreen(
    baseUrl: String,
    onCreate: () -> Unit,
    onOpen: (Int) -> Unit,
    onEmails: () -> Unit,
) {
    val now = remember { LocalDate.now() }
    var year by remember { mutableStateOf(now.year) }
    var month by remember { mutableStateOf(now.monthValue) }
    var rows by remember { mutableStateOf<List<AppointmentDto>?>(null) }
    var error by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(baseUrl, year, month) {
        try {
            rows = withContext(Dispatchers.IO) {
                ApiClient.get(baseUrl).appointments(year = year, month = month)
            }
        } catch (t: Throwable) {
            error = prettyError(t)
        }
    }

    Column(Modifier.fillMaxSize()) {
        ScreenHeader("Calendário", "$month/$year") {
            TextButton(onClick = onEmails) { Text("E-mails") }
            Button(onClick = onCreate) { Text("Novo") }
        }
        Row(Modifier.fillMaxWidth().padding(horizontal = 16.dp), horizontalArrangement = Arrangement.SpaceBetween) {
            TextButton(onClick = {
                if (month == 1) {
                    month = 12
                    year -= 1
                } else month -= 1
            }) { Text("←") }
            TextButton(onClick = {
                year = now.year
                month = now.monthValue
            }) { Text("Hoje") }
            TextButton(onClick = {
                if (month == 12) {
                    month = 1
                    year += 1
                } else month += 1
            }) { Text("→") }
        }
        when {
            error != null -> ErrorBox(error!!)
            rows == null -> LoadingBox()
            rows!!.isEmpty() -> ErrorBox("Nenhum compromisso neste mês.")
            else -> LazyColumn(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                items(rows!!) { appt ->
                    HubCard(modifier = Modifier.clickable { onOpen(appt.id) }) {
                        Text(appt.summary, style = MaterialTheme.typography.titleMedium)
                        Text(
                            "${appt.starts_at} → ${appt.ends_at}",
                            color = Muted,
                            style = MaterialTheme.typography.bodySmall,
                        )
                    }
                }
            }
        }
    }
}

@Composable
fun AppointmentFormScreen(baseUrl: String, appointmentId: Int?, onDone: () -> Unit) {
    var summary by remember { mutableStateOf("") }
    var description by remember { mutableStateOf("") }
    var location by remember { mutableStateOf("") }
    var allDay by remember { mutableStateOf(false) }
    var startsAt by remember {
        mutableStateOf(LocalDateTime.now().withMinute(0).withSecond(0).format(DateTimeFormatter.ISO_LOCAL_DATE_TIME))
    }
    var endsAt by remember {
        mutableStateOf(LocalDateTime.now().plusHours(1).withMinute(0).withSecond(0).format(DateTimeFormatter.ISO_LOCAL_DATE_TIME))
    }
    var error by remember { mutableStateOf<String?>(null) }
    val scope = rememberCoroutineScope()

    LaunchedEffect(appointmentId, baseUrl) {
        if (appointmentId != null) {
            val appt = withContext(Dispatchers.IO) { ApiClient.get(baseUrl).appointment(appointmentId) }
            summary = appt.summary
            description = appt.description.orEmpty()
            location = appt.location.orEmpty()
            allDay = appt.all_day
            startsAt = appt.starts_at
            endsAt = appt.ends_at
        }
    }

    Column(Modifier.fillMaxSize().verticalScroll(rememberScrollState()).padding(16.dp)) {
        ScreenHeader(if (appointmentId == null) "Novo compromisso" else "Editar compromisso") {
            if (appointmentId != null) {
                TextButton(onClick = {
                    scope.launch {
                        withContext(Dispatchers.IO) { ApiClient.get(baseUrl).deleteAppointment(appointmentId) }
                        onDone()
                    }
                }) { Text("Excluir") }
            }
        }
        HubField("Título", summary, { summary = it })
        Row(verticalAlignment = Alignment.CenterVertically) {
            Checkbox(checked = allDay, onCheckedChange = { allDay = it })
            Text("Dia inteiro")
        }
        HubField("Início (ISO)", startsAt, { startsAt = it })
        HubField("Fim (ISO)", endsAt, { endsAt = it })
        HubField("Local", location, { location = it })
        HubField("Descrição", description, { description = it }, singleLine = false)
        error?.let { Text(it, color = MaterialTheme.colorScheme.error) }
        Button(
            onClick = {
                scope.launch {
                    try {
                        val body = AppointmentWriteDto(
                            summary = summary,
                            description = description.ifBlank { null },
                            location = location.ifBlank { null },
                            starts_at = startsAt,
                            ends_at = endsAt,
                            all_day = allDay,
                        )
                        withContext(Dispatchers.IO) {
                            val api = ApiClient.get(baseUrl)
                            if (appointmentId == null) api.createAppointment(body)
                            else api.updateAppointment(appointmentId, body)
                        }
                        onDone()
                    } catch (t: Throwable) {
                        error = prettyError(t)
                    }
                }
            },
            modifier = Modifier.fillMaxWidth().padding(top = 8.dp),
        ) { Text("Salvar") }
    }
}

@Composable
fun CalendarEmailsScreen(baseUrl: String, onBack: () -> Unit) {
    var rows by remember { mutableStateOf<List<CalendarEmailDto>>(emptyList()) }
    var email by remember { mutableStateOf("") }
    var label by remember { mutableStateOf("") }
    var error by remember { mutableStateOf<String?>(null) }
    val scope = rememberCoroutineScope()

    fun reload() {
        scope.launch {
            rows = withContext(Dispatchers.IO) { ApiClient.get(baseUrl).calendarEmails() }
        }
    }

    LaunchedEffect(baseUrl) { reload() }

    Column(Modifier.fillMaxSize().padding(16.dp)) {
        ScreenHeader("E-mails") { TextButton(onClick = onBack) { Text("Voltar") } }
        HubField("E-mail", email, { email = it })
        HubField("Rótulo", label, { label = it })
        Button(onClick = {
            scope.launch {
                try {
                    withContext(Dispatchers.IO) {
                        ApiClient.get(baseUrl).createCalendarEmail(
                            CalendarEmailCreateDto(email = email, label = label.ifBlank { null }),
                        )
                    }
                    email = ""
                    label = ""
                    reload()
                } catch (t: Throwable) {
                    error = prettyError(t)
                }
            }
        }) { Text("Adicionar") }
        error?.let { Text(it, color = MaterialTheme.colorScheme.error) }
        LazyColumn(verticalArrangement = Arrangement.spacedBy(8.dp), modifier = Modifier.padding(top = 12.dp)) {
            items(rows) { row ->
                HubCard {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Column {
                            Text(row.email)
                            Text(
                                "${row.label ?: "—"} · ${if (row.is_active) "ativo" else "inativo"}",
                                color = Muted,
                                style = MaterialTheme.typography.bodySmall,
                            )
                        }
                        Row {
                            TextButton(onClick = {
                                scope.launch {
                                    withContext(Dispatchers.IO) {
                                        ApiClient.get(baseUrl).toggleCalendarEmail(row.id)
                                    }
                                    reload()
                                }
                            }) { Text("Alt.") }
                            TextButton(onClick = {
                                scope.launch {
                                    withContext(Dispatchers.IO) {
                                        ApiClient.get(baseUrl).deleteCalendarEmail(row.id)
                                    }
                                    reload()
                                }
                            }) { Text("Excluir") }
                        }
                    }
                }
            }
        }
    }
}
