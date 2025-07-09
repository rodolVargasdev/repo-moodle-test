<?php
/**
 * Crear estructura del Curso Básico de Nivelación en Telemedicina
 * Adaptado para replicar la estructura de Google Classroom
 */

define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');

echo "🏥 Creando Curso Básico de Nivelación en Telemedicina...\n";

// Buscar o crear la categoría
$category = $DB->get_record('course_categories', ['name' => 'Nivelación Médica']);
if (!$category) {
    $category = new stdClass();
    $category->name = 'Nivelación Médica';
    $category->description = 'Cursos básicos para personal médico';
    $category->parent = 0;
    $category->sortorder = 1;
    $category->timemodified = time();
    $category->id = $DB->insert_record('course_categories', $category);
    echo "✅ Categoría 'Nivelación Médica' creada\n";
}

// Buscar o crear el curso
$course = $DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
if (!$course) {
    $course = new stdClass();
    $course->category = $category->id;
    $course->shortname = 'CBN-TELEMEDICINA-2025';
    $course->fullname = 'Curso Básico de Nivelación en Telemedicina';
    $course->summary = 'Curso diseñado para fortalecer conocimientos en telemedicina y herramientas digitales para personal médico';
    $course->summaryformat = 1;
    $course->format = 'topics';
    $course->numsections = 4;
    $course->startdate = time();
    $course->enddate = time() + (90 * 24 * 60 * 60); // 90 días
    $course->visible = 1;
    $course->enablecompletion = 1;
    $course->completionnotify = 1;
    $course->timecreated = time();
    $course->timemodified = time();
    
    $course->id = $DB->insert_record('course', $course);
    echo "✅ Curso 'CBN-TELEMEDICINA-2025' creado\n";
} else {
    echo "✅ Curso 'CBN-TELEMEDICINA-2025' ya existe\n";
}

// Estructura del curso basada en Google Classroom
$modules = [
    1 => [
        'name' => 'Módulo A - Habilidades Tecnológicas',
        'description' => 'Introducción a Chrome OS y herramientas digitales para telemedicina',
        'activities' => [
            [
                'type' => 'announce',
                'name' => 'Bienvenidos al Curso Básico de Nivelación en Telemedicina',
                'content' => 'Nos complace que se una a esta experiencia de aprendizaje, el cual permitirá dar los primeros pasos para fortalecer los conocimientos en esta modalidad de atención médica a distancia...'
            ],
            [
                'type' => 'announce',
                'name' => 'Normas de Uso del Aula Virtual',
                'content' => 'Respeto y cortesía. Mantener un tono respetuoso y cordial en todas las interacciones...'
            ],
            [
                'type' => 'resource',
                'name' => 'UNIDAD 1. Introducción a Chrome OS y su uso en Telemedicina',
                'description' => 'Material de estudio sobre Chrome OS'
            ],
            [
                'type' => 'assign',
                'name' => '1.1 ACTIVIDAD EVALUADA 1: Formulario de Configuraciones de Chromebook',
                'instructions' => 'Complete el formulario sobre configuraciones básicas de Chromebook'
            ],
            [
                'type' => 'assign',
                'name' => '1.2 ACTIVIDAD EVALUADA 2: Formulario de Teclas especiales en Chromebook',
                'instructions' => 'Complete el formulario sobre teclas especiales en Chromebook'
            ],
            [
                'type' => 'assign',
                'name' => '1.3 ACTIVIDAD EVALUADA 3: Envío de mensajes por medio de Google Chat',
                'instructions' => 'Practique el envío de mensajes usando Google Chat'
            ],
            [
                'type' => 'assign',
                'name' => '1.4 ACTIVIDAD EVALUADA 4: Realización de videollamada en Google Meet',
                'instructions' => 'Realice una videollamada de prueba usando Google Meet'
            ],
            [
                'type' => 'assign',
                'name' => '1.5 ACTIVIDAD EVALUADA 5: Organización de carpetas en Google Drive',
                'instructions' => 'Organice carpetas en Google Drive según las indicaciones'
            ]
        ]
    ],
    2 => [
        'name' => 'Módulo B - Buenas Prácticas Digitales',
        'description' => 'Buenas prácticas digitales en teleconsulta y telemedicina',
        'activities' => [
            [
                'type' => 'resource',
                'name' => 'UNIDAD 2. Buenas Prácticas Digitales en la Teleconsulta',
                'description' => 'Material sobre mejores prácticas en teleconsulta'
            ],
            [
                'type' => 'assign',
                'name' => '2.1 ACTIVIDAD EVALUADA 1: Resumen de Portal de Práctica Médica (DEMO)',
                'instructions' => 'Elabore un resumen del portal de práctica médica'
            ],
            [
                'type' => 'assign',
                'name' => '2.2 ACTIVIDAD EVALUADA 2: Caso real de inconvenientes en Telemedicina',
                'instructions' => 'Analice un caso real de inconvenientes en telemedicina'
            ]
        ]
    ],
    3 => [
        'name' => 'Módulo C - Aplicaciones Médicas',
        'description' => 'Usabilidad de aplicaciones médicas especializadas',
        'activities' => [
            [
                'type' => 'resource',
                'name' => 'UNIDAD 3. Usabilidad de la aplicación DR. ISSS en línea',
                'description' => 'Manual de uso de la aplicación DR. ISSS'
            ],
            [
                'type' => 'assign',
                'name' => '3.1 ACTIVIDAD EVALUADA 1: Descarga de la Aplicación Dr. ISSS En línea',
                'instructions' => 'Descargue e instale la aplicación Dr. ISSS en línea'
            ],
            [
                'type' => 'assign',
                'name' => '3.2 ACTIVIDAD EVALUADA 2: Navegación en la aplicación Dr. ISSS',
                'instructions' => 'Explore las funcionalidades de la aplicación Dr. ISSS'
            ]
        ]
    ],
    4 => [
        'name' => 'Módulo D - Evaluación Final',
        'description' => 'Evaluación integral de conocimientos adquiridos',
        'activities' => [
            [
                'type' => 'forum',
                'name' => 'FORO PARA CONSULTAS',
                'description' => 'Espacio para resolver dudas y consultas del curso'
            ],
            [
                'type' => 'quiz',
                'name' => 'Evaluación Final del Curso',
                'description' => 'Evaluación integral de todos los módulos'
            ]
        ]
    ]
];

// Crear secciones y actividades
foreach ($modules as $section_num => $module) {
    echo "\n📖 Creando {$module['name']}...\n";
    
    // Actualizar nombre de sección
    $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => $section_num]);
    if ($section) {
        $section->name = $module['name'];
        $section->summary = $module['description'];
        $section->summaryformat = 1;
        $DB->update_record('course_sections', $section);
    }
    
    // Crear actividades
    foreach ($module['activities'] as $activity_data) {
        $activity = create_activity($course->id, $section_num, $activity_data);
        if ($activity) {
            echo "   ✅ {$activity_data['name']} creada\n";
        }
    }
}

echo "\n🔒 Configurando restricciones de acceso progresivo...\n";
setup_progressive_access($course->id);

echo "\n🎯 Curso Básico de Nivelación en Telemedicina creado exitosamente\n";
echo "📋 Estructura del curso:\n";
echo "   • Módulo A: Habilidades Tecnológicas (5 actividades)\n";
echo "   • Módulo B: Buenas Prácticas Digitales (2 actividades)\n";
echo "   • Módulo C: Aplicaciones Médicas (2 actividades)\n";
echo "   • Módulo D: Evaluación Final (2 actividades)\n";
echo "\n🔗 Acceso al curso: http://34.72.133.6/course/view.php?id={$course->id}\n";

/**
 * Crear una actividad basada en el tipo especificado
 */
function create_activity($course_id, $section, $activity_data) {
    global $DB;
    
    $module = new stdClass();
    $module->course = $course_id;
    $module->section = $section;
    $module->name = $activity_data['name'];
    $module->intro = $activity_data['content'] ?? $activity_data['description'] ?? $activity_data['instructions'] ?? '';
    $module->introformat = 1;
    $module->timecreated = time();
    $module->timemodified = time();
    $module->visible = 1;
    $module->completion = 1;
    $module->completionview = 1;
    
    switch ($activity_data['type']) {
        case 'announce':
            $module->modulename = 'label';
            $module->content = $activity_data['content'];
            break;
            
        case 'resource':
            $module->modulename = 'resource';
            $module->display = 0;
            break;
            
        case 'assign':
            $module->modulename = 'assign';
            $module->duedate = time() + (7 * 24 * 60 * 60); // 7 días
            $module->allowsubmissionsfromdate = time();
            $module->grade = 100;
            $module->completionsubmit = 1;
            break;
            
        case 'forum':
            $module->modulename = 'forum';
            $module->type = 'general';
            break;
            
        case 'quiz':
            $module->modulename = 'quiz';
            $module->timeopen = time();
            $module->timeclose = time() + (30 * 24 * 60 * 60); // 30 días
            $module->timelimit = 3600; // 1 hora
            $module->attempts = 2;
            $module->grade = 100;
            break;
    }
    
    // Obtener el ID del módulo
    $module_info = $DB->get_record('modules', ['name' => $module->modulename]);
    if (!$module_info) {
        return false;
    }
    
    $module->module = $module_info->id;
    
    // Insertar en course_modules
    $cm_id = $DB->insert_record('course_modules', $module);
    
    // Insertar en la tabla específica del módulo
    $instance = new stdClass();
    $instance->course = $course_id;
    $instance->name = $module->name;
    $instance->intro = $module->intro;
    $instance->introformat = $module->introformat;
    $instance->timecreated = $module->timecreated;
    $instance->timemodified = $module->timemodified;
    
    // Propiedades específicas por tipo de actividad
    switch ($module->modulename) {
        case 'assign':
            $instance->duedate = $module->duedate;
            $instance->allowsubmissionsfromdate = $module->allowsubmissionsfromdate;
            $instance->grade = $module->grade;
            break;
            
        case 'quiz':
            $instance->timeopen = $module->timeopen;
            $instance->timeclose = $module->timeclose;
            $instance->timelimit = $module->timelimit;
            $instance->attempts = $module->attempts;
            $instance->grade = $module->grade;
            break;
            
        case 'forum':
            $instance->type = $module->type;
            break;
    }
    
    $instance_id = $DB->insert_record($module->modulename, $instance);
    
    // Actualizar course_modules con el instance ID
    $DB->update_record('course_modules', ['id' => $cm_id, 'instance' => $instance_id]);
    
    return $instance_id;
}

/**
 * Configurar acceso progresivo entre módulos
 */
function setup_progressive_access($course_id) {
    global $DB;
    
    // Obtener todas las actividades del curso ordenadas por sección
    $activities = $DB->get_records_sql("
        SELECT cm.*, cs.section, m.name as modulename
        FROM {course_modules} cm
        JOIN {course_sections} cs ON cm.section = cs.id
        JOIN {modules} m ON cm.module = m.id
        WHERE cm.course = ?
        ORDER BY cs.section, cm.id
    ", [$course_id]);
    
    $previous_activity = null;
    $current_section = 0;
    
    foreach ($activities as $activity) {
        // Si cambiamos de sección, la primera actividad de la nueva sección
        // depende de la última actividad de la sección anterior
        if ($activity->section != $current_section && $previous_activity) {
            set_activity_dependency($activity->id, $previous_activity->id);
        }
        // Dentro de la misma sección, cada actividad depende de la anterior
        elseif ($previous_activity && $activity->section == $current_section) {
            set_activity_dependency($activity->id, $previous_activity->id);
        }
        
        $previous_activity = $activity;
        $current_section = $activity->section;
    }
}

/**
 * Establecer dependencia entre actividades
 */
function set_activity_dependency($activity_id, $depends_on_id) {
    global $DB;
    
    // Crear restricción de acceso
    $availability = json_encode([
        'op' => '&',
        'c' => [
            [
                'type' => 'completion',
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

?>