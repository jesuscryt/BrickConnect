# ConstruLink

> **Red social profesional para el sector de la construcción**

ConstruLink es una aplicación web completa desarrollada como Trabajo de Fin de Grado. Permite a profesionales del sector de la construcción crear un perfil, publicar contenido, conectar con otros usuarios, buscar y ofertar empleo, y comunicarse mediante mensajes privados.

---

## 🛠 Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | Laravel 12 / PHP 8.2 |
| Base de datos | PostgreSQL |
| Frontend | Blade + Bootstrap 5 + Tailwind CSS 4 |
| Build | Vite 7 |
| Almacenamiento | AWS S3 / Cloudflare R2 (league/flysystem-aws-s3-v3) |
| Despliegue | Docker (PHP 8.2-cli + Composer) |

---

## 🚀 Instalación y puesta en marcha

### Requisitos previos

- PHP 8.2+, Composer, Node.js 18+, PostgreSQL

### Instalación local

```bash
# 1. Instalar dependencias (un solo comando)
composer run setup

# El script ejecuta automáticamente:
#   composer install
#   cp .env.example .env && php artisan key:generate
#   php artisan migrate --force
#   npm install && npm run build
```

### Entorno de desarrollo

```bash
# Arranca servidor PHP, queue worker, Pail (logs) y Vite en paralelo
composer run dev
```

### Variables de entorno clave (`.env`)

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=construlink

# Almacenamiento S3 / R2
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=auto
AWS_BUCKET=
AWS_ENDPOINT=          # URL de Cloudflare R2 si aplica
AWS_URL=               # URL pública de los archivos

MAIL_MAILER=smtp       # Para el flujo de recuperación de contraseña
```

### Despliegue con Docker

```bash
docker build -t construlink .
docker run -p 10000:10000 construlink
```

El contenedor ejecuta automáticamente `migrate`, `storage:link` y `artisan serve` en el puerto 10000.

---

## Módulos y funcionalidades

### 🔐 Autenticación

El sistema de autenticación está construido completamente a medida sobre Laravel, sin paquetes de terceros como Breeze o Jetstream.

- Registro con validación estricta: nombre sin símbolos ni caracteres especiales, email único, contraseña con confirmación y toggle de visibilidad.
- Login con mensajes de error específicos (credenciales incorrectas, cuenta baneada) y opción **"Recuérdame"** con sesión persistente de larga duración.
- **Recuperación de contraseña** completa vía email: formulario de solicitud, envío de enlace tokenizado firmado y formulario de restablecimiento. Implementa anti-enumeración (siempre muestra el mismo mensaje independientemente de si el email existe). Redirige al login tras el envío.
- Rate limiting en login, registro y reset: máximo 5 intentos por minuto por IP.
- Cierre de sesión con invalidación de sesión y regeneración de token CSRF.

---

### 👤 Perfil de usuario

Cada usuario dispone de un perfil público con información profesional.

- Campos editables: nombre, profesión, empresa, ubicación, teléfono, descripción personal y avatar (almacenado en S3/R2).
- Validación completa en el formulario de edición con mensajes de error específicos.
- Vista de perfil ajeno con los posts del usuario y collapsible de comentarios por publicación.
- Indicador de conexión en el perfil ajeno: botón de conectar, cancelar solicitud pendiente o desconectar, según el estado actual.

---

### 📰 Feed de publicaciones

Muro central donde los usuarios comparten contenido con su red.

- Crear publicaciones con texto e imagen opcional (almacenada en S3/R2). Preview de la imagen antes de publicar.
- Eliminar publicaciones propias (borra también el archivo del storage).
- Sistema de comentarios con panel desplegable por publicación: añadir y eliminar los propios.
- Al comentar una publicación, el autor recibe una notificación automática.
- Sidebar izquierdo con datos del perfil (profesión, empresa, ubicación) y contador de contactos con enlace directo a `/red`.
- Sidebar derecho con las últimas 5 ofertas activas.
- Estado de carga en los formularios: el botón de envío se deshabilita y muestra un spinner mientras se procesa.

---

### 🤝 Red de contactos

Sistema de conexiones bidireccionales al estilo de LinkedIn.

- Enviar solicitudes de conexión a otros usuarios desde su perfil o desde la sección de red.
- Aceptar o rechazar solicitudes recibidas.
- **Desconectar** un contacto existente con confirmación previa.
- Consulta de contactos optimizada mediante subquery UNION (emisor_id ↔ receptor_id), eliminando el problema N+1 en relaciones bidireccionales.

---

### 💼 Ofertas de empleo

Módulo completo de publicación y búsqueda de empleo en el sector.

- Crear ofertas con: título, empresa, descripción, ubicación, tipo de contrato (validado por enum: indefinido, temporal, prácticas, autónomo, obra y servicio), estado activo/cerrado y salario configurable (rango 1–500.000 €, modalidad mensual/anual/por hora, neto/bruto).
- **Filtros de búsqueda**: texto libre, ubicación, tipo de contrato y estado (activa/cerrada). Contador de resultados y botón para limpiar todos los filtros.
- Candidatarse o retirar candidatura con un clic. Protección ante race conditions mediante transacción atómica y restricción UNIQUE en base de datos.
- Activar y desactivar ofertas propias sin necesidad de eliminarlas.
- **Panel del creador**: lista de candidatos con foto, nombre y profesión. Muestra el botón "Mensaje" si ya son contactos del publicante, o "Conectar" si no lo son.
- El publicante recibe una notificación automática cuando alguien se candidata a su oferta.

---

### 💬 Chat privado

Mensajería directa entre usuarios conectados.

- Solo se puede iniciar conversación con usuarios que sean contactos de la red.
- Lista de conversaciones ordenada por actividad reciente, con indicador de mensajes no leídos.
- **Polling automático** cada 5 segundos mediante AJAX (`?since={lastId}`) que inserta únicamente los mensajes nuevos sin recargar la página. Se pausa cuando la pestaña del navegador no está activa (Visibility API) para ahorrar recursos.
- Los mensajes nuevos se insertan en el DOM con escape HTML para prevenir inyección XSS.
- Separador de días entre mensajes de distintas fechas.
- El scroll de la página principal está bloqueado dentro del chat; solo el contenedor de mensajes es desplazable.
- Botón para ir al mensaje más reciente y barra de scroll oculta en navegadores compatibles.
- Timestamps correctos ajustados a la zona horaria del servidor.

---

### 🔔 Notificaciones

Sistema centralizado de avisos en tiempo real (polling).

- **Tres tipos de notificación**:
  - `comentario` — alguien ha comentado en una publicación propia.
  - `solicitud_amistad` — una solicitud de conexión ha sido aceptada.
  - `aceptacion_oferta` — alguien se ha candidatado a una oferta propia.
- Dropdown en el navbar con las últimas 5 notificaciones no leídas y enlaces accionables a cada evento. Cargadas con eager loading (`emisor`, `notificable` y relaciones anidadas) para evitar N+1.
- Vista completa en `/notificaciones` con botones contextuales según el tipo (ver post, ver oferta, ver perfil del emisor). Gestión segura de emisores eliminados (sin errores 500).
- Marcar leída individualmente o todas a la vez con un botón.
- Las alertas flash de éxito desaparecen automáticamente tras unos segundos.
- Contador de notificaciones no leídas y de mensajes no leídos visibles en la navbar.

---

### 🛡 Panel de administración

Interfaz de gestión exclusiva para cuentas con rol administrador (`is_admin = true`).

- Acceso protegido por middleware `IsAdmin`, completamente independiente del flujo de usuario normal.
- **Dashboard**: estadísticas globales (usuarios totales, posts, ofertas, mensajes, conexiones, notificaciones).
- **Gestión de usuarios**: listado completo con buscador. Banear temporal o permanente con motivo de baneo. Los usuarios baneados son deslogueados automáticamente en su próximo request (middleware `CheckBanned`). Desbanear con un clic. Eliminar usuario con borrado en cascada.
- **Gestión de publicaciones**: listado completo con autor. Eliminar cualquier publicación de cualquier usuario.
- **Gestión de ofertas**: listado completo con creador. Eliminar cualquier oferta publicada.

---

## 🔒 Seguridad

- Protección CSRF en todos los formularios mediante el helper `@csrf` de Blade.
- **Policies de Laravel** para autorización granular: posts y comentarios solo pueden eliminarlos su propietario (o el admin); ofertas solo pueden modificarse/eliminarse por su propietario (o el admin para eliminar).
- Middleware `CheckBanned` en el grupo `web`: verifica en cada request si el usuario está baneado (temporal o permanentemente) y lo cierra de sesión si es así, con mensaje informativo.
- Middleware `IsAdmin` con verificación de rol, devuelve 403 si no se cumple.
- Validación y saneamiento estricto de todas las entradas en los controllers.
- Sesión invalidada y token regenerado al cerrar sesión y al eliminar la cuenta.
- Escape HTML en el cliente para los mensajes de chat (prevención XSS del lado del cliente).
- Rate limiting en todos los endpoints de autenticación (5 req/min por IP).
- Anti-enumeración en el flujo de recuperación de contraseña.

---

### Almacenamiento de archivos

Los avatares e imágenes de posts se almacenan en un bucket S3 compatible (AWS S3 o Cloudflare R2). En desarrollo se puede usar el disco `local` configurando `FILESYSTEM_DISK=local` en `.env`.
