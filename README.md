# Sistema Web CORES — Gestión y Comunicaciones

Plataforma web integral para la administración estratégica de proyectos, usuarios con control de acceso por roles, bitácora de auditoría y portal corporativo (inspirado en afrocomunicaciones.co).

---

## 🚀 Requisitos del Entorno

- **Servidor Web:** Apache (vía XAMPP)
- **Lenguaje:** PHP 8.2 o superior
- **Base de Datos:** MySQL / MariaDB (vía XAMPP)

---

## 📦 Puesta en Marcha (2 Pasos)

1. **Iniciar XAMPP:**
   - Abre el panel de control de XAMPP y activa **Apache** y **MySQL** (ambos en verde).

2. **Inicializar Base de Datos:**
   - Abre en tu navegador: [http://localhost/CORES/install.php](http://localhost/CORES/install.php)
   - Haz clic en **"Inicializar Base de Datos"**.
   - El sistema creará automáticamente la base de datos `CORES`, todas las tablas, los roles y los datos iniciales de prueba.

---

## 🔑 Credenciales de Acceso

- **URL de la plataforma:** [http://localhost/CORES/](http://localhost/CORES/)
- **Usuario Administrador:** `admin@cores.com`
- **Contraseña:** `admin123`

---

## 🛠️ Módulos y Funcionalidades

| Módulo | Descripción |
|---|---|
| **Landing Page** | Portal público corporativo con Hero, Servicios, Métricas, Misión/Visión y Contacto. |
| **Autenticación** | Login, registro y cierre de sesión seguro con contraseñas hasheadas en `bcrypt`. |
| **Dashboard** | Tarjetas de indicadores numéricos (KPIs), gráficas interactivas con Chart.js y accesos rápidos. |
| **Proyectos (CRUD)** | Creación, listado, control de progreso (%), presupuestos y estados. |
| **Usuarios y Roles** | Administración de cuentas con roles: `Administrador`, `Operador` y `Consulta`. |
| **Auditoría** | Trazabilidad de acciones (quién realizó qué acción, fecha, hora e IP). |
| **Reportes** | Exportación de base de datos a formato `.csv` descargable en 1 clic. |

---

## 📂 Estructura del Código

```text
C:\xampp\htdocs\CORES/
├── config/
│   └── database.php          # Conexión PDO a MySQL
├── database/
│   └── schema.sql            # Esquema SQL y datos semilla
├── install.php               # Asistente de instalación web automática
├── index.php                 # Plataforma completa (Landing, Router, Dashboard y CRUDs)
└── README.md                 # Documentación técnica y de usuario
```
