# 🗂️ Tu Portfolio Personal

Sitio web de portfolio personal con **panel de administración completo**. Pensado para cualquier desarrollador que quiera tener su propio espacio en la web donde exponer proyectos, programas, habilidades e información personal — todo configurable desde una interfaz de admin sin tocar código.

---

## ¿Qué podés hacer con esto?

### Desde el panel de administración (`/admin`)
- Agregar, editar y eliminar **proyectos** y **programas**
- Marcar proyectos como **destacados** para que aparezcan en la portada
- Gestionar **categorías** y **tecnologías** (con colores personalizados por badge)
- Editar tu información personal: nombre, bio, foto de perfil
- Configurar tus redes sociales (GitHub, LinkedIn, Twitter) y email de contacto
- Controlar el **estilo y apariencia** del sitio desde la configuración
- Configurar la cantidad de proyectos por página (paginación)

### Lo que ve el visitante
- **Portada**: proyectos destacados y recientes con imagen, descripción, tecnologías y links
- **Proyectos**: listado completo con buscador y paginación
- **Programas**: sección dedicada a software/aplicaciones desarrolladas
- **Sobre mí**: bio, foto, tecnologías, estadísticas (nº de proyectos, tecnologías, categorías) y links a redes
- **Detalle de proyecto**: vista completa con imágenes, descripción larga, links a demo y repo
- **Contacto**: formulario de contacto

---

## 🛠️ Tecnologías

- **PHP** — backend y vistas
- **MySQL** — base de datos
- **Bootstrap 5** + **Bootstrap Icons** — UI responsiva
- **CSS** personalizado con variables de color

---

## 📁 Estructura del proyecto

```
tu_portfolio/
├── admin/                  # Panel de administración
├── assets/                 # Imágenes, CSS, JS estáticos
│   └── img/logo.jpeg
├── database/               # Scripts SQL para crear la base de datos
├── includes/               # Conexión a BD, header, helpers compartidos
├── index.php               # Portada (proyectos destacados + recientes)
├── proyectos.php           # Listado de proyectos con búsqueda y paginación
├── programas.php           # Listado de programas/aplicaciones
├── detalle-proyecto.php    # Vista individual de un proyecto
├── sobre-mi.php            # Sección "Sobre mí" con estadísticas y redes
├── contacto.php            # Formulario de contacto
├── scripts.php             # Scripts auxiliares
└── bots.php                # Bloqueo de bots
```

---

## ⚙️ Instalación

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/Kevinmoth/tu_portfolio.git
   ```

2. **Importar la base de datos** desde la carpeta `/database` en tu servidor MySQL.

3. **Configurar la conexión** en `includes/conexion.php`:
   ```php
   $host = 'localhost';
   $user = 'tu_usuario';
   $pass = 'tu_contraseña';
   $db   = 'nombre_base_de_datos';
   ```

4. **Servir el proyecto** con un servidor local (XAMPP, Laragon, WAMP, etc.) apuntando a la carpeta raíz.

5. **Ingresar al panel de admin** en `/admin` para personalizar tu información, cargar proyectos y configurar el sitio.

---

## 📋 Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache / Nginx)
