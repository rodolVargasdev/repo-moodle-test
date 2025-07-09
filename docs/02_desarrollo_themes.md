# 🎨 Desarrollo de Temas Médicos Personalizados

## 🎯 Objetivo
Crear un tema profesional y moderno para la plataforma médica que combine funcionalidad, estética y usabilidad, inspirado en Platzi pero adaptado al sector salud.

## 📐 Estructura del Tema MedicoTheme

### 1. Configuración Básica del Tema

**Crear directorio del tema**
```bash
mkdir -p themes/medico_theme
cd themes/medico_theme
```

**version.php**
```php
<?php
defined('MOODLE_INTERNAL') || die();

$plugin->version = 2025070701;
$plugin->requires = 2020110900;
$plugin->component = 'theme_medico_theme';
$plugin->dependencies = [
    'theme_boost' => 2020110900,
];
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.0.0';
```

**config.php**
```php
<?php
defined('MOODLE_INTERNAL') || die();

$THEME->name = 'medico_theme';
$THEME->doctype = 'html5';
$THEME->parents = ['boost'];
$THEME->enable_dock = false;
$THEME->yuicssmodules = [];
$THEME->editor_sheets = [];
$THEME->usefallback = true;
$THEME->precompiledcsscallback = 'theme_medico_theme_get_precompiled_css';
$THEME->prescsscallback = 'theme_medico_theme_get_pre_scss';
$THEME->extrascsscallback = 'theme_medico_theme_get_extra_scss';
$THEME->scss = function($theme) {
    return theme_medico_theme_get_main_scss_content($theme);
};
$THEME->layouts = [
    'base' => [
        'file' => 'drawers.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'standard' => [
        'file' => 'drawers.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'course' => [
        'file' => 'drawers.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'coursecategory' => [
        'file' => 'drawers.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'incourse' => [
        'file' => 'drawers.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'frontpage' => [
        'file' => 'frontpage.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'admin' => [
        'file' => 'drawers.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'mydashboard' => [
        'file' => 'medical_dashboard.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'mypublic' => [
        'file' => 'drawers.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'login' => [
        'file' => 'login.php',
        'regions' => [],
    ],
    'popup' => [
        'file' => 'popup.php',
        'regions' => [],
    ],
    'frametop' => [
        'file' => 'frametop.php',
        'regions' => [],
    ],
    'embedded' => [
        'file' => 'embedded.php',
        'regions' => [],
    ],
    'maintenance' => [
        'file' => 'maintenance.php',
        'regions' => [],
    ],
    'print' => [
        'file' => 'print.php',
        'regions' => [],
    ],
    'redirect' => [
        'file' => 'redirect.php',
        'regions' => [],
    ],
];
$THEME->javascripts = [];
$THEME->javascripts_footer = [];
$THEME->requiredblocks = '';
$THEME->addblockposition = BLOCK_ADDBLOCK_POSITION_FLATNAV;
$THEME->iconsystem = '\theme_medico_theme\output\icon_system_fontawesome';
$THEME->haseditswitch = true;
$THEME->usescourseindex = true;
$THEME->activityheaderconfig = [
    'notitle' => true,
];
```

### 2. Paleta de Colores y Diseño

**SCSS Variables - scss/variables.scss**
```scss
// Colores principales del tema médico
$primary-color: #2c5aa0;        // Azul médico (confianza)
$secondary-color: #28a745;      // Verde salud (bienestar)
$accent-color: #17a2b8;         // Cyan (tecnología)
$warning-color: #ffc107;        // Amarillo (atención)
$danger-color: #dc3545;         // Rojo (urgencia)
$info-color: #6c757d;           // Gris (información)
$success-color: #28a745;        // Verde (éxito)

// Colores de fondo
$bg-primary: #ffffff;           // Blanco principal
$bg-secondary: #f8f9fa;         // Gris claro
$bg-dark: #343a40;              // Gris oscuro
$bg-light: #e9ecef;             // Gris muy claro

// Colores de texto
$text-primary: #2c3e50;         // Gris oscuro para texto principal
$text-secondary: #6c757d;       // Gris medio para texto secundario
$text-muted: #adb5bd;           // Gris claro para texto desactivado
$text-white: #ffffff;           // Blanco para texto sobre fondos oscuros

// Colores específicos para especialidades médicas
$cardiology-color: #e74c3c;     // Rojo para cardiología
$neurology-color: #9b59b6;      // Púrpura para neurología
$oncology-color: #e67e22;       // Naranja para oncología
$pediatrics-color: #f39c12;     // Amarillo para pediatría
$surgery-color: #34495e;        // Azul oscuro para cirugía
$emergency-color: #c0392b;      // Rojo oscuro para emergencias

// Tipografía
$font-family-primary: 'Open Sans', sans-serif;
$font-family-headings: 'Roboto Slab', serif;
$font-family-code: 'Fira Code', monospace;

// Espaciado
$spacing-xs: 0.25rem;   // 4px
$spacing-sm: 0.5rem;    // 8px
$spacing-md: 1rem;      // 16px
$spacing-lg: 1.5rem;    // 24px
$spacing-xl: 2rem;      // 32px
$spacing-xxl: 3rem;     // 48px

// Sombras
$shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
$shadow-md: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);
$shadow-lg: 0 10px 20px rgba(0, 0, 0, 0.19), 0 6px 6px rgba(0, 0, 0, 0.23);
$shadow-xl: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);

// Bordes
$border-radius-sm: 0.125rem;    // 2px
$border-radius-md: 0.25rem;     // 4px
$border-radius-lg: 0.5rem;      // 8px
$border-radius-xl: 1rem;        // 16px
$border-radius-full: 50%;       // Circular

// Breakpoints
$breakpoint-xs: 0;
$breakpoint-sm: 576px;
$breakpoint-md: 768px;
$breakpoint-lg: 992px;
$breakpoint-xl: 1200px;
$breakpoint-xxl: 1400px;
```

### 3. Layout Principal - medical_dashboard.php

```php
<?php
defined('MOODLE_INTERNAL') || die();

// Obtener datos del usuario actual
$user_context = context_user::instance($USER->id);
$user_specialties = get_user_specialties($USER->id);
$user_progress = get_user_learning_progress($USER->id);
$upcoming_certifications = get_upcoming_certifications($USER->id);

// Preparar datos para el template
$templatecontext = [
    'sitename' => $SITE->shortname,
    'output' => $OUTPUT,
    'sidepreblocks' => $OUTPUT->blocks('side-pre'),
    'hasblocks' => true,
    'bodyattributes' => $OUTPUT->body_attributes(),
    'navdraweropen' => $navdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'user' => $USER,
    'user_specialties' => $user_specialties,
    'user_progress' => $user_progress,
    'upcoming_certifications' => $upcoming_certifications,
    'current_courses' => get_user_current_courses($USER->id),
    'recommended_courses' => get_recommended_courses($USER->id),
    'recent_activity' => get_recent_activity($USER->id),
    'achievements' => get_user_achievements($USER->id),
    'learning_stats' => get_learning_statistics($USER->id),
];

echo $OUTPUT->render_from_template('theme_medico_theme/medical_dashboard', $templatecontext);
```

### 4. Templates Mustache

**templates/medical_dashboard.mustache**
```mustache
<!DOCTYPE html>
<html {{{ output.htmlattributes }}}>
<head>
    <title>{{sitename}} - Dashboard Médico</title>
    <link rel="shortcut icon" href="{{output.favicon}}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&family=Roboto+Slab:wght@300;400;600;700&display=swap" rel="stylesheet">
    {{{ output.standard_head_html }}}
</head>
<body {{{ bodyattributes }}}>
    {{{ output.standard_top_of_body_html }}}
    
    <!-- Header médico -->
    <header class="medical-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <div class="medical-logo">
                        <img src="{{output.image_url('logo-medical', 'theme')}}" alt="{{sitename}}" class="img-fluid">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="medical-search">
                        <form class="search-form" method="get" action="{{config.wwwroot}}/course/search.php">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Buscar cursos, especialidades, procedimientos...">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="medical-user-menu">
                        <div class="user-info">
                            <img src="{{{ user.picture }}}" alt="{{user.fullname}}" class="user-avatar">
                            <div class="user-details">
                                <span class="user-name">{{user.fullname}}</span>
                                <span class="user-role">{{user.medical_specialty}}</span>
                            </div>
                        </div>
                        <div class="user-menu-dropdown">
                            <a href="{{config.wwwroot}}/user/profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i> Mi Perfil
                            </a>
                            <a href="{{config.wwwroot}}/user/preferences.php" class="dropdown-item">
                                <i class="fas fa-cog"></i> Configuración
                            </a>
                            <a href="{{config.wwwroot}}/login/logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Navegación principal -->
    <nav class="medical-navigation">
        <div class="container-fluid">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link active" href="{{config.wwwroot}}/my/">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{config.wwwroot}}/course/">
                        <i class="fas fa-book"></i> Mis Cursos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{config.wwwroot}}/local/learning_paths/">
                        <i class="fas fa-route"></i> Rutas de Aprendizaje
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{config.wwwroot}}/local/certifications/">
                        <i class="fas fa-certificate"></i> Certificaciones
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{config.wwwroot}}/mod/medical_simulation/">
                        <i class="fas fa-microscope"></i> Simuladores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{config.wwwroot}}/local/medical_library/">
                        <i class="fas fa-book-medical"></i> Biblioteca Médica
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{config.wwwroot}}/mod/forum/">
                        <i class="fas fa-users"></i> Comunidad
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Contenido principal del dashboard -->
    <main class="medical-dashboard-content">
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar con información del usuario -->
                <div class="col-md-3">
                    <div class="medical-sidebar">
                        <!-- Progreso general -->
                        <div class="progress-card">
                            <h5>Mi Progreso</h5>
                            <div class="progress-circle">
                                <svg width="120" height="120">
                                    <circle cx="60" cy="60" r="50" stroke="#e9ecef" stroke-width="8" fill="none"/>
                                    <circle cx="60" cy="60" r="50" stroke="#28a745" stroke-width="8" fill="none"
                                            stroke-dasharray="{{learning_stats.total_progress_percentage}} 100"
                                            stroke-linecap="round"/>
                                </svg>
                                <div class="progress-text">
                                    <span class="percentage">{{learning_stats.total_progress_percentage}}%</span>
                                    <span class="label">Completado</span>
                                </div>
                            </div>
                        </div>

                        <!-- Especialidades del usuario -->
                        <div class="specialties-card">
                            <h5>Mis Especialidades</h5>
                            {{#user_specialties}}
                            <div class="specialty-item">
                                <i class="fas fa-{{icon}}" style="color: {{color}}"></i>
                                <span>{{name}}</span>
                                <span class="specialty-level">Nivel {{level}}</span>
                            </div>
                            {{/user_specialties}}
                        </div>

                        <!-- Certificaciones próximas -->
                        <div class="certifications-card">
                            <h5>Próximas Certificaciones</h5>
                            {{#upcoming_certifications}}
                            <div class="certification-item">
                                <div class="cert-icon">
                                    <i class="fas fa-certificate"></i>
                                </div>
                                <div class="cert-info">
                                    <span class="cert-name">{{name}}</span>
                                    <span class="cert-date">{{due_date}}</span>
                                </div>
                            </div>
                            {{/upcoming_certifications}}
                        </div>
                    </div>
                </div>

                <!-- Contenido principal -->
                <div class="col-md-9">
                    <div class="dashboard-main">
                        <!-- Bienvenida personalizada -->
                        <div class="welcome-section">
                            <h1>Hola, {{user.firstname}}!</h1>
                            <p class="lead">Continúa tu formación médica profesional</p>
                        </div>

                        <!-- Estadísticas rápidas -->
                        <div class="quick-stats">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="stat-card">
                                        <div class="stat-icon">
                                            <i class="fas fa-book" style="color: #2c5aa0;"></i>
                                        </div>
                                        <div class="stat-info">
                                            <h3>{{learning_stats.total_courses}}</h3>
                                            <p>Cursos en Progreso</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stat-card">
                                        <div class="stat-icon">
                                            <i class="fas fa-clock" style="color: #28a745;"></i>
                                        </div>
                                        <div class="stat-info">
                                            <h3>{{learning_stats.total_hours}}</h3>
                                            <p>Horas Completadas</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stat-card">
                                        <div class="stat-icon">
                                            <i class="fas fa-trophy" style="color: #ffc107;"></i>
                                        </div>
                                        <div class="stat-info">
                                            <h3>{{learning_stats.total_achievements}}</h3>
                                            <p>Logros Obtenidos</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stat-card">
                                        <div class="stat-icon">
                                            <i class="fas fa-users" style="color: #17a2b8;"></i>
                                        </div>
                                        <div class="stat-info">
                                            <h3>{{learning_stats.ranking_position}}</h3>
                                            <p>Posición en Ranking</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cursos actuales -->
                        <div class="current-courses-section">
                            <h2>Continúa donde lo dejaste</h2>
                            <div class="courses-grid">
                                {{#current_courses}}
                                <div class="course-card">
                                    <div class="course-image">
                                        <img src="{{image_url}}" alt="{{fullname}}">
                                        <div class="course-progress-overlay">
                                            <div class="progress">
                                                <div class="progress-bar" style="width: {{progress_percentage}}%"></div>
                                            </div>
                                            <span class="progress-text">{{progress_percentage}}%</span>
                                        </div>
                                    </div>
                                    <div class="course-info">
                                        <h4>{{fullname}}</h4>
                                        <p class="course-description">{{summary}}</p>
                                        <div class="course-meta">
                                            <span class="course-specialty">{{specialty}}</span>
                                            <span class="course-duration">{{duration}} min</span>
                                        </div>
                                        <a href="{{view_url}}" class="btn btn-primary">Continuar</a>
                                    </div>
                                </div>
                                {{/current_courses}}
                            </div>
                        </div>

                        <!-- Recomendaciones -->
                        <div class="recommendations-section">
                            <h2>Recomendado para ti</h2>
                            <div class="recommendations-grid">
                                {{#recommended_courses}}
                                <div class="recommendation-card">
                                    <div class="recommendation-image">
                                        <img src="{{image_url}}" alt="{{fullname}}">
                                    </div>
                                    <div class="recommendation-info">
                                        <h4>{{fullname}}</h4>
                                        <p>{{summary}}</p>
                                        <div class="recommendation-meta">
                                            <span class="specialty">{{specialty}}</span>
                                            <span class="level">{{level}}</span>
                                        </div>
                                        <a href="{{view_url}}" class="btn btn-outline-primary">Ver Curso</a>
                                    </div>
                                </div>
                                {{/recommended_courses}}
                            </div>
                        </div>

                        <!-- Actividad reciente -->
                        <div class="recent-activity-section">
                            <h2>Actividad Reciente</h2>
                            <div class="activity-timeline">
                                {{#recent_activity}}
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-{{icon}}"></i>
                                    </div>
                                    <div class="activity-content">
                                        <h5>{{title}}</h5>
                                        <p>{{description}}</p>
                                        <small class="text-muted">{{time_ago}}</small>
                                    </div>
                                </div>
                                {{/recent_activity}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="medical-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2025 {{sitename}}. Todos los derechos reservados.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>Plataforma de Capacitación Médica</p>
                </div>
            </div>
        </div>
    </footer>

    {{{ output.standard_end_of_body_html }}}
</body>
</html>
```

### 5. Estilos SCSS - scss/main.scss

```scss
@import 'variables';
@import 'mixins';
@import 'components/buttons';
@import 'components/cards';
@import 'components/navigation';
@import 'components/dashboard';
@import 'components/forms';
@import 'components/courses';

// Estilos base
* {
    box-sizing: border-box;
}

body {
    font-family: $font-family-primary;
    color: $text-primary;
    background-color: $bg-secondary;
    line-height: 1.6;
}

h1, h2, h3, h4, h5, h6 {
    font-family: $font-family-headings;
    font-weight: 600;
    color: $text-primary;
}

// Header médico
.medical-header {
    background: linear-gradient(135deg, $primary-color 0%, darken($primary-color, 10%) 100%);
    color: $text-white;
    padding: $spacing-md 0;
    box-shadow: $shadow-md;

    .medical-logo img {
        max-height: 40px;
    }

    .medical-search {
        .search-form {
            .input-group {
                .form-control {
                    border-radius: $border-radius-lg 0 0 $border-radius-lg;
                    border: none;
                    padding: $spacing-sm $spacing-md;
                    
                    &:focus {
                        box-shadow: none;
                        border-color: $accent-color;
                    }
                }

                .btn {
                    border-radius: 0 $border-radius-lg $border-radius-lg 0;
                    background-color: $accent-color;
                    border-color: $accent-color;
                    
                    &:hover {
                        background-color: darken($accent-color, 10%);
                        border-color: darken($accent-color, 10%);
                    }
                }
            }
        }
    }

    .medical-user-menu {
        display: flex;
        align-items: center;
        justify-content: flex-end;

        .user-info {
            display: flex;
            align-items: center;
            gap: $spacing-sm;

            .user-avatar {
                width: 40px;
                height: 40px;
                border-radius: $border-radius-full;
                border: 2px solid $text-white;
            }

            .user-details {
                display: flex;
                flex-direction: column;

                .user-name {
                    font-weight: 600;
                    font-size: 14px;
                }

                .user-role {
                    font-size: 12px;
                    opacity: 0.8;
                }
            }
        }
    }
}

// Navegación principal
.medical-navigation {
    background-color: $bg-primary;
    border-bottom: 1px solid $bg-light;
    padding: $spacing-sm 0;

    .nav-pills {
        .nav-link {
            color: $text-secondary;
            font-weight: 500;
            padding: $spacing-sm $spacing-md;
            border-radius: $border-radius-lg;
            transition: all 0.3s ease;

            i {
                margin-right: $spacing-xs;
            }

            &:hover {
                background-color: $bg-light;
                color: $primary-color;
            }

            &.active {
                background-color: $primary-color;
                color: $text-white;
            }
        }
    }
}

// Dashboard principal
.medical-dashboard-content {
    padding: $spacing-lg 0;

    .medical-sidebar {
        .progress-card, .specialties-card, .certifications-card {
            background-color: $bg-primary;
            border-radius: $border-radius-lg;
            padding: $spacing-md;
            margin-bottom: $spacing-md;
            box-shadow: $shadow-sm;

            h5 {
                color: $text-primary;
                margin-bottom: $spacing-md;
                font-weight: 600;
            }
        }

        .progress-circle {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;

            svg {
                transform: rotate(-90deg);
                
                circle {
                    transition: stroke-dasharray 0.3s ease;
                }
            }

            .progress-text {
                position: absolute;
                text-align: center;

                .percentage {
                    font-size: 24px;
                    font-weight: 700;
                    color: $success-color;
                    display: block;
                }

                .label {
                    font-size: 12px;
                    color: $text-secondary;
                }
            }
        }

        .specialty-item {
            display: flex;
            align-items: center;
            gap: $spacing-sm;
            padding: $spacing-sm;
            margin-bottom: $spacing-xs;
            border-radius: $border-radius-md;
            transition: background-color 0.3s ease;

            &:hover {
                background-color: $bg-light;
            }

            .specialty-level {
                margin-left: auto;
                font-size: 12px;
                color: $text-secondary;
            }
        }

        .certification-item {
            display: flex;
            align-items: center;
            gap: $spacing-sm;
            padding: $spacing-sm;
            margin-bottom: $spacing-xs;
            border-radius: $border-radius-md;
            transition: background-color 0.3s ease;

            &:hover {
                background-color: $bg-light;
            }

            .cert-icon {
                width: 32px;
                height: 32px;
                border-radius: $border-radius-full;
                background-color: $warning-color;
                display: flex;
                align-items: center;
                justify-content: center;
                color: $text-white;
                font-size: 14px;
            }

            .cert-info {
                display: flex;
                flex-direction: column;

                .cert-name {
                    font-weight: 500;
                    font-size: 14px;
                }

                .cert-date {
                    font-size: 12px;
                    color: $text-secondary;
                }
            }
        }
    }

    .dashboard-main {
        .welcome-section {
            margin-bottom: $spacing-xl;

            h1 {
                color: $primary-color;
                font-size: 2.5rem;
                margin-bottom: $spacing-sm;
            }

            .lead {
                color: $text-secondary;
                font-size: 1.2rem;
            }
        }

        .quick-stats {
            margin-bottom: $spacing-xl;

            .stat-card {
                background-color: $bg-primary;
                border-radius: $border-radius-lg;
                padding: $spacing-md;
                box-shadow: $shadow-sm;
                display: flex;
                align-items: center;
                gap: $spacing-md;
                transition: transform 0.3s ease, box-shadow 0.3s ease;

                &:hover {
                    transform: translateY(-2px);
                    box-shadow: $shadow-md;
                }

                .stat-icon {
                    width: 60px;
                    height: 60px;
                    border-radius: $border-radius-full;
                    background-color: rgba($primary-color, 0.1);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 24px;
                }

                .stat-info {
                    h3 {
                        font-size: 2rem;
                        font-weight: 700;
                        margin-bottom: 0;
                        color: $primary-color;
                    }

                    p {
                        margin-bottom: 0;
                        color: $text-secondary;
                        font-size: 14px;
                    }
                }
            }
        }

        .current-courses-section, .recommendations-section {
            margin-bottom: $spacing-xl;

            h2 {
                color: $primary-color;
                margin-bottom: $spacing-lg;
                font-size: 1.8rem;
            }
        }

        .courses-grid, .recommendations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: $spacing-md;
        }

        .course-card, .recommendation-card {
            background-color: $bg-primary;
            border-radius: $border-radius-lg;
            overflow: hidden;
            box-shadow: $shadow-sm;
            transition: transform 0.3s ease, box-shadow 0.3s ease;

            &:hover {
                transform: translateY(-4px);
                box-shadow: $shadow-md;
            }

            .course-image, .recommendation-image {
                position: relative;
                height: 200px;
                overflow: hidden;

                img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }

                .course-progress-overlay {
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
                    padding: $spacing-sm;
                    display: flex;
                    align-items: center;
                    gap: $spacing-sm;

                    .progress {
                        flex: 1;
                        height: 6px;
                        background-color: rgba(255,255,255,0.3);
                        border-radius: $border-radius-sm;

                        .progress-bar {
                            height: 100%;
                            background-color: $success-color;
                            border-radius: $border-radius-sm;
                            transition: width 0.3s ease;
                        }
                    }

                    .progress-text {
                        color: $text-white;
                        font-size: 12px;
                        font-weight: 600;
                    }
                }
            }

            .course-info, .recommendation-info {
                padding: $spacing-md;

                h4 {
                    color: $primary-color;
                    margin-bottom: $spacing-sm;
                    font-size: 1.1rem;
                }

                p {
                    color: $text-secondary;
                    font-size: 14px;
                    margin-bottom: $spacing-sm;
                }

                .course-meta, .recommendation-meta {
                    display: flex;
                    gap: $spacing-sm;
                    margin-bottom: $spacing-md;

                    span {
                        padding: $spacing-xs $spacing-sm;
                        background-color: $bg-light;
                        border-radius: $border-radius-sm;
                        font-size: 12px;
                        color: $text-secondary;
                    }
                }
            }
        }

        .recent-activity-section {
            h2 {
                color: $primary-color;
                margin-bottom: $spacing-lg;
                font-size: 1.8rem;
            }

            .activity-timeline {
                .activity-item {
                    display: flex;
                    align-items: flex-start;
                    gap: $spacing-md;
                    padding: $spacing-md;
                    margin-bottom: $spacing-sm;
                    background-color: $bg-primary;
                    border-radius: $border-radius-lg;
                    box-shadow: $shadow-sm;

                    .activity-icon {
                        width: 40px;
                        height: 40px;
                        border-radius: $border-radius-full;
                        background-color: $primary-color;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: $text-white;
                        font-size: 16px;
                        flex-shrink: 0;
                    }

                    .activity-content {
                        flex: 1;

                        h5 {
                            color: $primary-color;
                            margin-bottom: $spacing-xs;
                            font-size: 1rem;
                        }

                        p {
                            color: $text-secondary;
                            margin-bottom: $spacing-xs;
                            font-size: 14px;
                        }

                        small {
                            color: $text-muted;
                            font-size: 12px;
                        }
                    }
                }
            }
        }
    }
}

// Footer
.medical-footer {
    background-color: $bg-dark;
    color: $text-white;
    padding: $spacing-md 0;
    margin-top: $spacing-xxl;

    p {
        margin-bottom: 0;
    }
}

// Responsive design
@media (max-width: $breakpoint-md) {
    .medical-header {
        .medical-search {
            margin-top: $spacing-sm;
        }
    }

    .medical-dashboard-content {
        .courses-grid, .recommendations-grid {
            grid-template-columns: 1fr;
        }

        .quick-stats {
            .stat-card {
                flex-direction: column;
                text-align: center;
            }
        }
    }
}
```

### 6. Funciones PHP del Tema

**lib.php**
```php
<?php
defined('MOODLE_INTERNAL') || die();

function theme_medico_theme_get_main_scss_content($theme) {
    global $CFG;
    
    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();
    
    $context = context_system::instance();
    $scss .= file_get_contents($CFG->dirroot . '/theme/medico_theme/scss/main.scss');
    
    return $scss;
}

function theme_medico_theme_get_pre_scss($theme) {
    $scss = '';
    $configurable = [
        'brandcolor' => 'primary-color',
        'brandsecondary' => 'secondary-color',
        'brandaccent' => 'accent-color',
    ];
    
    foreach ($configurable as $settingname => $scssvar) {
        $value = isset($theme->settings->{$settingname}) ? $theme->settings->{$settingname} : null;
        if (!empty($value)) {
            $scss .= '$' . $scssvar . ': ' . $value . ";\n";
        }
    }
    
    return $scss;
}

function theme_medico_theme_get_extra_scss($theme) {
    return !empty($theme->settings->scss) ? $theme->settings->scss : '';
}

function get_user_specialties($userid) {
    global $DB;
    
    $sql = "SELECT ms.* FROM {medical_specialties} ms
            JOIN {medical_user_specialties} mus ON ms.id = mus.specialty_id
            WHERE mus.user_id = ?";
    
    return $DB->get_records_sql($sql, [$userid]);
}

function get_user_learning_progress($userid) {
    global $DB;
    
    $sql = "SELECT mlp.*, mp.progress_percentage, mp.hours_completed
            FROM {medical_learning_paths} mlp
            JOIN {medical_user_progress} mp ON mlp.id = mp.learning_path_id
            WHERE mp.user_id = ?";
    
    return $DB->get_records_sql($sql, [$userid]);
}

function get_upcoming_certifications($userid) {
    global $DB;
    
    $sql = "SELECT mc.*, ms.name as specialty_name
            FROM {medical_certifications} mc
            JOIN {medical_specialties} ms ON mc.specialty_id = ms.id
            WHERE mc.user_id = ? AND mc.expiry_date > ? AND mc.is_active = 1
            ORDER BY mc.expiry_date ASC
            LIMIT 5";
    
    return $DB->get_records_sql($sql, [$userid, time()]);
}

function get_user_current_courses($userid) {
    global $DB;
    
    $sql = "SELECT c.*, cc.timeaccess, 
                   (SELECT COUNT(*) FROM {course_modules} cm WHERE cm.course = c.id) as total_modules,
                   (SELECT COUNT(*) FROM {course_modules_completion} cmc 
                    JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id 
                    WHERE cm.course = c.id AND cmc.userid = ? AND cmc.completionstate > 0) as completed_modules
            FROM {course} c
            JOIN {course_completions} cc ON c.id = cc.course
            WHERE cc.userid = ? AND cc.timecompleted IS NULL
            ORDER BY cc.timeaccess DESC
            LIMIT 6";
    
    $courses = $DB->get_records_sql($sql, [$userid, $userid]);
    
    foreach ($courses as $course) {
        $course->progress_percentage = $course->total_modules > 0 ? 
            round(($course->completed_modules / $course->total_modules) * 100) : 0;
        $course->view_url = new moodle_url('/course/view.php', ['id' => $course->id]);
    }
    
    return $courses;
}

function get_recommended_courses($userid) {
    global $DB;
    
    // Obtener especialidades del usuario
    $user_specialties = get_user_specialties($userid);
    $specialty_ids = array_keys($user_specialties);
    
    if (empty($specialty_ids)) {
        return [];
    }
    
    $sql = "SELECT c.*, ms.name as specialty_name
            FROM {course} c
            JOIN {medical_course_specialties} mcs ON c.id = mcs.course_id
            JOIN {medical_specialties} ms ON mcs.specialty_id = ms.id
            WHERE mcs.specialty_id IN (" . implode(',', $specialty_ids) . ")
            AND c.id NOT IN (
                SELECT course FROM {course_completions} 
                WHERE userid = ? AND (timecompleted IS NOT NULL OR timeaccess IS NOT NULL)
            )
            ORDER BY c.sortorder
            LIMIT 6";
    
    return $DB->get_records_sql($sql, [$userid]);
}
```

---

## 🎨 Personalización Avanzada

### 1. Configuración del Tema
- Variables SCSS personalizables
- Colores por especialidad médica
- Tipografía profesional
- Responsive design

### 2. Componentes Médicos
- Cards de progreso circular
- Timeline de actividades
- Badges de especialidades
- Indicadores de certificación

### 3. Navegación Intuitiva
- Menú principal médico
- Breadcrumbs contextuales
- Búsqueda avanzada
- Filtros por especialidad

---

**Próximo paso**: Desarrollo de plugins personalizados