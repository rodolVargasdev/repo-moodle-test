<?php
/**
 * Crear usuarios de ejemplo para dominios permitidos
 * @telesalud.gob.sv y @goes.gob.sv
 */

define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');

echo "👥 Creando usuarios de ejemplo para Telesalud...\n";

// Usuarios de ejemplo
$sample_users = [
    // Usuarios @telesalud.gob.sv
    [
        'username' => 'director.telesalud',
        'email' => 'director@telesalud.gob.sv',
        'firstname' => 'Director',
        'lastname' => 'Telesalud',
        'institution' => 'Ministerio de Salud',
        'department' => 'Dirección de Telesalud',
        'role' => 'manager'
    ],
    [
        'username' => 'medico.cardiologo',
        'email' => 'cardiologo@telesalud.gob.sv',
        'firstname' => 'María',
        'lastname' => 'Cardiólogo',
        'institution' => 'Hospital Nacional',
        'department' => 'Cardiología',
        'role' => 'editingteacher'
    ],
    [
        'username' => 'enfermera.jefe',
        'email' => 'enfermera.jefe@telesalud.gob.sv',
        'firstname' => 'Ana',
        'lastname' => 'Enfermera',
        'institution' => 'ISSS',
        'department' => 'Enfermería',
        'role' => 'teacher'
    ],
    [
        'username' => 'tecnico.radiologia',
        'email' => 'tecnico.radiologia@telesalud.gob.sv',
        'firstname' => 'Carlos',
        'lastname' => 'Técnico',
        'institution' => 'Hospital Regional',
        'department' => 'Radiología',
        'role' => 'student'
    ],
    
    // Usuarios @goes.gob.sv
    [
        'username' => 'coordinador.goes',
        'email' => 'coordinador@goes.gob.sv',
        'firstname' => 'Coordinador',
        'lastname' => 'GOES',
        'institution' => 'GOES',
        'department' => 'Coordinación General',
        'role' => 'manager'
    ],
    [
        'username' => 'paramedico.goes',
        'email' => 'paramedico@goes.gob.sv',
        'firstname' => 'José',
        'lastname' => 'Paramédico',
        'institution' => 'GOES',
        'department' => 'Emergencias',
        'role' => 'teacher'
    ],
    [
        'username' => 'instructor.goes',
        'email' => 'instructor@goes.gob.sv',
        'firstname' => 'Laura',
        'lastname' => 'Instructora',
        'institution' => 'GOES',
        'department' => 'Capacitación',
        'role' => 'editingteacher'
    ],
    [
        'username' => 'voluntario.goes',
        'email' => 'voluntario@goes.gob.sv',
        'firstname' => 'Pedro',
        'lastname' => 'Voluntario',
        'institution' => 'GOES',
        'department' => 'Voluntariado',
        'role' => 'student'
    ]
];

$created_users = [];
$updated_users = [];

foreach ($sample_users as $user_data) {
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
    
    // Asignar rol en contexto del sistema
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
            echo "   → Rol asignado: {$user_data['role']}\n";
        }
    }
}

echo "\n📊 Resumen de usuarios creados:\n";
echo "   • Usuarios nuevos: " . count($created_users) . "\n";
echo "   • Usuarios actualizados: " . count($updated_users) . "\n";

// Mostrar estadísticas por dominio
$telesalud_count = $DB->count_records_sql("SELECT COUNT(*) FROM {user} WHERE email LIKE '%@telesalud.gob.sv'");
$goes_count = $DB->count_records_sql("SELECT COUNT(*) FROM {user} WHERE email LIKE '%@goes.gob.sv'");

echo "\n📈 Estadísticas por dominio:\n";
echo "   • @telesalud.gob.sv: $telesalud_count usuarios\n";
echo "   • @goes.gob.sv: $goes_count usuarios\n";

// Verificar configuración de restricciones
echo "\n🔍 Verificando restricciones de dominio...\n";
$allowed_domains = get_config('core', 'allowemailaddresses');
echo "   • Dominios permitidos globalmente: $allowed_domains\n";

$issuer = $DB->get_record('oauth2_issuer', ['name' => 'Google Telesalud']);
if ($issuer) {
    $oauth_domains = $DB->get_record('oauth2_issuer_config', [
        'issuerid' => $issuer->id,
        'name' => 'alloweddomains'
    ]);
    if ($oauth_domains) {
        echo "   • Dominios OAuth configurados: {$oauth_domains->value}\n";
    }
}

echo "\n🎯 Usuarios de ejemplo creados exitosamente\n";
echo "📋 Próximos pasos:\n";
echo "1. Configurar credenciales de Google (si no se ha hecho)\n";
echo "2. Probar login OAuth con estos usuarios\n";
echo "3. Verificar que no se permitan otros dominios\n";

echo "\n🔐 Usuarios de prueba creados:\n";
foreach ($sample_users as $user_data) {
    echo "   • {$user_data['email']} ({$user_data['role']})\n";
}

echo "\n⚠️  IMPORTANTE:\n";
echo "• Estos usuarios solo pueden autenticarse vía OAuth 2.0\n";
echo "• Deben tener cuentas de Google en sus dominios correspondientes\n";
echo "• No tienen contraseña local en Moodle\n";

?>