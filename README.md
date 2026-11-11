# Suite de Pruebas Automatizadas Funcionales (TaskQA)

**Corporación Tecnológica del Oriente**  
**Facultad de Ingeniería | Programa de Ingeniería de Software**  
**Curso:** Pruebas y Calidad del Software  
**Estudiante:** Juan Pablo López Diaz  

*Módulo Web:* PHP 8.4 + Apache 2.4 (Puerto 8080) + PostgreSQL 16 (Puerto 5432)  
*Herramienta de Automatización:* Playwright (Python) + pytest  

---

## 🎯 Descripción del Proyecto
Este repositorio contiene el código fuente funcional del aplicativo web **TaskQA** y la suite completa de pruebas automatizadas de extremo a extremo (E2E) desarrollada con **Playwright** y **pytest**.

---

## 📁 Estructura del Código
```
├── app/                        # Módulo web bajo prueba (SUT)
│   ├── config.php              # Conexión PDO a PostgreSQL 16
│   ├── schema.sql              # Estructura DDL de base de datos y datos semilla
│   ├── login.php               # Vista de login y autenticación de usuarios
│   ├── index.php               # Dashboard y módulo de gestión CRUD
│   ├── api.php                 # Endpoints REST para peticiones AJAX
│   └── logout.php              # Destrucción de sesiones
├── tests/                      # Suite de pruebas automatizadas E2E
│   ├── test_suite.py           # 8 Casos de prueba automatizados (CP01 a CP08)
│   └── conftest.py             # Configuración y hooks de pytest
├── .gitignore                  # Exclusión de evidencias y reportes
└── README.md                   # Documentación técnica de ejecución
```

---

## 🚀 Guía de Instalación y Ejecución

### 1. Prerrequisitos
- Servidor **Apache 2.4** escuchando en el puerto `8080` (con PHP 8.4 y módulo `pdo_pgsql` activo).
- Servidor de base de datos **PostgreSQL 16** en el puerto `5432`.
- **Python 3.10+**.

### 2. Base de Datos
Crear la base de datos `gestion_qa_db` y ejecutar el script DDL:
```sql
-- Ejecutar en PostgreSQL
\i app/schema.sql
```
*Credenciales de prueba:* Usuario: `admin` / Contraseña: `admin123`.

### 3. Instalación de Dependencias de Prueba
```powershell
pip install playwright pytest pytest-html
python -m playwright install chromium
```

### 4. Ejecución de la Suite de Pruebas
Para ejecutar las pruebas en consola:
```powershell
python -m pytest tests/test_suite.py -v
```

---

## 📊 Matriz de Casos de Prueba Implementados
| ID | Caso de Prueba | Tipo | Descripción |
|---|---|---|---|
| **CP01** | Autenticación Exitosa | Positivo | Valida login con credenciales válidas y redirección a dashboard. |
| **CP02** | Autenticación Rechazada | Negativo | Valida mensaje de error ante usuario/clave inválidos. |
| **CP03** | Validación de Campos Vacíos | Validación | Valida prevención de envío sin campos obligatorios. |
| **CP04** | Creación de Tarea | Positivo | Valida registro y renderizado dinámico en la tabla. |
| **CP05** | Validación Formulario sin Título | Negativo | Valida mensajes de error en modal al omitir datos requeridos. |
| **CP06** | Búsqueda y Filtros en Tiempo Real | Funcional | Valida filtrado instantáneo por texto y estado de tarea. |
| **CP07** | Edición y Actualización | Positivo | Valida cambio de estado y actualización reactiva en BD/UI. |
| **CP08** | Eliminación y Logout | Positivo | Valida modal de confirmación, eliminación física y cierre de sesión. |
