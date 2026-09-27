# Sistema de Matrícula — Colegio Secundario de Guabito

Aplicación web para gestionar la matrícula de estudiantes. Migración del sistema original en C++/Qt Creator a PHP + MySQL.

> **Estado del proyecto:** en desarrollo inicial. Cada rol (Director, Secretaria, Profesor, Estudiante) se desarrolla por separado en ramas propias.

---

## 1. Stack tecnológico

- **Backend:** PHP nativo (sin frameworks tipo Laravel), **PDO con prepared statements** en todas las consultas.
- **Base de datos:** MySQL / MariaDB.
- **Frontend:** HTML5 + CSS3 + JavaScript vanilla. **Sin React/Vue/Angular.**
- **Librerías vía CDN:**
  - Bootstrap 5 (grids, cards, sidebar, formularios)
  - Chart.js (gráficas de barras en dashboard)
  - Cropper.js (recorte 1:1 de fotos de perfil antes de subirlas)
- **Sin Composer, sin npm, sin build tools** (InfinityFree no los soporta).
- **Entorno local:** XAMPP en Windows. El proyecto vive en `C:\xampp\htdocs\sistema-matricula\` y se accede como `http://localhost/sistema-matcula/`.

---

## 2. Instalación local (XAMPP)

### 2.1 Clonar el repositorio
```bash
# dentro de C:\xampp\htdocs\
git clone <URL_DEL_REPO> sistema-matricula
cd sistema-matricula
```

### 2.2 Configurar credenciales (NO está en el repo)
El archivo `config/credenciales.php` está en `.gitignore` porque contiene los datos reales de conexión.

```bash
# en la carpeta del proyecto
copy config\credenciales.example.php config\credenciales.php
```

Luego editar `config\credenciales.php` y poner los datos locales (XAMPP por defecto: host=`localhost`, user=`root`, pass=``).

### 2.3 Crear la base de datos
1. Abrir `http://localhost/phpmyadmin/`.
2. Crear la base de datos `sistema_matricula` con cotejamiento `utf8mb4_unicode_ci`.
3. Importar el archivo `sql/sistema_matricula_normalizado.sql`.

### 2.4 Verificar acceso
Navegar a `http://localhost/sistema-matricula/`. Debe redirigir al login.

> **Usuario inicial (Director):** cédula `111`, contraseña vacía. Definir contraseña antes del primer uso real (módulo de instalación).

---

## 3. Estructura del proyecto

```
/sistema-matricula
├── index.php                  redirige a login o dashboard según sesión
├── login.php
├── logout.php
├── recuperar-password.php
├── solicitud-matricula.php    formulario público
│
├── /config
│   ├── conexion.php           conexión PDO (requiere credenciales.php)
│   ├── credenciales.php       ⚠ NO en repo — credenciales reales
│   └── credenciales.example.php plantilla pública
│
├── /includes
│   ├── auth.php               verificación de sesión + rol
│   ├── csrf.php               helper de token CSRF
│   ├── header.php             layout superior común
│   └── sidebar.php            sidebar dinámico según rol
│
├── /director/                 panel del Director
├── /secretaria/               panel de Secretaria
├── /profesor/                 panel del Profesor
├── /estudiante/               panel del Estudiante
│
├── /actions/                  procesamiento de formularios (CRUD backend)
│
├── /assets
│   ├── /css
│   ├── /js
│   └── /img
│
├── /uploads                   archivos subidos por usuarios (NO se sube al repo)
│   ├── /perfiles              fotos de perfil (jpg/png recortados)
│   └── /documentos            boletines y certificados
│
└── /sql
    └── sistema_matricula_normalizado.sql
```

---

## 4. Roles y pantallas

| Rol        | Panel  | Acceso a                                         |
|------------|--------|--------------------------------------------------|
| Director   | `/director/`     | CRUD total + reportes + configuración    |
| Secretaria | `/secretaria/`   | Estudiantes, profesores, matrículas, solicitudes, reportes |
| Profesor   | `/profesor/`     | Sus materias, estudiantes, notas, horario |
| Estudiante | `/estudiante/`   | Sus materias, notas, horario, perfil     |

Más el flujo público:
- `/login.php` — inicio de sesión
- `/recuperar-password.php` — recuperación por cédula
- `/solicitud-matricula.php` — solicitud pública de matrícula

---

## 5. Trabajo en equipo (Git / GitHub)

### 5.1 Ramas
- `main` → código estable. Solo recibe merges desde `develop`.
- `develop` → rama de integración. Todos los PRs apuntan aquí.
- `rama-director-secretaria` → Director + Secretaria + login + base (yo).
- `rama-profesor` → Alexis.
- `rama-estudiante` → Eliecer.

### 5.2 Reglas para evitar conflictos
1. **Nadie edita directo en `main` ni en `develop`.**
2. Cada quien trabaja **solo en los archivos de su carpeta de rol** (`/profesor/`, `/estudiante/`, `/director/`, `/secretaria/`) para minimizar conflictos de merge.
3. Los archivos compartidos (`/config/conexion.php`, `/includes/*`, el schema SQL) **se tocan solo avisando al equipo**, porque ahí sí hay riesgo de choque.
4. **Pull Request obligatorio** de `rama-*` → `develop` (no merge directo), aunque sea autoaprobado, para mantener historial claro.
5. **Commits descriptivos en español**, ej:
   - `feat: agrega CRUD de estudiantes`
   - `fix: corrige validación de cédula en login`
   - `chore: actualiza .gitignore`

---

## 6. Seguridad (resumen — ver código en `/includes/auth.php`)

| Amenaza    | Mitigación                                                    |
|------------|---------------------------------------------------------------|
| Inyección SQL | 100% PDO con prepared statements (`ATTR_EMULATE_PREPARES => false`). |
| XSS        | `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')` en todo output.   |
| CSRF       | Token por sesión en cada formulario POST.                     |
| Sesiones   | Cookies `httponly` + `samesite=Strict`, `regenerate_id` post-login. |
| Inactividad | Cierre automático a los 5 min (`$_SESSION['ultima_actividad']`). |
| Control de rol | Verificación en `auth.php` al inicio de cada página protegida (no solo en el menú). |
| Uploads    | Validar MIME real con `finfo_file`, renombrar archivos, `.htaccess` con `php_flag engine off` en `/uploads/`. |
| Fuerza bruta | Bloqueo temporal tras 5 intentos fallidos por cédula. Mensajes de error genéricos. |
| Cabeceras HTTP | `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`. |
| Credenciales | `config/credenciales.php` en `.gitignore`. Plantilla pública `credenciales.example.php`. |

---

## 7. Orden de desarrollo

1. ✅ Estructura de carpetas + `.gitignore` + `README.md` + schema SQL ajustado.
2. ⏳ Backend base: conexión PDO + login + sesiones + verificación de rol.
3. ⏳ Sidebar y layout base reutilizable por rol.
4. ⏳ Panel Director completo (como referencia de patrón).
5. ⏳ Módulo de subida y recorte de fotos (Cropper.js + GD).
6. ⏳ Paneles Secretaria, Profesor y Estudiante siguiendo el patrón del Director.

---

## 8. Próximos pasos para colaboradores

Antes de empezar a trabajar en su rama:

1. Clonar el repo y seguir la sección **2. Instalación local**.
2. Crear su rama desde `develop`:
   ```bash
   git checkout develop
   git pull
   git checkout -b rama-profesor      # o rama-estudiante
   ```
3. Leer el patrón del **Panel Director** (punto 4 del orden de desarrollo) cuando esté listo — todos los paneles comparten estructura (sidebar + header + tarjetas de resumen + tablas + modales).
4. Cualquier archivo compartido (`includes/`, `config/`) requiere aviso previo al equipo.