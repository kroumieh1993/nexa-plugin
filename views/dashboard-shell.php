<?php
// We assume the following variables exist in scope:
// $messages, $properties, $total_properties, $properties_this_week, $current_user
?>
<div class="nexa-agency-shell">
    <div class="nexa-agency-layout">
        <aside class="nexa-sidebar">
            <div class="nexa-sidebar-header">
                <div class="nexa-sidebar-logo-circle">N</div>
                <div>
                    <div class="nexa-sidebar-title">Nexa Property Suite</div>
                    <div class="nexa-sidebar-subtitle">Agency Admin</div>
                </div>
            </div>

            <nav class="nexa-sidebar-nav">
                <a class="nexa-nav-link nexa-nav-link-active">
                    <span class="nexa-nav-icon">▢</span>
                    <span>Dashboard</span>
                </a>
                <a class="nexa-nav-link">
                    <span class="nexa-nav-icon">▥</span>
                    <span>Properties</span>
                </a>
            </nav>

            <div class="nexa-sidebar-section-label">SYSTEM</div>
            <nav class="nexa-sidebar-nav">
                <a class="nexa-nav-link">
                    <span class="nexa-nav-icon">⚙</span>
                    <span>Settings</span>
                </a>
            </nav>

            <div class="nexa-sidebar-footer">
                © <?php echo esc_html( date( 'Y' ) ); ?> Nexa
            </div>
        </aside>

        <main class="nexa-main">
            <header class="nexa-topbar">
                <div class="nexa-topbar-title">Dashboard</div>
                <div class="nexa-topbar-right">
                    <div class="nexa-topbar-search">
                        <span class="nexa-topbar-search-icon">🔍</span>
                        <input type="text" placeholder="Search (coming soon)" disabled>
                    </div>
                    <div class="nexa-topbar-user">
                        <div class="nexa-user-avatar">
                            <?php echo esc_html( strtoupper( mb_substr( $current_user->display_name, 0, 1 ) ) ); ?>
                        </div>
                        <div class="nexa-user-meta">
                            <div class="nexa-user-name"><?php echo esc_html( $current_user->display_name ); ?></div>
                            <div class="nexa-user-role">Admin</div>
                        </div>
                    </div>
                </div>
            </header>

            <?php if ( ! empty( $messages ) ) : ?>
                <div class="nexa-messages">
                    <?php foreach ( $messages as $msg ) : ?>
                        <div class="nexa-banner nexa-banner-<?php echo esc_attr( $msg['type'] ); ?>">
                            <?php echo esc_html( $msg['text'] ); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <section class="nexa-stats-row">
                <div class="nexa-stat-card">
                    <div class="nexa-stat-label">TOTAL PROPERTIES</div>
                    <div class="nexa-stat-value"><?php echo esc_html( $total_properties ); ?></div>
                    <div class="nexa-stat-sub">Across your agency</div>
                </div>

                <div class="nexa-stat-card">
                    <div class="nexa-stat-label">THIS WEEK (PROPERTIES)</div>
                    <div class="nexa-stat-value"><?php echo esc_html( $properties_this_week ); ?></div>
                    <div class="nexa-stat-sub">New properties added</div>
                </div>

                <div class="nexa-stat-card nexa-stat-card-highlight">
                    <div class="nexa-stat-label">SYSTEM HEALTH</div>
                    <div class="nexa-stat-value">99.9%</div>
                    <div class="nexa-stat-sub">API uptime (placeholder)</div>
                </div>
            </section>

            <?php include NEXA_RE_PLUGIN_DIR . 'views/dashboard-properties.php'; ?>
        </main>
    </div>
</div>