package com.nossouhub.app.ui.screens

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.Button
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.nossouhub.app.data.api.ApiClient
import com.nossouhub.app.data.model.HomeDto
import com.nossouhub.app.data.model.TimelineItemDto
import com.nossouhub.app.ui.theme.BrandYellowSoft
import com.nossouhub.app.ui.theme.Muted
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.delay
import kotlinx.coroutines.withContext
import java.time.OffsetDateTime
import java.time.Period
import java.time.ZoneId
import java.time.ZonedDateTime
import java.time.temporal.ChronoUnit

@Composable
fun HomeScreen(baseUrl: String) {
    var home by remember { mutableStateOf<HomeDto?>(null) }
    var error by remember { mutableStateOf<String?>(null) }
    var tick by remember { mutableStateOf(0) }

    LaunchedEffect(baseUrl) {
        try {
            home = withContext(Dispatchers.IO) { ApiClient.get(baseUrl).home() }
        } catch (t: Throwable) {
            error = prettyError(t)
        }
    }
    LaunchedEffect(Unit) {
        while (true) {
            delay(1000)
            tick++
        }
    }

    when {
        error != null -> ErrorBox(error!!)
        home == null -> LoadingBox()
        else -> {
            val parts = remember(home!!.since, tick) { diffParts(home!!.since) }
            Column(
                Modifier.fillMaxSize().padding(24.dp),
                verticalArrangement = Arrangement.Center,
            ) {
                Text("Nosso Hub", color = BrandYellowSoft, style = MaterialTheme.typography.labelLarge)
                Text("Juntos há", style = MaterialTheme.typography.displaySmall, modifier = Modifier.padding(top = 8.dp))
                Spacer(Modifier.height(16.dp))
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                    listOf(
                        parts.years to "anos",
                        parts.months to "meses",
                        parts.days to "dias",
                        parts.hours to "horas",
                        parts.minutes to "min",
                    ).forEach { (value, label) ->
                        HubCard(modifier = Modifier.weight(1f).padding(2.dp)) {
                            Column {
                                Text("$value", style = MaterialTheme.typography.titleLarge)
                                Text(label, color = Muted, style = MaterialTheme.typography.labelSmall)
                            }
                        }
                    }
                }
            }
        }
    }
}

private data class DiffParts(val years: Long, val months: Long, val days: Long, val hours: Long, val minutes: Long)

private fun diffParts(sinceIso: String): DiffParts {
    val since = runCatching { OffsetDateTime.parse(sinceIso).toZonedDateTime() }
        .getOrElse { ZonedDateTime.parse(sinceIso) }
    val now = ZonedDateTime.now(ZoneId.of("America/Sao_Paulo"))
    var period = Period.between(since.toLocalDate(), now.toLocalDate())
    var hours = ChronoUnit.HOURS.between(since, now) % 24
    var minutes = ChronoUnit.MINUTES.between(since, now) % 60
    if (minutes < 0) {
        minutes += 60
        hours -= 1
    }
    if (hours < 0) {
        hours += 24
        period = period.minusDays(1)
    }
    return DiffParts(
        years = period.years.toLong().coerceAtLeast(0),
        months = period.months.toLong().coerceAtLeast(0),
        days = period.days.toLong().coerceAtLeast(0),
        hours = hours.coerceAtLeast(0),
        minutes = minutes.coerceAtLeast(0),
    )
}

@Composable
fun TimelineScreen(baseUrl: String, onOpenEvent: (Int) -> Unit, onCreate: () -> Unit) {
    var items by remember { mutableStateOf<List<TimelineItemDto>?>(null) }
    var error by remember { mutableStateOf<String?>(null) }

    fun load() {
        error = null
    }

    LaunchedEffect(baseUrl) {
        try {
            items = withContext(Dispatchers.IO) { ApiClient.get(baseUrl).timeline() }
        } catch (t: Throwable) {
            error = prettyError(t)
        }
    }

    Column(Modifier.fillMaxSize()) {
        ScreenHeader("Linha do Tempo", "Nossa história") {
            Button(onClick = onCreate) { Text("Novo") }
        }
        when {
            error != null -> ErrorBox(error!!) {
                // reload
            }
            items == null -> LoadingBox()
            items!!.isEmpty() -> ErrorBox("Ainda não há momentos.")
            else -> LazyColumn(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                items(items!!) { item ->
                    if (item.spacer_px > 0) Spacer(Modifier.height((item.spacer_px / 4).coerceAtMost(48).dp))
                    HubCard(modifier = Modifier.clickable { onOpenEvent(item.event.id) }) {
                        Text(item.event.occurred_at, color = BrandYellowSoft, style = MaterialTheme.typography.labelMedium)
                        Text(item.event.title, style = MaterialTheme.typography.titleMedium)
                        item.event.description?.takeIf { it.isNotBlank() }?.let {
                            Text(it.take(140), color = Muted, style = MaterialTheme.typography.bodySmall)
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun EventsScreen(baseUrl: String, onOpen: (Int) -> Unit, onCreate: () -> Unit) {
    TimelineScreen(baseUrl, onOpen, onCreate)
}
