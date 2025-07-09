<?php
/**
 * Verificar configuración OAuth 2.0 para Telesalud
 * Script de diagnóstico y validación
 */

define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');

echo "🔍 Verificando configuración OAuth 2.0 para Telesalud...\n\n";

$errors = [];
$warnings = [];
$success_count = 0;

// 1. Verificar que OAuth 2.0 esté habilitado
echo "1. Verificando autenticación OAuth 2.0...\n";
$auth_methods = get_config('core', 'auth');
if (strpos($auth_methods, 'oauth2') !== false) {
    echo "   ✅ OAuth 2.0 está habilitado\n";
    $success_count++;
} else {
    echo "   ❌ OAuth 2.0 NO está habilitado\n";
    $errors[] = "OAuth 2.0 no está habilitado en los métodos de autenticación";
}

// 2. Verificar dominios permitidos globalmente
echo "\n2. Verificando dominios permitidos globalmente...\n";
$allowed_domains = get_config('core', 'allowemailaddresses');
if ($allowed_domains && strpos($allowed_domains, 'telesalud.gob.sv') !== false && strpos($allowed_domains, 'goes.gob.sv') !== false) {
    echo "   ✅ Dominios permitidos configurados: $allowed_domains\n";
    $success_count++;
} else {
    echo "   ❌ Dominios permitidos NO configurados correctamente\n";
    echo "   Actual: " . ($allowed_domains ?: 'no configurado') . "\n";
    $errors[] = "Dominios permitidos no configurados: debe incluir telesalud.gob.sv,goes.gob.sv";
}

// 3. Verificar issuer de Google
echo "\n3. Verificando issuer de Google...\n";
$issuer = $DB->get_record('oauth2_issuer', ['name' => 'Google Telesalud']);
if ($issuer) {
    echo "   ✅ Issuer 'Google Telesalud' encontrado (ID: {$issuer->id})\n";
    echo "   • Habilitado: " . ($issuer->enabled ? 'Sí' : 'No') . "\n";
    echo "   • Visible en login: " . ($issuer->showonloginpage ? 'Sí' : 'No') . "\n";
    $success_count++;
    
    if (!$issuer->enabled) {
        $warnings[] = "El issuer de Google no está habilitado";
    }
    if (!$issuer->showonloginpage) {
        $warnings[] = "El issuer de Google no es visible en la página de login";
    }
} else {
    echo "   ❌ Issuer 'Google Telesalud' NO encontrado\n";
    $errors[] = "Issuer de Google no configurado";
}

// 4. Verificar endpoints de Google
if ($issuer) {
    echo "\n4. Verificando endpoints de Google...\n";
    $required_endpoints = [
        'authorization_endpoint',
        'token_endpoint',
        'userinfo_endpoint',
        'discovery_endpoint'
    ];
    
    $endpoints = $DB->get_records('oauth2_endpoint', ['issuerid' => $issuer->id]);
    $endpoint_names = array_column($endpoints, 'name');
    
    foreach ($required_endpoints as $endpoint) {
        if (in_array($endpoint, $endpoint_names)) {
            echo "   ✅ $endpoint configurado\n";
            $success_count++;
        } else {
            echo "   ❌ $endpoint NO configurado\n";
            $errors[] = "Endpoint $endpoint no configurado";
        }
    }
}

// 5. Verificar credenciales de Google
if ($issuer) {
    echo "\n5. Verificando credenciales de Google...\n";
    $client_id = $DB->get_record('oauth2_issuer_config', [
        'issuerid' => $issuer->id,
        'name' => 'clientid'
    ]);
    $client_secret = $DB->get_record('oauth2_issuer_config', [
        'issuerid' => $issuer->id,
        'name' => 'clientsecret'
    ]);
    
    if ($client_id && $client_id->value && $client_id->value !== 'TU_GOOGLE_CLIENT_ID_AQUI') {
        echo "   ✅ Client ID configurado\n";
        $success_count++;
    } else {
        echo "   ❌ Client ID NO configurado\n";
        $errors[] = "Client ID de Google no configurado";
    }
    
    if ($client_secret && $client_secret->value && $client_secret->value !== 'TU_GOOGLE_CLIENT_SECRET_AQUI') {
        echo "   ✅ Client Secret configurado\n";
        $success_count++;
    } else {
        echo "   ❌ Client Secret NO configurado\n";
        $errors[] = "Client Secret de Google no configurado";
    }
}

// 6. Verificar restricciones de dominio OAuth
if ($issuer) {
    echo "\n6. Verificando restricciones de dominio OAuth...\n";
    $oauth_domains = $DB->get_record('oauth2_issuer_config', [
        'issuerid' => $issuer->id,
        'name' => 'alloweddomains'
    ]);
    
    if ($oauth_domains && strpos($oauth_domains->value, 'telesalud.gob.sv') !== false && strpos($oauth_domains->value, 'goes.gob.sv') !== false) {
        echo "   ✅ Dominios OAuth configurados: {$oauth_domains->value}\n";
        $success_count++;
    } else {
        echo "   ❌ Dominios OAuth NO configurados correctamente\n";
        $errors[] = "Dominios OAuth no configurados para el issuer";
    }
}

// 7. Verificar mapeo de campos
if ($issuer) {
    echo "\n7. Verificando mapeo de campos...\n";
    $mappings = $DB->get_records('oauth2_user_field_mapping', ['issuerid' => $issuer->id]);
    $required_mappings = ['email', 'firstname', 'lastname'];
    
    foreach ($required_mappings as $field) {
        $found = false;
        foreach ($mappings as $mapping) {
            if ($mapping->internalfield === $field) {
                echo "   ✅ Campo '$field' mapeado a '{$mapping->externalfield}'\n";
                $found = true;
                $success_count++;
                break;
            }
        }
        if (!$found) {
            echo "   ❌ Campo '$field' NO mapeado\n";
            $errors[] = "Campo $field no mapeado";
        }
    }
}

// 8. Verificar usuarios de ejemplo
echo "\n8. Verificando usuarios de ejemplo...\n";
$telesalud_users = $DB->count_records_sql("SELECT COUNT(*) FROM {user} WHERE email LIKE '%@telesalud.gob.sv'");
$goes_users = $DB->count_records_sql("SELECT COUNT(*) FROM {user} WHERE email LIKE '%@goes.gob.sv'");

if ($telesalud_users > 0) {
    echo "   ✅ Usuarios @telesalud.gob.sv: $telesalud_users\n";
    $success_count++;
} else {
    echo "   ⚠️  No hay usuarios @telesalud.gob.sv\n";
    $warnings[] = "No hay usuarios de ejemplo para telesalud.gob.sv";
}

if ($goes_users > 0) {
    echo "   ✅ Usuarios @goes.gob.sv: $goes_users\n";
    $success_count++;
} else {
    echo "   ⚠️  No hay usuarios @goes.gob.sv\n";
    $warnings[] = "No hay usuarios de ejemplo para goes.gob.sv";
}

// 9. Verificar configuración de prevención de creación de cuentas
echo "\n9. Verificando prevención de creación de cuentas...\n";
$prevent_account_creation = get_config('auth_oauth2', 'preventaccountcreation');
if ($prevent_account_creation) {
    echo "   ✅ Prevención de creación automática habilitada\n";
    $success_count++;
} else {
    echo "   ⚠️  Prevención de creación automática NO habilitada\n";
    $warnings[] = "La creación automática de cuentas está habilitada (puede ser inseguro)";
}

// 10. Verificar URLs críticas
echo "\n10. Verificando URLs críticas...\n";
$wwwroot = $CFG->wwwroot;
echo "   • Moodle URL: $wwwroot\n";
echo "   • Login URL: $wwwroot/login/index.php\n";
echo "   • Callback URL: $wwwroot/admin/oauth2callback.php\n";

if (strpos($wwwroot, 'https://') === 0) {
    echo "   ✅ HTTPS configurado\n";
    $success_count++;
} else {
    echo "   ⚠️  HTTP en uso (recomendado HTTPS para producción)\n";
    $warnings[] = "Se recomienda HTTPS para producción";
}

// Resumen final
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 RESUMEN DE VERIFICACIÓN\n";
echo str_repeat("=", 60) . "\n";

echo "✅ Verificaciones exitosas: $success_count\n";
echo "❌ Errores encontrados: " . count($errors) . "\n";
echo "⚠️  Advertencias: " . count($warnings) . "\n";

if (count($errors) > 0) {
    echo "\n🚨 ERRORES QUE DEBEN SER CORREGIDOS:\n";
    foreach ($errors as $i => $error) {
        echo "   " . ($i + 1) . ". $error\n";
    }
}

if (count($warnings) > 0) {
    echo "\n⚠️  ADVERTENCIAS:\n";
    foreach ($warnings as $i => $warning) {
        echo "   " . ($i + 1) . ". $warning\n";
    }
}

if (count($errors) === 0) {
    echo "\n🎉 ¡CONFIGURACIÓN OAUTH 2.0 CORRECTA!\n";
    echo "✅ Todos los componentes críticos están configurados\n";
    echo "🔗 URL de prueba: $wwwroot/login/index.php\n";
    echo "🔐 Los usuarios pueden autenticarse con Google\n";
    echo "🏥 Solo dominios @telesalud.gob.sv y @goes.gob.sv permitidos\n";
} else {
    echo "\n🔧 ACCIÓN REQUERIDA:\n";
    echo "❌ Hay errores que deben ser corregidos antes de usar OAuth 2.0\n";
    echo "📋 Ejecuta los scripts de configuración faltantes\n";
}

echo "\n📋 SCRIPTS DISPONIBLES:\n";
echo "• setup_google_oauth_telesalud.php - Configuración inicial\n";
echo "• set_google_credentials.php - Configurar credenciales\n";
echo "• create_telesalud_users.php - Crear usuarios de ejemplo\n";
echo "• verify_oauth_setup.php - Este script de verificación\n";

?>