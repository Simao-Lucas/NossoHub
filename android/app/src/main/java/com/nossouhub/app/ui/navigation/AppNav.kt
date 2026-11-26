package com.nossouhub.app.ui.navigation

import androidx.compose.foundation.layout.padding
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.CalendarMonth
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.ListAlt
import androidx.compose.material.icons.filled.PhotoLibrary
import androidx.compose.material.icons.filled.Settings
import androidx.compose.material.icons.filled.Timeline
import androidx.compose.material3.Icon
import androidx.compose.material3.NavigationBar
import androidx.compose.material3.NavigationBarItem
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.remember
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.navigation.NavGraph.Companion.findStartDestination
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.currentBackStackEntryAsState
import androidx.navigation.compose.rememberNavController
import androidx.navigation.navArgument
import com.nossouhub.app.data.repo.SettingsRepository
import com.nossouhub.app.ui.screens.AppointmentFormScreen
import com.nossouhub.app.ui.screens.CalendarEmailsScreen
import com.nossouhub.app.ui.screens.CalendarScreen
import com.nossouhub.app.ui.screens.EventDetailScreen
import com.nossouhub.app.ui.screens.EventFormScreen
import com.nossouhub.app.ui.screens.EventsScreen
import com.nossouhub.app.ui.screens.GalleryScreen
import com.nossouhub.app.ui.screens.HomeScreen
import com.nossouhub.app.ui.screens.PlanCategoriesScreen
import com.nossouhub.app.ui.screens.PlanFormScreen
import com.nossouhub.app.ui.screens.PlansScreen
import com.nossouhub.app.ui.screens.SettingsScreen
import com.nossouhub.app.ui.screens.SetupScreen
import com.nossouhub.app.ui.screens.TimelineScreen

private data class Tab(val route: String, val label: String, val icon: androidx.compose.ui.graphics.vector.ImageVector)

@Composable
fun AppNav() {
    val context = LocalContext.current
    val settings = remember { SettingsRepository(context.applicationContext) }
    val baseUrl by settings.baseUrl.collectAsState(initial = "")
    val navController = rememberNavController()

    if (baseUrl.isBlank()) {
        SetupScreen(settings = settings)
        return
    }

    val tabs = listOf(
        Tab("home", "Início", Icons.Default.Home),
        Tab("timeline", "Tempo", Icons.Default.Timeline),
        Tab("gallery", "Galeria", Icons.Default.PhotoLibrary),
        Tab("plans", "Planos", Icons.Default.ListAlt),
        Tab("calendar", "Agenda", Icons.Default.CalendarMonth),
        Tab("settings", "Ajustes", Icons.Default.Settings),
    )

    val backStack by navController.currentBackStackEntryAsState()
    val current = backStack?.destination?.route.orEmpty()
    val showBottom = tabs.any { current == it.route || current.startsWith(it.route + "?") }

    Scaffold(
        bottomBar = {
            if (showBottom) {
                NavigationBar {
                    tabs.forEach { tab ->
                        NavigationBarItem(
                            selected = current == tab.route,
                            onClick = {
                                navController.navigate(tab.route) {
                                    popUpTo(navController.graph.findStartDestination().id) {
                                        saveState = true
                                    }
                                    launchSingleTop = true
                                    restoreState = true
                                }
                            },
                            icon = { Icon(tab.icon, contentDescription = tab.label) },
                            label = { Text(tab.label) },
                        )
                    }
                }
            }
        },
    ) { padding ->
        NavHost(
            navController = navController,
            startDestination = "home",
            modifier = Modifier.padding(padding),
        ) {
            composable("home") { HomeScreen(baseUrl = baseUrl) }
            composable("timeline") {
                TimelineScreen(
                    baseUrl = baseUrl,
                    onOpenEvent = { navController.navigate("event/$it") },
                    onCreate = { navController.navigate("event/new") },
                )
            }
            composable("events") {
                EventsScreen(
                    baseUrl = baseUrl,
                    onOpen = { navController.navigate("event/$it") },
                    onCreate = { navController.navigate("event/new") },
                )
            }
            composable("event/new") {
                EventFormScreen(
                    baseUrl = baseUrl,
                    eventId = null,
                    onDone = { navController.popBackStack() },
                )
            }
            composable(
                "event/{id}",
                arguments = listOf(navArgument("id") { type = NavType.IntType }),
            ) { entry ->
                val id = entry.arguments!!.getInt("id")
                EventDetailScreen(
                    baseUrl = baseUrl,
                    eventId = id,
                    onEdit = { navController.navigate("event/$id/edit") },
                    onBack = { navController.popBackStack() },
                )
            }
            composable(
                "event/{id}/edit",
                arguments = listOf(navArgument("id") { type = NavType.IntType }),
            ) { entry ->
                EventFormScreen(
                    baseUrl = baseUrl,
                    eventId = entry.arguments!!.getInt("id"),
                    onDone = { navController.popBackStack() },
                )
            }
            composable("gallery") { GalleryScreen(baseUrl = baseUrl) }
            composable("plans") {
                PlansScreen(
                    baseUrl = baseUrl,
                    onOpen = { navController.navigate("plan/$it/edit") },
                    onCreate = { navController.navigate("plan/new") },
                    onCategories = { navController.navigate("plan-categories") },
                )
            }
            composable("plan/new") {
                PlanFormScreen(baseUrl = baseUrl, planId = null, onDone = { navController.popBackStack() })
            }
            composable(
                "plan/{id}/edit",
                arguments = listOf(navArgument("id") { type = NavType.IntType }),
            ) { entry ->
                PlanFormScreen(
                    baseUrl = baseUrl,
                    planId = entry.arguments!!.getInt("id"),
                    onDone = { navController.popBackStack() },
                )
            }
            composable("plan-categories") {
                PlanCategoriesScreen(baseUrl = baseUrl, onBack = { navController.popBackStack() })
            }
            composable("calendar") {
                CalendarScreen(
                    baseUrl = baseUrl,
                    onCreate = { navController.navigate("appointment/new") },
                    onOpen = { navController.navigate("appointment/$it/edit") },
                    onEmails = { navController.navigate("calendar-emails") },
                )
            }
            composable("appointment/new") {
                AppointmentFormScreen(baseUrl = baseUrl, appointmentId = null, onDone = { navController.popBackStack() })
            }
            composable(
                "appointment/{id}/edit",
                arguments = listOf(navArgument("id") { type = NavType.IntType }),
            ) { entry ->
                AppointmentFormScreen(
                    baseUrl = baseUrl,
                    appointmentId = entry.arguments!!.getInt("id"),
                    onDone = { navController.popBackStack() },
                )
            }
            composable("calendar-emails") {
                CalendarEmailsScreen(baseUrl = baseUrl, onBack = { navController.popBackStack() })
            }
            composable("settings") {
                SettingsScreen(
                    settings = settings,
                    baseUrl = baseUrl,
                    onSaved = {
                        navController.navigate("home") {
                            popUpTo(0)
                        }
                    },
                )
            }
        }
    }
}
