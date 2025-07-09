<?php
/**
 * Crear y enrollar usuarios específicos al Curso de Telemedicina
 * - jose.vargas@telesalud.gob.sv
 * - rodolfovargasoff@gmail.com (administrador)
 */

define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/enrollib.php');

echo "👥 Creando usuarios específicos para el Curso de Telemedicina...\n";

// Usuarios específicos a crear
$specific_users = [
    [
        'username' => 'jose.vargas.telesalud',
        'email' => 'jose.vargas@telesalud.gob.sv',
        'firstname' => 'José',
        'lastname' => 'Vargas',
        'institution' => 'Ministerio de Salud',
        'department' => 'Dirección de Telesalud',
        'role' => 'editingteacher',
        'description' => 'José Vargas - Instructor principal Telesalud'
    ],
    [
        'username' => 'rodolfo.vargas.admin',
        'email' => 'rodolfovargasoff@gmail.com',
        'firstname' => 'Rodolfo',
        'lastname' => 'Vargas',
        'institution' => 'Administración del Sistema',
        'department' => 'Administración General',
        'role' => 'manager',
        'description' => 'Rodolfo Vargas - Administrador del sistema'
    ]
];

$created_users = [];
$updated_users = [];

// Primero, permitir el dominio gmail.com para el administrador
echo "🔧 Configurando dominios permitidos...\n";
$current_domains = get_config('core', 'allowemailaddresses');
$allowed_domains = 'telesalud.gob.sv,goes.gob.sv,gmail.com';

// Actualizar dominios permitidos globalmente
set_config('allowemailaddresses', $allowed_domains);
echo "✅ Dominios permitidos actualizados: $allowed_domains\n";

// Actualizar también en OAuth si existe
$oauth_issuer = $DB->get_record('oauth2_issuer', ['name' => 'Google Telesalud']);
if ($oauth_issuer) {
    $oauth_domains_config = $DB->get_record('oauth2_issuer_config', [
        'issuerid' => $oauth_issuer->id,
        'name' => 'alloweddomains'
    ]);
    
    if ($oauth_domains_config) {
        $oauth_domains_config->value = $allowed_domains;
        $DB->update_record('oauth2_issuer_config', $oauth_domains_config);
        echo "✅ Dominios OAuth actualizados\n";
    }
}

// Crear usuarios
foreach ($specific_users as $user_data) {
    echo "\n👤 Procesando usuario: {$user_data['email']}\n";
    
    // Verificar si el usuario ya existe
    $existing_user = $DB->get_record('user', ['email' => $user_data['email']]);
    
    if ($existing_user) {
        // Actualizar usuario existente
        $user = $existing_user;
        $user->firstname = $user_data['firstname'];
        $user->lastname = $user_data['lastname'];
        $user->institution = $user_data['institution'];
        $user->department = $user_data['department'];
        $user->timemodified = time();
        
        $DB->update_record('user', $user);
        $updated_users[] = $user_data['email'];
        echo "✅ Usuario actualizado: {$user_data['email']}\n";
    } else {
        // Crear nuevo usuario
        $user = new stdClass();
        $user->username = $user_data['username'];
        $user->email = $user_data['email'];
        $user->firstname = $user_data['firstname'];
        $user->lastname = $user_data['lastname'];
        $user->institution = $user_data['institution'];
        $user->department = $user_data['department'];
        $user->auth = 'oauth2';
        $user->confirmed = 1;
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->password = hash_internal_user_password(''); // Sin contraseña, solo OAuth
        $user->timecreated = time();
        $user->timemodified = time();
        
        $user->id = $DB->insert_record('user', $user);
        $created_users[] = $user_data['email'];
        echo "✅ Usuario creado: {$user_data['email']}\n";
    }
    
    // Asignar rol global
    $context = context_system::instance();
    $roleid = $DB->get_field('role', 'id', ['shortname' => $user_data['role']]);
    
    if ($roleid) {
        // Verificar si ya tiene el rol asignado
        $existing_assignment = $DB->get_record('role_assignments', [
            'userid' => $user->id,
            'roleid' => $roleid,
            'contextid' => $context->id
        ]);
        
        if (!$existing_assignment) {
            role_assign($roleid, $user->id, $context->id);
            echo "   → Rol global asignado: {$user_data['role']}\n";
        }
    }
    
    // Enrollar al curso de telemedicina
    $course = $DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
    if ($course) {
        echo "   🎓 Enrollando al curso de telemedicina...\n";
        
        // Obtener instancia de enrollment
        $enroll_plugin = enrol_get_plugin('manual');
        $enroll_instance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'manual']);
        
        if (!$enroll_instance) {
            // Crear instancia de inscripción manual si no existe
            $enroll_instance = new stdClass();
            $enroll_instance->courseid = $course->id;
            $enroll_instance->enrol = 'manual';
            $enroll_instance->status = 0;
            $enroll_instance->name = 'Inscripción manual';
            $enroll_instance->timecreated = time();
            $enroll_instance->timemodified = time();
            
            $enroll_instance->id = $DB->insert_record('enrol', $enroll_instance);
        }
        
        // Verificar si ya está enrollado
        $existing_enrollment = $DB->get_record('user_enrolments', [
            'userid' => $user->id,
            'enrolid' => $enroll_instance->id
        ]);
        
        if (!$existing_enrollment) {
            // Enrollar usuario
            $course_role = $DB->get_record('role', ['shortname' => $user_data['role']]);
            $enroll_plugin->enrol_user($enroll_instance, $user->id, $course_role->id);
            echo "   ✅ Usuario enrollado al curso como {$user_data['role']}\n";
        } else {
            echo "   ℹ️  Usuario ya estaba enrollado al curso\n";
        }
        
        // Agregar al grupo correspondiente
        $group_name = (strpos($user_data['email'], '@telesalud.gob.sv') !== false) ? 'Grupo Telesalud' : 'Grupo Administradores';
        
        // Crear grupo de administradores si no existe
        if ($group_name === 'Grupo Administradores') {
            $admin_group = $DB->get_record('groups', [
                'courseid' => $course->id,
                'name' => 'Grupo Administradores'
            ]);
            
            if (!$admin_group) {
                $group = new stdClass();
                $group->courseid = $course->id;
                $group->name = 'Grupo Administradores';
                $group->description = 'Administradores del sistema y curso';
                $group->descriptionformat = 1;
                $group->timecreated = time();
                $group->timemodified = time();
                
                $admin_group = new stdClass();
                $admin_group->id = $DB->insert_record('groups', $group);
                echo "   ✅ Grupo Administradores creado\n";
            }
            $target_group = $admin_group;
        } else {
            $target_group = $DB->get_record('groups', [
                'courseid' => $course->id,
                'name' => $group_name
            ]);
        }
        
        if ($target_group) {
            // Verificar si ya está en el grupo
            $existing_membership = $DB->get_record('groups_members', [
                'groupid' => $target_group->id,
                'userid' => $user->id
            ]);
            
            if (!$existing_membership) {
                $member = new stdClass();
                $member->groupid = $target_group->id;
                $member->userid = $user->id;
                $member->timeadded = time();
                
                $DB->insert_record('groups_members', $member);
                echo "   → Usuario agregado al $group_name\n";
            }
        }
    } else {
        echo "   ⚠️  Curso CBN-TELEMEDICINA-2025 no encontrado\n";
    }
}

echo "\n📊 Resumen de usuarios procesados:\n";
echo "   • Usuarios nuevos: " . count($created_users) . "\n";
echo "   • Usuarios actualizados: " . count($updated_users) . "\n";

// Mostrar información de acceso
echo "\n🔐 Información de acceso:\n";
echo "📧 jose.vargas@telesalud.gob.sv:\n";
echo "   • Rol: Instructor principal (editingteacher)\n";
echo "   • Institución: Ministerio de Salud - Telesalud\n";
echo "   • Grupo: Grupo Telesalud\n";
echo "   • Acceso: OAuth con Google (@telesalud.gob.sv)\n";

echo "\n📧 rodolfovargasoff@gmail.com:\n";
echo "   • Rol: Administrador del sistema (manager)\n";
echo "   • Institución: Administración del Sistema\n";
echo "   • Grupo: Grupo Administradores\n";
echo "   • Acceso: OAuth con Google (@gmail.com - permitido como excepción)\n";

// Verificar configuración final
echo "\n🔍 Verificando configuración final...\n";
$course = $DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
if ($course) {
    $total_enrolled = $DB->count_records_sql("
        SELECT COUNT(*) FROM {user_enrolments} ue 
        JOIN {enrol} e ON ue.enrolid = e.id 
        WHERE e.courseid = ?
    ", [$course->id]);
    
    echo "👥 Total usuarios enrollados en el curso: $total_enrolled\n";
    echo "🔗 URL del curso: http://34.72.133.6/course/view.php?id={$course->id}\n";
}

echo "\n✅ Proceso completado exitosamente\n";

// Mostrar instrucciones de login
echo "\n📋 INSTRUCCIONES DE LOGIN:\n";
echo "=" . str_repeat("=", 50) . "\n";
echo "1. Ir a: http://34.72.133.6/login/index.php\n";
echo "2. Hacer clic en 'Google Telesalud'\n";
echo "3. Ingresar credenciales de Google:\n";
echo "   • jose.vargas@telesalud.gob.sv\n";
echo "   • rodolfovargasoff@gmail.com\n";
echo "4. Serán redirigidos automáticamente a Moodle\n";
echo "5. Acceder al curso desde el dashboard\n";

echo "\n🔧 Si hay problemas de acceso:\n";
echo "• Verificar que las cuentas de Google existan\n";
echo "• Verificar configuración OAuth en Google Console\n";
echo "• Revisar logs: kubectl logs moodle-684db8486b-5c5zp\n";

?>