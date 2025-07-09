<?php
/**
 * Script maestro para configurar completamente el Curso de Telemedicina
 * Ejecuta todo el proceso de configuración en secuencia
 */

define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');

echo "🚀 Configuración Completa del Curso Básico de Nivelación en Telemedicina\n";
echo "=" . str_repeat("=", 80) . "\n\n";

$start_time = time();

// Paso 1: Crear estructura del curso
echo "📚 PASO 1: Creando estructura del curso...\n";
echo "-" . str_repeat("-", 50) . "\n";
include 'create_telemedicina_course_structure.php';
echo "\n";

// Paso 2: Agregar contenido detallado
echo "📝 PASO 2: Agregando contenido detallado...\n";
echo "-" . str_repeat("-", 50) . "\n";
include 'add_detailed_content_telemedicina.php';
echo "\n";

// Paso 3: Inscribir usuarios
echo "👥 PASO 3: Inscribiendo usuarios...\n";
echo "-" . str_repeat("-", 50) . "\n";
include 'enroll_users_telemedicina.php';
echo "\n";

// Paso 4: Configurar acceso progresivo avanzado
echo "🔐 PASO 4: Configurando acceso progresivo avanzado...\n";
echo "-" . str_repeat("-", 50) . "\n";
setup_advanced_progressive_access();
echo "\n";

// Paso 5: Crear insignias y certificados
echo "🏆 PASO 5: Creando insignias y certificados...\n";
echo "-" . str_repeat("-", 50) . "\n";
setup_badges_and_certificates();
echo "\n";

// Paso 6: Configurar reportes y analíticas
echo "📊 PASO 6: Configurando reportes y analíticas...\n";
echo "-" . str_repeat("-", 50) . "\n";
setup_course_analytics();
echo "\n";

$end_time = time();
$duration = $end_time - $start_time;

echo "🎉 CONFIGURACIÓN COMPLETADA EXITOSAMENTE\n";
echo "=" . str_repeat("=", 80) . "\n";
echo "⏱️  Tiempo total: " . gmdate("H:i:s", $duration) . "\n";

// Buscar el curso para mostrar información final
$course = $DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
if ($course) {
    echo "🔗 Acceso al curso: http://34.72.133.6/course/view.php?id={$course->id}\n";
    
    // Mostrar estadísticas finales
    show_course_statistics($course->id);
}

echo "\n📋 COMANDOS PARA USAR CON GCLOUD:\n";
echo "-" . str_repeat("-", 50) . "\n";
show_gcloud_commands();

echo "\n✅ ¡El curso está listo para usar!\n";

/**
 * Configurar acceso progresivo avanzado
 */
function setup_advanced_progressive_access() {
    global $DB;
    
    $course = $DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
    if (!$course) {
        echo "❌ Curso no encontrado\n";
        return;
    }
    
    // Obtener todas las actividades del curso
    $activities = $DB->get_records_sql("
        SELECT cm.*, cs.section, m.name as modulename, cm.name as activity_name
        FROM {course_modules} cm
        JOIN {course_sections} cs ON cm.section = cs.id
        JOIN {modules} m ON cm.module = m.id
        WHERE cm.course = ?
        ORDER BY cs.section, cm.id
    ", [$course->id]);
    
    $previous_activity = null;
    $section_activities = [];
    
    foreach ($activities as $activity) {
        $section_activities[$activity->section][] = $activity;
    }
    
    // Configurar restricciones por sección
    foreach ($section_activities as $section => $activities) {
        if ($section == 0) continue; // Saltar sección general
        
        echo "   🔒 Configurando acceso progresivo para sección $section\n";
        
        for ($i = 0; $i < count($activities); $i++) {
            $current_activity = $activities[$i];
            
            if ($i > 0) {
                // Dentro de la sección, cada actividad depende de la anterior
                $previous_activity = $activities[$i - 1];
                set_activity_restriction($current_activity->id, $previous_activity->id, 'completion');
                echo "     → {$current_activity->activity_name} depende de {$previous_activity->activity_name}\n";
            } elseif ($section > 1) {
                // Primera actividad de la sección depende de la última de la sección anterior
                $previous_section = $section - 1;
                if (isset($section_activities[$previous_section])) {
                    $last_activity_previous_section = end($section_activities[$previous_section]);
                    set_activity_restriction($current_activity->id, $last_activity_previous_section->id, 'completion');
                    echo "     → {$current_activity->activity_name} depende de completar sección anterior\n";
                }
            }
        }
    }
    
    echo "✅ Acceso progresivo configurado para " . count($activities) . " actividades\n";
}

/**
 * Configurar insignias y certificados
 */
function setup_badges_and_certificates() {
    global $DB;
    
    $course = $DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
    if (!$course) {
        echo "❌ Curso no encontrado\n";
        return;
    }
    
    // Crear insignias por módulo
    $badges = [
        [
            'name' => 'Experto en Habilidades Tecnológicas',
            'description' => 'Completó exitosamente el Módulo A - Habilidades Tecnológicas',
            'section' => 1
        ],
        [
            'name' => 'Especialista en Buenas Prácticas',
            'description' => 'Completó exitosamente el Módulo B - Buenas Prácticas Digitales',
            'section' => 2
        ],
        [
            'name' => 'Usuario Avanzado Dr. ISSS',
            'description' => 'Completó exitosamente el Módulo C - Aplicaciones Médicas',
            'section' => 3
        ],
        [
            'name' => 'Certificado en Telemedicina',
            'description' => 'Completó exitosamente todo el Curso Básico de Nivelación',
            'section' => 4
        ]
    ];
    
    foreach ($badges as $badge_data) {
        $badge = new stdClass();
        $badge->name = $badge_data['name'];
        $badge->description = $badge_data['description'];
        $badge->courseid = $course->id;
        $badge->status = 1; // Activo
        $badge->type = 2; // Insignia de curso
        $badge->timecreated = time();
        $badge->timemodified = time();
        
        $badge_id = $DB->insert_record('badge', $badge);
        echo "✅ Insignia creada: {$badge_data['name']}\n";
    }
    
    echo "🏆 Sistema de insignias configurado\n";
}

/**
 * Configurar reportes y analíticas
 */
function setup_course_analytics() {
    global $DB;
    
    $course = $DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
    if (!$course) {
        echo "❌ Curso no encontrado\n";
        return;
    }
    
    // Habilitar seguimiento de finalización
    $DB->update_record('course', [
        'id' => $course->id,
        'enablecompletion' => 1,
        'completionnotify' => 1
    ]);
    
    // Configurar criterios de finalización del curso
    $completion_criteria = [
        'course' => $course->id,
        'criteriatype' => 6, // Completar todas las actividades
        'timecreated' => time()
    ];
    
    $DB->insert_record('course_completion_criteria', $completion_criteria);
    
    echo "✅ Analíticas y seguimiento configurados\n";
    echo "📊 Reportes disponibles en: http://34.72.133.6/report/index.php?id={$course->id}\n";
}

/**
 * Establecer restricción de actividad
 */
function set_activity_restriction($activity_id, $depends_on_id, $type = 'completion') {
    global $DB;
    
    $availability = json_encode([
        'op' => '&',
        'c' => [
            [
                'type' => $type,
                'cm' => $depends_on_id,
                'e' => 1
            ]
        ]
    ]);
    
    $DB->update_record('course_modules', [
        'id' => $activity_id,
        'availability' => $availability
    ]);
}

/**
 * Mostrar estadísticas del curso
 */
function show_course_statistics($course_id) {
    global $DB;
    
    // Contar actividades por tipo
    $activities = $DB->get_records_sql("
        SELECT m.name as modulename, COUNT(*) as count
        FROM {course_modules} cm
        JOIN {modules} m ON cm.module = m.id
        WHERE cm.course = ?
        GROUP BY m.name
    ", [$course_id]);
    
    // Contar usuarios inscritos por rol
    $enrollments = $DB->get_records_sql("
        SELECT r.shortname, COUNT(*) as count
        FROM {user_enrolments} ue
        JOIN {enrol} e ON ue.enrolid = e.id
        JOIN {role_assignments} ra ON ue.userid = ra.userid
        JOIN {role} r ON ra.roleid = r.id
        WHERE e.courseid = ?
        GROUP BY r.shortname
    ", [$course_id]);
    
    echo "\n📊 ESTADÍSTICAS DEL CURSO:\n";
    echo "-" . str_repeat("-", 30) . "\n";
    
    echo "📚 Actividades por tipo:\n";
    foreach ($activities as $activity) {
        echo "   • {$activity->modulename}: {$activity->count}\n";
    }
    
    echo "\n👥 Usuarios por rol:\n";
    foreach ($enrollments as $enrollment) {
        echo "   • {$enrollment->shortname}: {$enrollment->count}\n";
    }
    
    $total_activities = array_sum(array_column($activities, 'count'));
    $total_users = array_sum(array_column($enrollments, 'count'));
    
    echo "\n📈 Totales:\n";
    echo "   • Total actividades: $total_activities\n";
    echo "   • Total usuarios: $total_users\n";
}

/**
 * Mostrar comandos para usar con gcloud
 */
function show_gcloud_commands() {
    echo "# Conectar al cluster GKE\n";
    echo "gcloud container clusters get-credentials moodle-cluster --zone=us-central1-c --project=moodle-gcp-test\n\n";
    
    echo "# Acceder al pod de Moodle\n";
    echo "kubectl exec -it moodle-684db8486b-5c5zp -- /bin/bash\n\n";
    
    echo "# Navegar al directorio de Moodle\n";
    echo "cd /bitnami/moodle/\n\n";
    
    echo "# Ejecutar configuración completa (este script)\n";
    echo "php setup_complete_telemedicina_course.php\n\n";
    
    echo "# Ejecutar scripts individuales:\n";
    echo "php create_telemedicina_course_structure.php\n";
    echo "php add_detailed_content_telemedicina.php\n";
    echo "php enroll_users_telemedicina.php\n\n";
    
    echo "# Ver logs del curso\n";
    echo "kubectl logs moodle-684db8486b-5c5zp | grep -i telemedicina\n\n";
    
    echo "# Verificar estado del pod\n";
    echo "kubectl get pods -l app=moodle\n\n";
    
    echo "# Acceder a la base de datos\n";
    echo "kubectl exec -it moodle-mariadb-0 -- mysql -u root -p moodle\n\n";
    
    echo "# Hacer backup de la base de datos\n";
    echo "kubectl exec -it moodle-mariadb-0 -- mysqldump -u root -p moodle > backup_telemedicina.sql\n\n";
    
    echo "# Verificar usuarios OAuth\n";
    echo "kubectl exec -it moodle-684db8486b-5c5zp -- php -r \"require_once('/bitnami/moodle/config.php'); echo 'Usuarios OAuth: ' . \$DB->count_records('user', ['auth' => 'oauth2']) . \\\"\\n\\\";\"\n\n";
    
    echo "# Monitorear recursos\n";
    echo "kubectl top pods moodle-684db8486b-5c5zp\n";
    echo "kubectl top pods moodle-mariadb-0\n";
}

?>