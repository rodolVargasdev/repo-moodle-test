<?php
/**
 * Verificar configuración de usuarios específicos
 * - jose.vargas@telesalud.gob.sv
 * - rodolfovargasoff@gmail.com
 */

define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');

echo "🔍 Verificando usuarios específicos en Moodle...\n";
echo "=" . str_repeat("=", 60) . "\n";

$target_emails = [
    'jose.vargas@telesalud.gob.sv',
    'rodolfovargasoff@gmail.com'
];

$course = $DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);

foreach ($target_emails as $email) {
    echo "\n👤 Verificando: $email\n";
    echo "-" . str_repeat("-", 50) . "\n";
    
    // 1. Verificar que el usuario existe
    $user = $DB->get_record('user', ['email' => $email]);
    if ($user) {
        echo "✅ Usuario existe en Moodle\n";
        echo "   • ID: {$user->id}\n";
        echo "   • Username: {$user->username}\n";
        echo "   • Nombre: {$user->firstname} {$user->lastname}\n";
        echo "   • Institución: {$user->institution}\n";
        echo "   • Departamento: {$user->department}\n";
        echo "   • Método auth: {$user->auth}\n";
        echo "   • Confirmado: " . ($user->confirmed ? 'Sí' : 'No') . "\n";
    } else {
        echo "❌ Usuario NO existe en Moodle\n";
        continue;
    }
    
    // 2. Verificar roles globales
    $global_roles = $DB->get_records_sql("
        SELECT r.shortname, r.name 
        FROM {role_assignments} ra 
        JOIN {role} r ON ra.roleid = r.id 
        JOIN {context} c ON ra.contextid = c.id 
        WHERE ra.userid = ? AND c.contextlevel = 10
    ", [$user->id]);
    
    if ($global_roles) {
        echo "🎭 Roles globales:\n";
        foreach ($global_roles as $role) {
            echo "   • {$role->shortname}: {$role->name}\n";
        }
    } else {
        echo "⚠️  Sin roles globales asignados\n";
    }
    
    // 3. Verificar enrollment en el curso
    if ($course) {
        $enrollment = $DB->get_record_sql("
            SELECT ue.*, r.shortname as role_shortname, r.name as role_name
            FROM {user_enrolments} ue
            JOIN {enrol} e ON ue.enrolid = e.id
            JOIN {role_assignments} ra ON ra.userid = ue.userid
            JOIN {role} r ON ra.roleid = r.id
            JOIN {context} ctx ON ra.contextid = ctx.id
            WHERE e.courseid = ? AND ue.userid = ? AND ctx.instanceid = ?
        ", [$course->id, $user->id, $course->id]);
        
        if ($enrollment) {
            echo "🎓 Enrollment en curso:\n";
            echo "   • Curso: CBN-TELEMEDICINA-2025\n";
            echo "   • Rol: {$enrollment->role_shortname} ({$enrollment->role_name})\n";
            echo "   • Estado: " . ($enrollment->status == 0 ? 'Activo' : 'Suspendido') . "\n";
            echo "   • Fecha inscripción: " . date('Y-m-d H:i:s', $enrollment->timecreated) . "\n";
        } else {
            echo "❌ NO enrollado en el curso de telemedicina\n";
        }
        
        // 4. Verificar grupos
        $groups = $DB->get_records_sql("
            SELECT g.name, g.description
            FROM {groups_members} gm
            JOIN {groups} g ON gm.groupid = g.id
            WHERE gm.userid = ? AND g.courseid = ?
        ", [$user->id, $course->id]);
        
        if ($groups) {
            echo "👥 Grupos del curso:\n";
            foreach ($groups as $group) {
                echo "   • {$group->name}: {$group->description}\n";
            }
        } else {
            echo "⚠️  No pertenece a ningún grupo del curso\n";
        }
    } else {
        echo "❌ Curso CBN-TELEMEDICINA-2025 no encontrado\n";
    }
    
    // 5. Verificar acceso OAuth
    echo "🔐 Verificación OAuth:\n";
    
    // Verificar dominios permitidos
    $allowed_domains = get_config('core', 'allowemailaddresses');
    $domain = substr($email, strpos($email, '@') + 1);
    $domain_allowed = strpos($allowed_domains, $domain) !== false;
    
    echo "   • Dominio del usuario: $domain\n";
    echo "   • Dominios permitidos: $allowed_domains\n";
    echo "   • Dominio permitido: " . ($domain_allowed ? 'Sí' : 'No') . "\n";
    
    // Verificar OAuth issuer
    $oauth_issuer = $DB->get_record('oauth2_issuer', ['name' => 'Google Telesalud']);
    if ($oauth_issuer) {
        echo "   • OAuth issuer: Configurado ({$oauth_issuer->name})\n";
        echo "   • OAuth habilitado: " . ($oauth_issuer->enabled ? 'Sí' : 'No') . "\n";
        
        $oauth_domains = $DB->get_record('oauth2_issuer_config', [
            'issuerid' => $oauth_issuer->id,
            'name' => 'alloweddomains'
        ]);
        
        if ($oauth_domains) {
            $oauth_domain_allowed = strpos($oauth_domains->value, $domain) !== false;
            echo "   • OAuth dominios: {$oauth_domains->value}\n";
            echo "   • OAuth dominio permitido: " . ($oauth_domain_allowed ? 'Sí' : 'No') . "\n";
        }
    } else {
        echo "   • OAuth issuer: NO configurado\n";
    }
}

// Resumen general
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 RESUMEN GENERAL\n";
echo str_repeat("=", 60) . "\n";

// Contar usuarios por dominio
$telesalud_users = $DB->count_records_sql("SELECT COUNT(*) FROM {user} WHERE email LIKE '%@telesalud.gob.sv'");
$gmail_users = $DB->count_records_sql("SELECT COUNT(*) FROM {user} WHERE email LIKE '%@gmail.com'");
$goes_users = $DB->count_records_sql("SELECT COUNT(*) FROM {user} WHERE email LIKE '%@goes.gob.sv'");

echo "👥 Usuarios por dominio:\n";
echo "   • @telesalud.gob.sv: $telesalud_users\n";
echo "   • @goes.gob.sv: $goes_users\n";
echo "   • @gmail.com: $gmail_users\n";

if ($course) {
    $total_enrolled = $DB->count_records_sql("
        SELECT COUNT(*) FROM {user_enrolments} ue 
        JOIN {enrol} e ON ue.enrolid = e.id 
        WHERE e.courseid = ?
    ", [$course->id]);
    
    echo "\n🎓 Curso CBN-TELEMEDICINA-2025:\n";
    echo "   • Total enrollados: $total_enrolled\n";
    echo "   • URL: http://34.72.133.6/course/view.php?id={$course->id}\n";
    
    // Contar por roles en el curso
    $course_roles = $DB->get_records_sql("
        SELECT r.shortname, r.name, COUNT(*) as count
        FROM {user_enrolments} ue
        JOIN {enrol} e ON ue.enrolid = e.id
        JOIN {role_assignments} ra ON ra.userid = ue.userid
        JOIN {role} r ON ra.roleid = r.id
        JOIN {context} ctx ON ra.contextid = ctx.id
        WHERE e.courseid = ? AND ctx.instanceid = ?
        GROUP BY r.shortname, r.name
    ", [$course->id, $course->id]);
    
    echo "   • Usuarios por rol:\n";
    foreach ($course_roles as $role) {
        echo "     - {$role->shortname}: {$role->count}\n";
    }
}

// Verificar configuración OAuth general
echo "\n🔐 Configuración OAuth:\n";
$auth_methods = get_config('core', 'auth');
echo "   • Métodos auth: $auth_methods\n";

$oauth_enabled = strpos($auth_methods, 'oauth2') !== false;
echo "   • OAuth 2.0: " . ($oauth_enabled ? 'Habilitado' : 'Deshabilitado') . "\n";

$allowed_domains = get_config('core', 'allowemailaddresses');
echo "   • Dominios permitidos: $allowed_domains\n";

echo "\n📋 INSTRUCCIONES DE ACCESO:\n";
echo str_repeat("-", 40) . "\n";
echo "1. URL de login: http://34.72.133.6/login/index.php\n";
echo "2. Hacer clic en 'Google Telesalud'\n";
echo "3. Usar credenciales de Google:\n";
echo "   • jose.vargas@telesalud.gob.sv\n";
echo "   • rodolfovargasoff@gmail.com\n";
echo "4. Acceder al curso desde el dashboard\n";

echo "\n✅ Verificación completada\n";

?>