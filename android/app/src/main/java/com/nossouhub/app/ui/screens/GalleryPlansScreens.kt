package com.nossouhub.app.ui.screens

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.aspectRatio
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
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
import androidx.compose.ui.Modifier
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import com.nossouhub.app.data.api.ApiClient
import com.nossouhub.app.data.model.GalleryAssetDto
import com.nossouhub.app.data.model.GalleryDto
import com.nossouhub.app.data.model.PlanCategoryCreateDto
import com.nossouhub.app.data.model.PlanCategoryDto
import com.nossouhub.app.data.model.PlanDto
import com.nossouhub.app.data.model.PlanWriteDto
import com.nossouhub.app.ui.theme.Muted
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

@Composable
fun GalleryScreen(baseUrl: String) {
    var data by remember { mutableStateOf<GalleryDto?>(null) }
    var error by remember { mutableStateOf<String?>(null) }
    var preview by remember { mutableStateOf<GalleryAssetDto?>(null) }
    var q by remember { mutableStateOf("") }

    LaunchedEffect(baseUrl, q) {
        try {
            data = withContext(Dispatchers.IO) {
                ApiClient.get(baseUrl).gallery(q = q.ifBlank { null })
            }
            error = data?.error
        } catch (t: Throwable) {
            error = prettyError(t)
        }
    }

    Column(Modifier.fillMaxSize()) {
        ScreenHeader("Galeria", "Immich")
        HubField("Buscar", q, { q = it })
        when {
            data == null && error == null -> LoadingBox()
            data?.assets.isNullOrEmpty() -> ErrorBox(error ?: "Nenhuma mídia encontrada.")
            else -> {
                LazyVerticalGrid(
                    columns = GridCells.Adaptive(110.dp),
                    modifier = Modifier.padding(12.dp),
                    verticalArrangement = Arrangement.spacedBy(8.dp),
                    horizontalArrangement = Arrangement.spacedBy(8.dp),
                ) {
                    items(data!!.assets) { asset ->
                        AsyncImage(
                            model = ApiClient.absoluteUrl(asset.thumbnail),
                            contentDescription = null,
                            contentScale = ContentScale.Crop,
                            modifier = Modifier
                                .fillMaxWidth()
                                .aspectRatio(1f)
                                .clickable { preview = asset },
                        )
                    }
                }
            }
        }
    }

    preview?.let { asset ->
        AlertDialog(
            onDismissRequest = { preview = null },
            confirmButton = { TextButton(onClick = { preview = null }) { Text("Fechar") } },
            text = {
                AsyncImage(
                    model = ApiClient.absoluteUrl(asset.preview),
                    contentDescription = null,
                    modifier = Modifier.fillMaxWidth(),
                )
            },
        )
    }
}

@Composable
fun PlansScreen(
    baseUrl: String,
    onOpen: (Int) -> Unit,
    onCreate: () -> Unit,
    onCategories: () -> Unit,
) {
    var plans by remember { mutableStateOf<List<PlanDto>?>(null) }
    var error by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(baseUrl) {
        try {
            plans = withContext(Dispatchers.IO) { ApiClient.get(baseUrl).plans() }
        } catch (t: Throwable) {
            error = prettyError(t)
        }
    }

    Column(Modifier.fillMaxSize()) {
        ScreenHeader("Planos") {
            TextButton(onClick = onCategories) { Text("Categorias") }
            Button(onClick = onCreate) { Text("Novo") }
        }
        when {
            error != null -> ErrorBox(error!!)
            plans == null -> LoadingBox()
            else -> LazyColumn(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                items(plans!!) { plan ->
                    HubCard(modifier = Modifier.clickable { onOpen(plan.id) }) {
                        Text(plan.title, style = MaterialTheme.typography.titleMedium)
                        Text(
                            "${plan.category?.name ?: ""} · ${plan.priority} · ${plan.status}",
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
fun PlanFormScreen(baseUrl: String, planId: Int?, onDone: () -> Unit) {
    var categories by remember { mutableStateOf<List<PlanCategoryDto>>(emptyList()) }
    var title by remember { mutableStateOf("") }
    var description by remember { mutableStateOf("") }
    var categoryIdText by remember { mutableStateOf("") }
    var priority by remember { mutableStateOf("medium") }
    var status by remember { mutableStateOf("pending") }
    var link by remember { mutableStateOf("") }
    var notes by remember { mutableStateOf("") }
    var error by remember { mutableStateOf<String?>(null) }
    val scope = rememberCoroutineScope()

    LaunchedEffect(baseUrl, planId) {
        categories = withContext(Dispatchers.IO) { ApiClient.get(baseUrl).planCategories() }
        if (categoryIdText.isBlank()) {
            categoryIdText = categories.firstOrNull()?.id?.toString().orEmpty()
        }
        if (planId != null) {
            val plan = withContext(Dispatchers.IO) { ApiClient.get(baseUrl).plan(planId) }
            title = plan.title
            description = plan.description.orEmpty()
            categoryIdText = plan.plan_category_id.toString()
            priority = plan.priority
            status = plan.status
            link = plan.link.orEmpty()
            notes = plan.notes.orEmpty()
        }
    }

    Column(Modifier.fillMaxSize().verticalScroll(rememberScrollState()).padding(16.dp)) {
        ScreenHeader(if (planId == null) "Novo plano" else "Editar plano") {
            if (planId != null) {
                TextButton(onClick = {
                    scope.launch {
                        withContext(Dispatchers.IO) { ApiClient.get(baseUrl).deletePlan(planId) }
                        onDone()
                    }
                }) { Text("Excluir") }
            }
        }
        HubField("Título", title, { title = it })
        Text(
            "Categorias: " + categories.joinToString { "${it.id}=${it.name}" },
            color = Muted,
            style = MaterialTheme.typography.bodySmall,
            modifier = Modifier.padding(bottom = 4.dp),
        )
        HubField("ID da categoria", categoryIdText, { categoryIdText = it })
        HubField("Prioridade (low/medium/high)", priority, { priority = it })
        HubField("Status (pending/in_progress/completed)", status, { status = it })
        HubField("Descrição", description, { description = it }, singleLine = false)
        HubField("Link", link, { link = it })
        HubField("Notas", notes, { notes = it }, singleLine = false)
        error?.let { Text(it, color = MaterialTheme.colorScheme.error) }
        Button(
            onClick = {
                val cat = categoryIdText.toIntOrNull() ?: return@Button
                scope.launch {
                    try {
                        val body = PlanWriteDto(
                            title = title,
                            description = description.ifBlank { null },
                            plan_category_id = cat,
                            priority = priority,
                            status = status,
                            link = link.ifBlank { null },
                            notes = notes.ifBlank { null },
                        )
                        withContext(Dispatchers.IO) {
                            val api = ApiClient.get(baseUrl)
                            if (planId == null) api.createPlan(body) else api.updatePlan(planId, body)
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
fun PlanCategoriesScreen(baseUrl: String, onBack: () -> Unit) {
    var rows by remember { mutableStateOf<List<PlanCategoryDto>>(emptyList()) }
    var name by remember { mutableStateOf("") }
    var error by remember { mutableStateOf<String?>(null) }
    val scope = rememberCoroutineScope()

    fun reload() {
        scope.launch {
            rows = withContext(Dispatchers.IO) { ApiClient.get(baseUrl).planCategories() }
        }
    }

    LaunchedEffect(baseUrl) { reload() }

    Column(Modifier.fillMaxSize().padding(16.dp)) {
        ScreenHeader("Categorias") { TextButton(onClick = onBack) { Text("Voltar") } }
        HubField("Nova categoria", name, { name = it })
        Button(onClick = {
            scope.launch {
                try {
                    withContext(Dispatchers.IO) {
                        ApiClient.get(baseUrl).createPlanCategory(PlanCategoryCreateDto(name))
                    }
                    name = ""
                    reload()
                } catch (t: Throwable) {
                    error = prettyError(t)
                }
            }
        }) { Text("Adicionar") }
        error?.let { Text(it, color = MaterialTheme.colorScheme.error) }
        LazyColumn(verticalArrangement = Arrangement.spacedBy(8.dp), modifier = Modifier.padding(top = 12.dp)) {
            items(rows) { cat ->
                HubCard {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Column {
                            Text(cat.name)
                            Text("${cat.plans_count} plano(s)", color = Muted, style = MaterialTheme.typography.bodySmall)
                        }
                        TextButton(
                            enabled = cat.plans_count == 0,
                            onClick = {
                                scope.launch {
                                    withContext(Dispatchers.IO) {
                                        ApiClient.get(baseUrl).deletePlanCategory(cat.id)
                                    }
                                    reload()
                                }
                            },
                        ) { Text("Excluir") }
                    }
                }
            }
        }
    }
}
