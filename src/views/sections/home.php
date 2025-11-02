<!-- Sección de inicio -->
<div class="user-welcome mb-4">
    <h2>¡Bienvenido de nuevo, <?= htmlspecialchars($user['username']) ?>!</h2>
    <p>Último acceso: <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Primer acceso' ?></p>
</div>

<div class="row">
    <!-- Estadísticas -->
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-icon events">
                    <i class="fas fa-calendar"></i>
                </div>
                <h5>Eventos Activos</h5>
                <h3><?= $eventos_count ?></h3>
                <a href="?section=eventos" class="btn btn-sm btn-action mt-2">Ver Eventos</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-icon photos">
                    <i class="fas fa-images"></i>
                </div>
                <h5>Total de Fotos</h5>
                <h3><?= $fotos_count ?></h3>
                <a href="?section=galeria" class="btn btn-sm btn-action mt-2">Ver Galería</a>
            </div>
        </div>
    </div>
    <?php if ($user['rol'] === 'admin'): ?>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-icon users">
                    <i class="fas fa-users"></i>
                </div>
                <h5>Usuarios Activos</h5>
                <h3><?= $usuarios_count ?></h3>
                <a href="?section=usuarios" class="btn btn-sm btn-action mt-2">Ver Usuarios</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Acciones Rápidas -->
<div class="quick-actions mt-4">
    <h4 class="mb-4">Acciones Rápidas</h4>
    <div class="row">
        <div class="col-md-4 mb-3">
            <a href="?section=eventos&action=new" class="action-button">
                <i class="fas fa-plus"></i>
                <span>Nuevo Evento</span>
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="?section=galeria&action=upload" class="action-button">
                <i class="fas fa-upload"></i>
                <span>Subir Fotos</span>
            </a>
        </div>
        <?php if ($user['rol'] === 'admin'): ?>
        <div class="col-md-4 mb-3">
            <a href="?section=usuarios&action=new" class="action-button">
                <i class="fas fa-user-plus"></i>
                <span>Nuevo Usuario</span>
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Eventos Próximos -->
<div class="card mt-4">
    <div class="card-body">
        <h4 class="card-title">Próximos Eventos</h4>
        <div class="table-responsive">
            <?php
            $result = $conn->query("SELECT * FROM eventos WHERE estado = 'programado' AND fecha >= NOW() ORDER BY fecha ASC LIMIT 5");
            $proximos_eventos = $result->fetch_all(MYSQLI_ASSOC);
            ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proximos_eventos as $evento): ?>
                    <tr>
                        <td><?= htmlspecialchars($evento['titulo']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($evento['fecha'])) ?></td>
                        <td>
                            <span class="badge bg-success">
                                <?= ucfirst($evento['estado']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($proximos_eventos)): ?>
                    <tr>
                        <td colspan="3" class="text-center">No hay eventos próximos</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>