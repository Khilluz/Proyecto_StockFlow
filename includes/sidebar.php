<aside class="sidebar" id="appSidebar">
    <a class="brand" href="dashboard.php"><span class="brand-mark">S</span><span>Stock<span>Flow</span></span></a>
    <div class="sidebar-label">Workspace</div>
    <nav class="sidebar-nav" aria-label="Navegación principal">
        <a class="<?= $activePage === 'dashboard' ? 'active' : '' ?>" href="dashboard.php"><span class="nav-icon">⌂</span>Dashboard</a>
        <?php if (in_array(($user['role'] ?? ''), ['Administrador', 'Repositor'], true)): ?><a class="<?= $activePage === 'inventario' ? 'active' : '' ?>" href="inventario.php"><span class="nav-icon">▦</span>Inventario</a><?php endif; ?>
        <?php if (in_array(($user['role'] ?? ''), ['Administrador', 'Vendedor'], true)): ?><a class="<?= $activePage === 'vendedor' ? 'active' : '' ?>" href="vendedor.php"><span class="nav-icon">＋</span>Ventas</a><?php endif; ?>
        <?php if (($user['role'] ?? '') === 'Administrador'): ?><a class="<?= $activePage === 'gastos' ? 'active' : '' ?>" href="gastos.php"><span class="nav-icon">↘</span>Gastos</a><?php endif; ?>
        <a class="<?= $activePage === 'reportes' ? 'active' : '' ?>" href="reportes.php"><span class="nav-icon">▤</span>Reportes</a>
        <?php if (in_array(($user['role'] ?? ''), ['Administrador', 'Repositor'], true)): ?><a class="<?= $activePage === 'repositor' ? 'active' : '' ?>" href="repositor.php"><span class="nav-icon">↥</span>Reposición</a><?php endif; ?>
        <?php if (($user['role'] ?? '') === 'Administrador'): ?><a class="<?= $activePage === 'usuarios' ? 'active' : '' ?>" href="usuarios.php"><span class="nav-icon">♙</span>Usuarios</a><?php endif; ?>
    </nav>
    <div class="sidebar-footer"><a href="logout.php"><span class="nav-icon">⇥</span>Cerrar sesión</a></div>
</aside>