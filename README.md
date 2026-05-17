# aGo SMTP

> Send WordPress emails via SMTP with 8 provider presets, a step-by-step credentials wizard, encrypted password storage, DNS health check (SPF / DKIM / DMARC) and failure rate alerts. Completely free.

[![License: GPL v2+](https://img.shields.io/badge/License-GPLv2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress 6.0+](https://img.shields.io/badge/WordPress-6.0%2B-21759b.svg)](https://wordpress.org)
[![PHP 8.1+](https://img.shields.io/badge/PHP-8.1%2B-777bb4.svg)](https://www.php.net)

Versión en español más abajo · [Spanish version below](#ago-smtp-español)

---

## What it does

WordPress by default sends mail through the host's `mail()` command, which fails silently on most shared and cloud hosting. This plugin routes every outgoing email through the SMTP server of your choice. Pick a provider preset, follow the in-plugin wizard to obtain the right credentials for that provider, and you are sending in two minutes.

The differentiator from other SMTP plugins: every preset includes a built-in wizard that opens when you select it and walks you, step by step, through how to obtain a username and password for that exact provider, with direct links to the right page in the provider dashboard. The wizard is translated to English, Spanish and Brazilian Portuguese.

## Provider presets included

- Gmail (App Password)
- SendGrid (API key)
- Amazon SES (SMTP credentials from SES console)
- Brevo / Sendinblue (SMTP key)
- Resend (API key)
- MailerSend (SMTP user)
- SMTP2GO (SMTP user)
- Acumbamail (SMTP password)
- Custom (manual entry of any SMTP server)

## Main features

- 8 provider presets with auto-fill of host, port and encryption.
- Step-by-step credentials wizard per provider, in English / Spanish / Portuguese.
- Test email button with the exact SMTP response on failure plus a heuristic hint.
- **DNS health check**: on-demand audit of SPF, DKIM and DMARC records for your sending domain. Read-only DNS queries; nothing is modified.
- **Encrypted password storage**: AES-256-CBC tied to your WordPress `AUTH_KEY`. Falls back to base64 only when the WordPress secret keys are placeholders.
- **Optional override**: define `AGO_SMTP_PASSWORD` as a constant in `wp-config.php` and the plugin reads the password from the constant, never from the database.
- **Failure rate alerts**: optional email to the admin when the failure rate across the last N emails exceeds a configurable threshold. Throttled to one alert per 24 hours.
- Recent log of last 10 outgoing emails in the WordPress options table. No custom database tables are created.
- No external API calls on its own. No remote license server. No telemetry. No premium upsell.
- Clean uninstall: deactivating leaves your settings intact, uninstalling removes both options and unschedules the cron event.

## Requirements

- WordPress 6.0 or newer
- PHP 8.1 or newer
- SMTP credentials from any of the supported providers (or any other SMTP server you control)

## Installation

1. Download the latest [release ZIP](../../releases) or clone this repository into `wp-content/plugins/ago-smtp`.
2. Activate the plugin from the WordPress admin.
3. Open `aGo Tools → SMTP`.
4. Pick a provider preset. The credentials wizard opens automatically with step-by-step instructions and direct links.
5. Save the settings and send a test email.
6. Run the DNS health check on your sending domain to confirm SPF, DKIM and DMARC are correctly set.

## Privacy

- The plugin only opens an SMTP connection to the host you configure. No data goes anywhere else.
- The optional DNS health check uses PHP's built-in `dns_get_record()` against the resolver your hosting server is configured with. No data leaves your server through the plugin.
- The plugin does not contact any aGo Lab server at any point. No telemetry, no remote license check, no analytics.

Each SMTP provider has its own terms and privacy policy. Review them before sending production traffic. The list is in `readme.txt → External services`.

## Contributing

Issues and pull requests are welcome. Open an issue first if you want to discuss a feature or a bigger change. For bugs, please include WordPress version, PHP version, the active theme and steps to reproduce.

If you ship a translation for another locale, drop a pull request with the `.l10n.php` file inside `languages/`. The plugin already ships in English, Spanish and Brazilian Portuguese.

## Support the project

If this plugin saves you time, consider buying me a coffee:

- [PayPal — single donation](https://paypal.me/sixtovaldes)
- [Buy Me a Coffee](https://www.buymeacoffee.com/sixtovaldese)

I build the [aGo plugin suite](https://ago.cl) in my spare time. Donations help keep these plugins maintained and open source.

## Other aGo Lab plugins

Free WordPress plugins from the same team:

- **aGo AI Chatbot** — AI customer support widget powered by your own knowledge base.
- **aGo Legal** — GDPR / LGPD / Chile Law 21.719 compliance toolkit.
- **aGo Cleanup** — Remove WordPress bloat and front-end clutter.
- **aGo Harden** — Lightweight security hardening toggles.

Browse them all at [ago.cl/herramientas/](https://ago.cl/herramientas/).

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

## Credits

Made by [Sixto Valdés](https://github.com/sixtovaldese), founder of [aGo lab](https://ago.cl) in Chillán, Chile.

---

# aGo SMTP (español)

> Envía emails de WordPress vía SMTP con 8 presets de proveedores, asistente paso a paso para las credenciales, contraseña cifrada, diagnóstico DNS (SPF / DKIM / DMARC) y alertas de tasa de fallos. Completamente gratuito.

## Qué hace

Por defecto WordPress manda email con la función `mail()` del host, que falla silenciosamente en la mayoría de hostings compartidos y en la nube. Este plugin enruta todos los envíos a través del servidor SMTP que elijas. Eliges un preset, el asistente del plugin te muestra cómo obtener las credenciales de ese proveedor, y en dos minutos estás enviando.

El diferenciador frente a otros plugins SMTP: cada preset incluye un asistente que se abre al seleccionarlo y te lleva, paso a paso, a obtener usuario y contraseña para ese proveedor, con enlaces directos al panel del proveedor. El asistente está traducido a inglés, español y portugués brasileño.

## Presets de proveedores incluidos

- Gmail (Contraseña de aplicación)
- SendGrid (API key)
- Amazon SES (credenciales SMTP de la consola SES)
- Brevo / Sendinblue (SMTP key)
- Resend (API key)
- MailerSend (usuario SMTP)
- SMTP2GO (usuario SMTP)
- Acumbamail (contraseña SMTP)
- Personalizado (ingreso manual de cualquier servidor SMTP)

## Funcionalidades principales

- 8 presets de proveedores con autocompletado de host, puerto y cifrado.
- Asistente paso a paso por proveedor, en inglés / español / portugués.
- Botón de email de prueba con la respuesta SMTP exacta en caso de fallo y una pista heurística.
- **Diagnóstico DNS**: chequeo on-demand de los registros SPF, DKIM y DMARC del dominio remitente. Solo consultas DNS de lectura; nunca modifica tus registros.
- **Almacenamiento de contraseña cifrado**: AES-256-CBC usando tu `AUTH_KEY` de WordPress. Cae a base64 solo si las claves secretas de WP siguen siendo los placeholders.
- **Override opcional**: define `AGO_SMTP_PASSWORD` como constante en `wp-config.php` y el plugin lee la contraseña de la constante, nunca de la base de datos.
- **Alertas de tasa de fallos**: email opcional al admin cuando la tasa de fallos de los últimos N envíos supera un umbral configurable. Limitado a una alerta por 24 horas.
- Registro reciente de los últimos 10 emails enviados en la tabla de opciones de WordPress. No se crean tablas extra.
- Sin llamadas a APIs externas por su cuenta. Sin servidor remoto de licencias. Sin telemetría. Sin upsell premium.
- Desinstalación limpia: desactivar conserva la configuración, desinstalar borra opciones y cancela el cron.

## Requisitos

- WordPress 6.0 o superior
- PHP 8.1 o superior
- Credenciales SMTP de cualquiera de los proveedores soportados (o cualquier servidor SMTP que controles)

## Instalación

1. Descarga el [ZIP de la última release](../../releases) o clona este repositorio en `wp-content/plugins/ago-smtp`.
2. Activa el plugin desde el admin de WordPress.
3. Entra a `aGo Herramientas → SMTP`.
4. Elige un preset de proveedor. El asistente de credenciales se abre solo con los pasos y enlaces directos.
5. Guarda la configuración y envía un email de prueba.
6. Corre el diagnóstico DNS contra tu dominio de envío para confirmar que SPF, DKIM y DMARC están bien.

## Privacidad

- El plugin solo abre una conexión SMTP al servidor que tú configures. No envía datos a ningún otro lugar.
- El diagnóstico DNS usa la función nativa `dns_get_record()` de PHP contra el resolver DNS configurado en tu servidor. No sale información del plugin.
- El plugin nunca contacta ningún servidor de aGo Lab. Sin telemetría, sin verificación remota de licencia, sin analítica.

Cada proveedor SMTP tiene sus propios términos y política de privacidad. Revísalos antes de enviar tráfico de producción. La lista está en `readme.txt → External services`.

## Contribuir

Los issues y pull requests son bienvenidos. Si quieres discutir una función nueva o un cambio grande, abre un issue primero. Para reportar bugs incluye versión de WordPress, de PHP, el theme activo y los pasos para reproducir.

Si traduces el plugin a otro idioma, abre un PR con el archivo `.l10n.php` dentro de `languages/`. El plugin ya viene en inglés, español y portugués brasileño.

## Apoyar el proyecto

Si este plugin te ahorra tiempo, considera invitarme un café:

- [PayPal — donación única](https://paypal.me/sixtovaldes)
- [Buy Me a Coffee](https://www.buymeacoffee.com/sixtovaldese)

Desarrollo la [suite de plugins aGo](https://ago.cl) en mi tiempo libre. Las donaciones ayudan a mantener estos plugins activos y open source.

## Otros plugins de aGo Lab

Plugins gratuitos de WordPress del mismo equipo:

- **aGo AI Chatbot** — Widget de atención al cliente con IA basado en tu propia base de conocimiento.
- **aGo Legal** — Kit de cumplimiento GDPR / LGPD / Ley 21.719 de Chile.
- **aGo Cleanup** — Quita el bloat de WordPress y la basura del frontend.
- **aGo Harden** — Toggles ligeros para reforzar la seguridad.

Verlos todos en [ago.cl/herramientas/](https://ago.cl/herramientas/).

## Licencia

GPL-2.0-or-later. Ver [LICENSE](LICENSE).

## Créditos

Hecho por [Sixto Valdés](https://github.com/sixtovaldese), fundador de [aGo lab](https://ago.cl) en Chillán, Chile.
