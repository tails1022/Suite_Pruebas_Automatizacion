<?php
require_once 'config.php';
check_auth();

$nombre = $_SESSION['nombre'] ?? 'Usuario';
$rol = $_SESSION['rol'] ?? 'Tester';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskQA - Panel de Gestión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-custom {
            background: #1e3c72;
        }
        .badge-alta { background-color: #dc3545; color: white; }
        .badge-media { background-color: #fd7e14; color: white; }
        .badge-baja { background-color: #198754; color: white; }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
                <i class="bi bi-shield-check me-2 fs-4"></i> TaskQA
            </a>
            <div class="d-flex align-items-center text-white">
                <div class="me-3 text-end d-none d-sm-block">
                    <span id="user-display-name" class="fw-semibold d-block"><?= htmlspecialchars($nombre) ?></span>
                    <small class="badge bg-light text-dark"><?= htmlspecialchars($rol) ?></small>
                </div>
                <a href="logout.php" id="btn-logout" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Notificación Global -->
        <div id="global-alert" class="alert alert-success alert-dismissible fade d-none" role="alert">
            <span id="global-alert-message"></span>
            <button type="button" class="btn-close" onclick="hideAlert()"></button>
        </div>

        <!-- Encabezado y Acciones -->
        <div class="card card-custom p-4 mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <h4 class="fw-bold mb-1 text-primary">Módulo de Gestión de Tareas</h4>
                    <p class="text-muted small mb-0">Gestión de casos y asignaciones para el equipo de Calidad de Software.</p>
                </div>
                <div class="col-md-7 d-flex flex-wrap gap-2 justify-content-md-end">
                    <button id="btn-new-task" class="btn btn-primary fw-semibold" onclick="openCreateModal()">
                        <i class="bi bi-plus-circle me-1"></i> Nueva Tarea
                    </button>
                </div>
            </div>

            <hr class="my-3 text-secondary">

            <!-- Filtros y Búsqueda -->
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" id="search-input" class="form-control" placeholder="Buscar tarea por título o descripción..." oninput="filterTasks()">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-funnel"></i></span>
                        <select id="filter-status" class="form-select" onchange="filterTasks()">
                            <option value="Todos">Todos los Estados</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="En Progreso">En Progreso</option>
                            <option value="Completada">Completada</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 text-end">
                    <span id="task-counter" class="badge bg-secondary py-2 px-3 fs-6">0 tareas</span>
                </div>
            </div>
        </div>

        <!-- Tabla de Tareas -->
        <div class="card card-custom p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tasks-table">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">ID</th>
                            <th scope="col">Título</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Prioridad</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Fecha Límite</th>
                            <th scope="col" class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tasks-tbody">
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Cargando tareas...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Crear / Editar Tarea -->
    <div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="taskModalLabel">Nueva Tarea</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="form-feedback-error" class="alert alert-danger d-none mb-3"></div>

                    <form id="task-form" onsubmit="event.preventDefault(); submitTask();">
                        <input type="hidden" id="task-id" value="0">

                        <div class="mb-3">
                            <label for="task-title" class="form-label fw-semibold">Título de la Tarea <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="task-title" placeholder="Ej. Validar flujo de checkout" required>
                        </div>

                        <div class="mb-3">
                            <label for="task-desc" class="form-label fw-semibold">Descripción</label>
                            <textarea class="form-control" id="task-desc" rows="3" placeholder="Detalle técnico o pasos..."></textarea>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label for="task-priority" class="form-label fw-semibold">Prioridad</label>
                                <select class="form-select" id="task-priority">
                                    <option value="Alta">Alta</option>
                                    <option value="Media" selected>Media</option>
                                    <option value="Baja">Baja</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="task-status" class="form-label fw-semibold">Estado</label>
                                <select class="form-select" id="task-status">
                                    <option value="Pendiente" selected>Pendiente</option>
                                    <option value="En Progreso">En Progreso</option>
                                    <option value="Completada">Completada</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="task-deadline" class="form-label fw-semibold">Fecha Límite <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="task-deadline" required>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" id="btn-save-task" class="btn btn-primary fw-semibold" onclick="submitTask()">
                                <i class="bi bi-save me-1"></i> Guardar Tarea
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Eliminación -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-trash3 text-danger display-4 mb-3 d-block"></i>
                    <h5 class="fw-bold mb-2">¿Eliminar Tarea?</h5>
                    <p class="text-muted small mb-4">Esta acción no se puede deshacer.</p>
                    <input type="hidden" id="delete-task-id" value="0">
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" id="btn-confirm-delete" class="btn btn-danger btn-sm fw-semibold" onclick="confirmDelete()">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let allTasks = [];
        const taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

        document.addEventListener('DOMContentLoaded', () => {
            loadTasks();
            // Default deadline: tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('task-deadline').value = tomorrow.toISOString().split('T')[0];
        });

        async function loadTasks() {
            try {
                const res = await fetch('api.php?action=list');
                const data = await res.json();
                if (data.success) {
                    allTasks = data.data;
                    renderTasks(allTasks);
                }
            } catch (err) {
                console.error(err);
            }
        }

        function renderTasks(tasks) {
            const tbody = document.getElementById('tasks-tbody');
            const counter = document.getElementById('task-counter');
            counter.innerText = `${tasks.length} tarea${tasks.length === 1 ? '' : 's'}`;

            if (tasks.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted" id="no-tasks-message">No se encontraron tareas registradas.</td></tr>`;
                return;
            }

            tbody.innerHTML = tasks.map(t => {
                const badgeClass = t.prioridad === 'Alta' ? 'badge-alta' : (t.prioridad === 'Media' ? 'badge-media' : 'badge-baja');
                const statusBadge = t.estado === 'Completada' ? 'bg-success' : (t.estado === 'En Progreso' ? 'bg-info text-dark' : 'bg-warning text-dark');
                return `
                    <tr id="row-task-${t.id}">
                        <td class="ps-4 fw-semibold text-muted">#${t.id}</td>
                        <td class="fw-bold text-dark task-title-cell">${escapeHtml(t.titulo)}</td>
                        <td class="text-muted small">${escapeHtml(t.descripcion || '-')}</td>
                        <td><span class="badge ${badgeClass}">${t.prioridad}</span></td>
                        <td><span class="badge ${statusBadge} task-status-badge">${t.estado}</span></td>
                        <td><small class="text-secondary"><i class="bi bi-calendar-event me-1"></i>${t.fecha_limite}</small></td>
                        <td class="text-end pe-4">
                            <button class="btn btn-outline-primary btn-sm me-1 btn-edit" onclick="openEditModal(${t.id})" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm btn-delete" onclick="openDeleteModal(${t.id})" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function filterTasks() {
            const query = document.getElementById('search-input').value.toLowerCase().trim();
            const status = document.getElementById('filter-status').value;

            const filtered = allTasks.filter(t => {
                const matchesQuery = t.titulo.toLowerCase().includes(query) || (t.descripcion && t.descripcion.toLowerCase().includes(query));
                const matchesStatus = status === 'Todos' || t.estado === status;
                return matchesQuery && matchesStatus;
            });

            renderTasks(filtered);
        }

        function openCreateModal() {
            document.getElementById('taskModalLabel').innerText = 'Nueva Tarea';
            document.getElementById('task-id').value = '0';
            document.getElementById('task-title').value = '';
            document.getElementById('task-desc').value = '';
            document.getElementById('task-priority').value = 'Media';
            document.getElementById('task-status').value = 'Pendiente';
            
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('task-deadline').value = tomorrow.toISOString().split('T')[0];

            hideFormError();
            taskModal.show();
        }

        async function openEditModal(id) {
            try {
                const res = await fetch(`api.php?action=get&id=${id}`);
                const data = await res.json();
                if (data.success) {
                    const t = data.data;
                    document.getElementById('taskModalLabel').innerText = 'Editar Tarea';
                    document.getElementById('task-id').value = t.id;
                    document.getElementById('task-title').value = t.titulo;
                    document.getElementById('task-desc').value = t.descripcion || '';
                    document.getElementById('task-priority').value = t.prioridad;
                    document.getElementById('task-status').value = t.estado;
                    document.getElementById('task-deadline').value = t.fecha_limite;

                    hideFormError();
                    taskModal.show();
                }
            } catch (err) {
                console.error(err);
            }
        }

        async function submitTask() {
            const id = document.getElementById('task-id').value;
            const titulo = document.getElementById('task-title').value.trim();
            const descripcion = document.getElementById('task-desc').value.trim();
            const prioridad = document.getElementById('task-priority').value;
            const estado = document.getElementById('task-status').value;
            const fecha_limite = document.getElementById('task-deadline').value;

            // Validación en cliente
            if (!titulo) {
                showFormError('El título de la tarea es obligatorio.');
                return;
            }
            if (!fecha_limite) {
                showFormError('La fecha límite es obligatoria.');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'save');
            formData.append('id', id);
            formData.append('titulo', titulo);
            formData.append('descripcion', descripcion);
            formData.append('prioridad', prioridad);
            formData.append('estado', estado);
            formData.append('fecha_limite', fecha_limite);

            try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    taskModal.hide();
                    showAlert(data.message || 'Operación completada con éxito.');
                    await loadTasks();
                    filterTasks();
                } else {
                    showFormError(data.error || 'Ocurrió un error al guardar la tarea.');
                }
            } catch (err) {
                showFormError('Error de conexión con el servidor.');
            }
        }

        function openDeleteModal(id) {
            document.getElementById('delete-task-id').value = id;
            deleteModal.show();
        }

        async function confirmDelete() {
            const id = document.getElementById('delete-task-id').value;
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    deleteModal.hide();
                    showAlert('Tarea eliminada exitosamente.');
                    await loadTasks();
                    filterTasks();
                } else {
                    alert(data.error || 'No se pudo eliminar la tarea.');
                }
            } catch (err) {
                alert('Error al conectar con el servidor.');
            }
        }

        function showFormError(msg) {
            const errDiv = document.getElementById('form-feedback-error');
            errDiv.innerText = msg;
            errDiv.classList.remove('d-none');
        }

        function hideFormError() {
            const errDiv = document.getElementById('form-feedback-error');
            errDiv.classList.add('d-none');
        }

        function showAlert(msg) {
            const alert = document.getElementById('global-alert');
            document.getElementById('global-alert-message').innerText = msg;
            alert.classList.remove('d-none');
            alert.classList.add('show');
            setTimeout(() => hideAlert(), 4000);
        }

        function hideAlert() {
            const alert = document.getElementById('global-alert');
            alert.classList.add('d-none');
            alert.classList.remove('show');
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>
</html>
