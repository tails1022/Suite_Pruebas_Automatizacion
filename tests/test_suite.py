"""
Suite de Pruebas Automatizadas E2E - Taller Universitario de Calidad de Software
Herramienta: Playwright (Python)
Aplicación bajo prueba: TaskQA (PHP + PostgreSQL)
Ruta: http://localhost:8080/gestion_qa/
"""

import os
import pytest
from playwright.sync_api import sync_playwright, expect

BASE_URL = "http://localhost:8080/gestion_qa"
EVIDENCES_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "evidences"))

os.makedirs(EVIDENCES_DIR, exist_ok=True)


@pytest.fixture(scope="session")
def browser_context():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(viewport={"width": 1280, "height": 800})
        yield context
        browser.close()


@pytest.fixture(scope="function")
def page(browser_context):
    page = browser_context.new_page()
    yield page
    page.close()


def test_cp01_login_exitoso(page):
    """
    CP01: Autenticación exitosa con credenciales válidas.
    Precondición: Usuario 'admin' registrado en base de datos PostgreSQL.
    Resultado esperado: Redirección al Dashboard con bienvenida al usuario.
    """
    page.goto(f"{BASE_URL}/login.php")
    page.wait_for_load_state("networkidle")

    page.fill("#username", "admin")
    page.fill("#password", "admin123")
    page.click("#btn-login")

    page.wait_for_url(f"{BASE_URL}/index.php")
    expect(page.locator("#user-display-name")).to_contain_text("Administrador QA")
    expect(page.locator("#tasks-table")).to_be_visible()

    evidence_path = os.path.join(EVIDENCES_DIR, "CP01_login_exitoso.png")
    page.screenshot(path=evidence_path, full_page=True)
    assert os.path.exists(evidence_path)


def test_cp02_login_credenciales_invalidas(page):
    """
    CP02: Autenticación rechazada por credenciales incorrectas.
    Precondición: Formulario de login cargado.
    Resultado esperado: Mensaje de alerta en rojo 'Credenciales inválidas'.
    """
    page.goto(f"{BASE_URL}/login.php")
    page.wait_for_load_state("networkidle")

    page.fill("#username", "usuario_no_existente")
    page.fill("#password", "clave_invalida_999")
    page.click("#btn-login")

    error_alert = page.locator("#login-alert-error")
    expect(error_alert).to_be_visible()
    expect(error_alert).to_contain_text("Credenciales inválidas")

    evidence_path = os.path.join(EVIDENCES_DIR, "CP02_login_fallido.png")
    page.screenshot(path=evidence_path, full_page=True)
    assert os.path.exists(evidence_path)


def test_cp03_login_campos_vacios(page):
    """
    CP03: Validación de campos obligatorios en el inicio de sesión.
    Precondición: Formulario de login cargado sin datos.
    Resultado esperado: Alerta indicando que todos los campos son obligatorios.
    """
    page.goto(f"{BASE_URL}/login.php")
    page.wait_for_load_state("networkidle")

    # Intentar enviar sin completar campos (simulando envío forzado/novalidate)
    page.click("#btn-login")

    error_alert = page.locator("#login-alert-error")
    expect(error_alert).to_be_visible()
    expect(error_alert).to_contain_text("Por favor complete todos los campos obligatorios")

    evidence_path = os.path.join(EVIDENCES_DIR, "CP03_login_validacion.png")
    page.screenshot(path=evidence_path, full_page=True)
    assert os.path.exists(evidence_path)


def test_cp04_crear_tarea_exitosa(page):
    """
    CP04: Creación y registro de una nueva tarea funcional.
    Precondición: Sesión iniciada como 'admin'.
    Resultado esperado: La tarea se guarda en PostgreSQL y se renderiza en la tabla.
    """
    # Iniciar sesión
    page.goto(f"{BASE_URL}/login.php")
    page.fill("#username", "admin")
    page.fill("#password", "admin123")
    page.click("#btn-login")
    page.wait_for_url(f"{BASE_URL}/index.php")

    # Abrir modal de creación
    page.click("#btn-new-task")
    expect(page.locator("#taskModal")).to_be_visible()

    # Diligenciar datos
    task_title = "Automatizar pruebas E2E con Playwright"
    task_desc = "Implementación del script de pruebas funcionales para entrega del taller universitario de Calidad de Software."
    page.fill("#task-title", task_title)
    page.fill("#task-desc", task_desc)
    page.select_option("#task-priority", "Alta")
    page.select_option("#task-status", "En Progreso")

    page.click("#btn-save-task")

    # Esperar confirmación
    global_alert = page.locator("#global-alert")
    expect(global_alert).to_be_visible()
    expect(global_alert).to_contain_text("exitosa")

    # Validar que aparezca en la tabla
    expect(page.locator("#tasks-table")).to_contain_text(task_title)

    evidence_path = os.path.join(EVIDENCES_DIR, "CP04_crear_tarea.png")
    page.screenshot(path=evidence_path, full_page=True)
    assert os.path.exists(evidence_path)


def test_cp05_validacion_creacion_sin_titulo(page):
    """
    CP05: Validación de campos obligatorios en formulario de tareas.
    Precondición: Modal de creación abierto con título en blanco.
    Resultado esperado: Mensaje de error 'El título de la tarea es obligatorio.'
    """
    page.goto(f"{BASE_URL}/login.php")
    page.fill("#username", "admin")
    page.fill("#password", "admin123")
    page.click("#btn-login")
    page.wait_for_url(f"{BASE_URL}/index.php")

    page.click("#btn-new-task")
    expect(page.locator("#taskModal")).to_be_visible()

    # Dejar título vacío e intentar guardar
    page.fill("#task-title", "")
    page.click("#btn-save-task")

    form_error = page.locator("#form-feedback-error")
    expect(form_error).to_be_visible()
    expect(form_error).to_contain_text("El título de la tarea es obligatorio")

    evidence_path = os.path.join(EVIDENCES_DIR, "CP05_validacion_formulario.png")
    page.screenshot(path=evidence_path, full_page=True)
    assert os.path.exists(evidence_path)


def test_cp06_filtrado_y_busqueda(page):
    """
    CP06: Búsqueda dinámica y filtrado por estado.
    Precondición: Múltiples tareas existentes en la tabla.
    Resultado esperado: La tabla filtra en tiempo real según el criterio ingresado.
    """
    page.goto(f"{BASE_URL}/login.php")
    page.fill("#username", "admin")
    page.fill("#password", "admin123")
    page.click("#btn-login")
    page.wait_for_url(f"{BASE_URL}/index.php")

    # Probar búsqueda por texto
    page.fill("#search-input", "Automatizar")
    page.wait_for_timeout(300)
    expect(page.locator("#tasks-tbody")).to_contain_text("Automatizar")

    # Limpiar búsqueda y probar filtro por estado 'Completada'
    page.fill("#search-input", "")
    page.select_option("#filter-status", "Completada")
    page.wait_for_timeout(300)

    # Validar que las filas mostradas tengan badge de Completada
    status_badges = page.locator(".task-status-badge")
    count = status_badges.count()
    assert count > 0
    for i in range(count):
        expect(status_badges.nth(i)).to_have_text("Completada")

    evidence_path = os.path.join(EVIDENCES_DIR, "CP06_filtro_busqueda.png")
    page.screenshot(path=evidence_path, full_page=True)
    assert os.path.exists(evidence_path)


def test_cp07_editar_tarea(page):
    """
    CP07: Modificación y actualización de un registro existente.
    Precondición: Tarea previa creada en la base de datos.
    Resultado esperado: Actualización de datos reflejada inmediatamente en la UI y DB.
    """
    page.goto(f"{BASE_URL}/login.php")
    page.fill("#username", "admin")
    page.fill("#password", "admin123")
    page.click("#btn-login")
    page.wait_for_url(f"{BASE_URL}/index.php")

    # Seleccionar la primera tarea para editar
    page.select_option("#filter-status", "Todos")
    page.fill("#search-input", "Automatizar pruebas E2E")
    page.wait_for_timeout(300)

    first_edit_btn = page.locator(".btn-edit").first
    first_edit_btn.click()

    expect(page.locator("#taskModal")).to_be_visible()
    page.fill("#task-title", "Automatizar pruebas E2E con Playwright [VERIFICADO]")
    page.select_option("#task-status", "Completada")
    page.click("#btn-save-task")

    global_alert = page.locator("#global-alert")
    expect(global_alert).to_be_visible()
    expect(global_alert).to_contain_text("actualizada")

    # Validar cambio en la tabla
    expect(page.locator("#tasks-tbody")).to_contain_text("[VERIFICADO]")

    evidence_path = os.path.join(EVIDENCES_DIR, "CP07_editar_tarea.png")
    page.screenshot(path=evidence_path, full_page=True)
    assert os.path.exists(evidence_path)


def test_cp08_eliminar_tarea_y_logout(page):
    """
    CP08: Eliminación controlada de registro mediante modal y cierre de sesión.
    Precondición: Tarea con '[VERIFICADO]' existente.
    Resultado esperado: La tarea se elimina de la tabla y la sesión se destruye correctamente.
    """
    page.goto(f"{BASE_URL}/login.php")
    page.fill("#username", "admin")
    page.fill("#password", "admin123")
    page.click("#btn-login")
    page.wait_for_url(f"{BASE_URL}/index.php")

    page.fill("#search-input", "[VERIFICADO]")
    page.wait_for_timeout(300)

    # Abrir modal de confirmación de eliminación
    delete_btn = page.locator(".btn-delete").first
    delete_btn.click()
    expect(page.locator("#deleteModal")).to_be_visible()

    # Confirmar eliminación
    page.click("#btn-confirm-delete")

    global_alert = page.locator("#global-alert")
    expect(global_alert).to_be_visible()
    expect(global_alert).to_contain_text("eliminada")

    # Cerrar sesión
    page.click("#btn-logout")
    page.wait_for_url(f"{BASE_URL}/login.php?logout=1")

    logout_alert = page.locator("#login-alert-success")
    expect(logout_alert).to_be_visible()
    expect(logout_alert).to_contain_text("Sesión cerrada exitosamente")

    evidence_path = os.path.join(EVIDENCES_DIR, "CP08_eliminar_logout.png")
    page.screenshot(path=evidence_path, full_page=True)
    assert os.path.exists(evidence_path)
