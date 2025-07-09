<?php
/**
 * Inscribir usuarios al Curso Básico de Nivelación en Telemedicina
 * Con roles específicos según su función
 */

define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/enrollib.php');

echo "👥 Inscribiendo usuarios al Curso de Telemedicina...\n";

// Buscar el curso
$course = $DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
if (!$course) {
    echo "❌ Error: Curso no encontrado. Ejecuta primero create_telemedicina_course_structure.php\n";
    exit(1);
}

// Obtener el contexto del curso
$context = context_course::instance($course->id);

// Obtener plugin de inscripción manual
$enroll_plugin = enrol_get_plugin('manual');
$enroll_instance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'manual']);

if (!$enroll_instance) {
    // Crear instancia de inscripción manual
    $enroll_instance = new stdClass();
    $enroll_instance->courseid = $course->id;
    $enroll_instance->enrol = 'manual';
    $enroll_instance->status = 0; // Habilitado
    $enroll_instance->name = 'Inscripción manual';
    $enroll_instance->timecreated = time();
    $enroll_instance->timemodified = time();
    
    $enroll_instance->id = $DB->insert_record('enrol', $enroll_instance);
    echo "✅ Instancia de inscripción manual creada\n";
}

// Obtener roles
$roles = [
    'editingteacher' => $DB->get_record('role', ['shortname' => 'editingteacher']),
    'teacher' => $DB->get_record('role', ['shortname' => 'teacher']),
    'student' => $DB->get_record('role', ['shortname' => 'student']),
    'manager' => $DB->get_record('role', ['shortname' => 'manager'])
];

// Usuarios para inscribir con sus roles específicos
$users_to_enroll = [
    // Usuarios @telesalud.gob.sv
    [
        'email' => 'director@telesalud.gob.sv',
        'role' => 'manager',
        'description' => 'Director de Telesalud - Acceso completo'
    ],
    [
        'email' => 'cardiologo@telesalud.gob.sv',
        'role' => 'editingteacher',
        'description' => 'Cardiólogo - Instructor principal'
    ],
    [
        'email' => 'enfermera.jefe@telesalud.gob.sv',
        'role' => 'teacher',
        'description' => 'Jefe de Enfermería - Instructor asistente'
    ],
    [
        'email' => 'tecnico.radiologia@telesalud.gob.sv',
        'role' => 'student',
        'description' => 'Técnico en Radiología - Estudiante'
    ],
    
    // Usuarios @goes.gob.sv
    [
        'email' => 'coordinador@goes.gob.sv',
        'role' => 'manager',
        'description' => 'Coordinador GOES - Acceso completo'
    ],
    [
        'email' => 'instructor@goes.gob.sv',
        'role' => 'editingteacher',
        'description' => 'Instructor GOES - Instructor principal'
    ],
    [
        'email' => 'paramedico@goes.gob.sv',
        'role' => 'teacher',
        'description' => 'Paramédico - Instructor asistente'
    ],
    [
        'email' => 'voluntario@goes.gob.sv',
        'role' => 'student',
        'description' => 'Voluntario - Estudiante'
    ]
];

$enrolled_count = 0;
$already_enrolled = 0;

foreach ($users_to_enroll as $user_data) {
    // Buscar usuario por email
    $user = $DB->get_record('user', ['email' => $user_data['email']]);
    
    if (!$user) {
        echo "⚠️  Usuario no encontrado: {$user_data['email']}\n";
        continue;
    }
    
    // Verificar si ya está inscrito
    $existing_enrollment = $DB->get_record('user_enrolments', [
        'userid' => $user->id,
        'enrolid' => $enroll_instance->id
    ]);
    
    if ($existing_enrollment) {
        echo "ℹ️  Usuario ya inscrito: {$user_data['email']}\n";
        $already_enrolled++;
        
        // Verificar y actualizar rol si es necesario
        $role_assignment = $DB->get_record('role_assignments', [
            'userid' => $user->id,
            'contextid' => $context->id
        ]);
        
        $target_role = $roles[$user_data['role']];
        if ($role_assignment && $role_assignment->roleid != $target_role->id) {
            // Actualizar rol
            $DB->update_record('role_assignments', [
                'id' => $role_assignment->id,
                'roleid' => $target_role->id,
                'timemodified' => time()
            ]);
            echo "   → Rol actualizado a: {$user_data['role']}\n";
        }
        
        continue;
    }
    
    // Inscribir usuario
    $enroll_plugin->enrol_user($enroll_instance, $user->id, $roles[$user_data['role']]->id);
    
    $enrolled_count++;
    echo "✅ Usuario inscrito: {$user_data['email']} como {$user_data['role']}\n";
    echo "   → {$user_data['description']}\n";
}

echo "\n📊 Resumen de inscripciones:\n";
echo "   • Usuarios inscritos: $enrolled_count\n";
echo "   • Ya inscritos: $already_enrolled\n";
echo "   • Total procesados: " . ($enrolled_count + $already_enrolled) . "\n";

// Crear grupos por institución
echo "\n👥 Creando grupos por institución...\n";
create_institution_groups($course->id);

// Configurar notificaciones
echo "\n🔔 Configurando notificaciones...\n";
configure_course_notifications($course->id);

echo "\n🎯 Proceso de inscripción completado exitosamente\n";
echo "📋 Roles asignados:\n";
echo "   • Managers: 2 (Director Telesalud, Coordinador GOES)\n";
echo "   • Editingteachers: 2 (Cardiólogo, Instructor GOES)\n";
echo "   • Teachers: 2 (Enfermera Jefe, Paramédico)\n";
echo "   • Students: 2 (Técnico Radiología, Voluntario)\n";

echo "\n🔗 Acceso al curso: http://34.72.133.6/course/view.php?id={$course->id}\n";

/**
 * Crear grupos por institución
 */
function create_institution_groups($course_id) {
    global $DB;
    
    $groups_data = [
        'telesalud' => [
            'name' => 'Grupo Telesalud',
            'description' => 'Personal del Ministerio de Salud - Dirección de Telesalud',
            'domain' => '@telesalud.gob.sv'
        ],
        'goes' => [
            'name' => 'Grupo GOES',
            'description' => 'Personal de GOES - Gerencia de Operaciones de Emergencia',
            'domain' => '@goes.gob.sv'
        ]
    ];
    
    foreach ($groups_data as $key => $group_data) {
        // Crear grupo si no existe
        $existing_group = $DB->get_record('groups', [
            'courseid' => $course_id,
            'name' => $group_data['name']
        ]);
        
        if (!$existing_group) {
            $group = new stdClass();
            $group->courseid = $course_id;
            $group->name = $group_data['name'];
            $group->description = $group_data['description'];
            $group->descriptionformat = 1;
            $group->timecreated = time();
            $group->timemodified = time();
            
            $group->id = $DB->insert_record('groups', $group);
            echo "✅ Grupo creado: {$group_data['name']}\n";
        } else {
            $group = $existing_group;
            echo "ℹ️  Grupo ya existe: {$group_data['name']}\n";
        }
        
        // Agregar usuarios al grupo según dominio
        $users = $DB->get_records_sql("
            SELECT u.id, u.email 
            FROM {user} u 
            JOIN {user_enrolments} ue ON u.id = ue.userid 
            JOIN {enrol} e ON ue.enrolid = e.id 
            WHERE e.courseid = ? AND u.email LIKE ?
        ", [$course_id, '%' . $group_data['domain']]);
        
        foreach ($users as $user) {
            // Verificar si ya está en el grupo
            $existing_membership = $DB->get_record('groups_members', [
                'groupid' => $group->id,
                'userid' => $user->id
            ]);
            
            if (!$existing_membership) {
                $member = new stdClass();
                $member->groupid = $group->id;
                $member->userid = $user->id;
                $member->timeadded = time();
                
                $DB->insert_record('groups_members', $member);
                echo "   → Usuario {$user->email} agregado al grupo\n";
            }
        }
    }
}

/**
 * Configurar notificaciones del curso
 */
function configure_course_notifications($course_id) {
    global $DB;
    
    // Configurar notificaciones por defecto
    $notification_settings = [
        'completion' => 1,
        'forum' => 1,
        'assignment' => 1,
        'quiz' => 1
    ];
    
    // Obtener usuarios inscritos
    $enrolled_users = $DB->get_records_sql("
        SELECT u.id 
        FROM {user} u 
        JOIN {user_enrolments} ue ON u.id = ue.userid 
        JOIN {enrol} e ON ue.enrolid = e.id 
        WHERE e.courseid = ?
    ", [$course_id]);
    
    foreach ($enrolled_users as $user) {
        foreach ($notification_settings as $component => $enabled) {
            $preference_name = "message_provider_mod_{$component}_notification_loggedin";
            
            // Configurar preferencia si no existe
            $existing_preference = $DB->get_record('user_preferences', [
                'userid' => $user->id,
                'name' => $preference_name
            ]);
            
            if (!$existing_preference) {
                $preference = new stdClass();
                $preference->userid = $user->id;
                $preference->name = $preference_name;
                $preference->value = $enabled ? 'email' : 'none';
                
                $DB->insert_record('user_preferences', $preference);
            }
        }
    }
    
    echo "✅ Notificaciones configuradas para " . count($enrolled_users) . " usuarios\n";
}

?>