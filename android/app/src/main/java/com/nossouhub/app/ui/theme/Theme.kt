package com.nossouhub.app.ui.theme

import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color

val BrandPurpleDeep = Color(0xFF12081F)
val BrandPurple = Color(0xFF2A1848)
val BrandYellow = Color(0xFFE8C547)
val BrandYellowSoft = Color(0xFFF5D76E)
val SurfaceElevated = Color(0xFF1F1730)
val Ink = Color(0xFFF4F0FA)
val Muted = Color(0xFFA89BB8)

private val colors = darkColorScheme(
    primary = BrandYellow,
    onPrimary = BrandPurpleDeep,
    secondary = BrandYellowSoft,
    background = BrandPurpleDeep,
    surface = SurfaceElevated,
    onBackground = Ink,
    onSurface = Ink,
    error = Color(0xFFFFB4B4),
)

@Composable
fun NossoHubTheme(content: @Composable () -> Unit) {
    MaterialTheme(
        colorScheme = colors,
        content = content,
    )
}
