# Nosso Hub — App Android

App nativo (Kotlin + Jetpack Compose) com CRUD completo contra a API JSON do Nosso Hub (`/api/...`).

O site web **não é alterado** por este app: ele só consome a API.

## Pré-requisitos

- Android Studio Ladybug+ (ou SDK 35 + JDK 17)
- Servidor Nosso Hub no ar (`:2807`)
- [Tailscale](https://tailscale.com/) no telefone e no homelab (mesma conta/rede)

## Configurar URL (Tailscale)

No primeiro open do app, informe a URL do servidor, por exemplo:

```text
http://100.x.x.x:2807
```

ou MagicDNS:

```text
http://nome-do-homelab:2807
```

A URL fica salva no DataStore e pode ser trocada em **Ajustes**.

Cleartext HTTP está liberado (necessário para IPs Tailscale sem TLS).

## Gerar o APK

### Pelo Android Studio (recomendado)

1. Abra a pasta `android/` no Android Studio  
2. Aguarde o Gradle Sync  
3. `Build → Build Bundle(s) / APK(s) → Build APK(s)`  
4. APK debug: `app/build/outputs/apk/debug/app-debug.apk`

Para release:

1. `Build → Generate Signed Bundle / APK` (ou `assembleRelease` se já tiver keystore)  
2. APK: `app/build/outputs/apk/release/app-release.apk` (ou unsigned conforme config)

### Pela linha de comando

Na primeira vez, abra no Android Studio para baixar o Gradle Wrapper, ou rode:

```bash
cd android
# se tiver Gradle instalado:
gradle wrapper
./gradlew assembleDebug
```

Windows:

```powershell
cd android
.\gradlew.bat assembleDebug
```

Instale no aparelho:

```bash
adb install -r app/build/outputs/apk/debug/app-debug.apk
```

## API usada

| Recurso | Prefixo |
|---------|---------|
| Health / Home | `/api/health`, `/api/home` |
| Eventos / Timeline | `/api/events`, `/api/timeline` |
| Planos / Categorias | `/api/plans`, `/api/plan-categories` |
| Agenda / E-mails | `/api/appointments`, `/api/calendar-emails` |
| Galeria | `/api/gallery` |
| Mídia / Immich | `/midia/{id}`, `/immich/...` (rotas existentes) |

## Módulos do app

- Início (timer)
- Linha do Tempo / Eventos (CRUD + upload)
- Galeria Immich
- Planos + categorias
- Calendário + e-mails
- Ajustes (URL Tailscale)
