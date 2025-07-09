<?php
/**
 * Configurar credenciales de Google OAuth 2.0
 * Ejecutar después de obtener Client ID y Client Secret
 */

define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');

// Configurar aquí las credenciales obtenidas de Google Console
$GOOGLE_CLIENT_ID = 'TU_GOOGLE_CLIENT_ID_AQUI';
$GOOGLE_CLIENT_SECRET = 'TU_GOOGLE_CLIENT_SECRET_AQUI';

// Verificar que se hayan configurado las credenciales
if ($GOOGLE_CLIENT_ID === 'TU_GOOGLE_CLIENT_ID_AQUI' || $GOOGLE_CLIENT_SECRET === 'TU_GOOGLE_CLIENT_SECRET_AQUI') {
    echo "❌ ERROR: Debes configurar las credenciales de Google primero\n";
    echo "   Edita este archivo y reemplaza:\n";
    echo "   • TU_GOOGLE_CLIENT_ID_AQUI\n";
    echo "   • TU_GOOGLE_CLIENT_SECRET_AQUI\n";
    echo "   Con las credenciales obtenidas de Google Console\n";
    exit(1);
}

echo "🔧 Configurando credenciales de Google OAuth 2.0...\n";

// Buscar el issuer de Google Telesalud
$issuer = $DB->get_record('oauth2_issuer', ['name' => 'Google Telesalud']);
if (!$issuer) {
    echo "❌ ERROR: No se encontró el issuer 'Google Telesalud'\n";
    echo "   Ejecuta primero: php setup_google_oauth_telesalud.php\n";
    exit(1);
}

// Configurar Client ID
$client_id_config = $DB->get_record('oauth2_issuer_config', [
    'issuerid' => $issuer->id,
    'name' => 'clientid'
]);

if ($client_id_config) {
    $client_id_config->value = $GOOGLE_CLIENT_ID;
    $DB->update_record('oauth2_issuer_config', $client_id_config);
    echo "✅ Client ID actualizado\n";
} else {
    $client_id_config = new stdClass();
    $client_id_config->issuerid = $issuer->id;
    $client_id_config->name = 'clientid';
    $client_id_config->value = $GOOGLE_CLIENT_ID;
    $DB->insert_record('oauth2_issuer_config', $client_id_config);
    echo "✅ Client ID configurado\n";
}

// Configurar Client Secret
$client_secret_config = $DB->get_record('oauth2_issuer_config', [
    'issuerid' => $issuer->id,
    'name' => 'clientsecret'
]);

if ($client_secret_config) {
    $client_secret_config->value = $GOOGLE_CLIENT_SECRET;
    $DB->update_record('oauth2_issuer_config', $client_secret_config);
    echo "✅ Client Secret actualizado\n";
} else {
    $client_secret_config = new stdClass();
    $client_secret_config->issuerid = $issuer->id;
    $client_secret_config->name = 'clientsecret';
    $client_secret_config->value = $GOOGLE_CLIENT_SECRET;
    $DB->insert_record('oauth2_issuer_config', $client_secret_config);
    echo "✅ Client Secret configurado\n";
}

// Verificar configuración
echo "\n🔍 Verificando configuración...\n";
$configs = $DB->get_records('oauth2_issuer_config', ['issuerid' => $issuer->id]);
foreach ($configs as $config) {
    if ($config->name === 'clientsecret') {
        echo "   • Client Secret: " . str_repeat('*', strlen($config->value)) . "\n";
    } else {
        echo "   • {$config->name}: {$config->value}\n";
    }
}

echo "\n🎯 Credenciales configuradas exitosamente\n";
echo "📋 Próximos pasos:\n";
echo "1. Crear usuarios para dominios @telesalud.gob.sv y @goes.gob.sv\n";
echo "2. Probar autenticación OAuth 2.0\n";
echo "3. Verificar restricciones de dominio\n";

echo "\n🔗 URLs importantes:\n";
echo "• Moodle: http://34.72.133.6\n";
echo "• Login OAuth: http://34.72.133.6/login/index.php\n";
echo "• Callback: http://34.72.133.6/admin/oauth2callback.php\n";

?>