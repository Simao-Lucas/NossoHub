package com.nossouhub.app.ui.screens

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Button
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.nossouhub.app.data.api.ApiClient
import com.nossouhub.app.data.repo.SettingsRepository
import com.nossouhub.app.ui.theme.Muted
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

@Composable
fun SetupScreen(settings: SettingsRepository) {
    var url by remember { mutableStateOf("http://") }
    var error by remember { mutableStateOf<String?>(null) }
    var loading by remember { mutableStateOf(false) }
    val scope = rememberCoroutineScope()

    Column(
        Modifier.fillMaxSize().padding(24.dp),
        verticalArrangement = Arrangement.Center,
    ) {
        Text("Nosso Hub", style = MaterialTheme.typography.headlineLarge)
        Text(
            "Informe a URL Tailscale do servidor (ex.: http://100.x.x.x:2807).",
            color = Muted,
            modifier = Modifier.padding(top = 8.dp, bottom = 16.dp),
        )
        HubField("URL do servidor", url, { url = it })
        error?.let { Text(it, color = MaterialTheme.colorScheme.error, modifier = Modifier.padding(top = 8.dp)) }
        Button(
            enabled = !loading && url.isNotBlank(),
            onClick = {
                scope.launch {
                    loading = true
                    error = null
                    try {
                        withContext(Dispatchers.IO) {
                            ApiClient.get(url).health()
                        }
                        settings.setBaseUrl(url)
                        ApiClient.reset()
                    } catch (t: Throwable) {
                        error = "Não conectou: ${prettyError(t)}"
                    } finally {
                        loading = false
                    }
                }
            },
            modifier = Modifier.fillMaxWidth().padding(top = 16.dp),
        ) {
            Text(if (loading) "Testando..." else "Salvar e entrar")
        }
    }
}

@Composable
fun SettingsScreen(
    settings: SettingsRepository,
    baseUrl: String,
    onSaved: () -> Unit,
) {
    var url by remember { mutableStateOf(baseUrl) }
    var message by remember { mutableStateOf<String?>(null) }
    var error by remember { mutableStateOf<String?>(null) }
    val scope = rememberCoroutineScope()

    Column(Modifier.fillMaxSize().padding(16.dp)) {
        ScreenHeader("Ajustes", "Conexão Tailscale e atalhos técnicos")
        HubField("URL do servidor", url, { url = it })
        Button(
            onClick = {
                scope.launch {
                    error = null
                    message = null
                    try {
                        withContext(Dispatchers.IO) {
                            ApiClient.reset()
                            ApiClient.get(url).health()
                        }
                        settings.setBaseUrl(url)
                        message = "URL salva."
                        onSaved()
                    } catch (t: Throwable) {
                        error = prettyError(t)
                    }
                }
            },
            modifier = Modifier.fillMaxWidth().padding(top = 12.dp),
        ) { Text("Salvar URL") }

        message?.let { Text(it, color = MaterialTheme.colorScheme.primary, modifier = Modifier.padding(top = 8.dp)) }
        error?.let { Text(it, color = MaterialTheme.colorScheme.error, modifier = Modifier.padding(top = 8.dp)) }

        Text(
            "E-mails do calendário e categorias ficam nas abas Agenda e Planos.",
            color = Muted,
            modifier = Modifier.padding(top = 24.dp),
        )
    }
}
