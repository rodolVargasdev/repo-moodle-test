<?php
/**
 * Configuración OAuth 2.0 Google específica para Telesalud
 * Restricciones: @telesalud.gob.sv y @goes.gob.sv
 */

define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');

echo "🔧 Configurando OAuth 2.0 Google para Telesalud...\n";

// 1. Habilitar autenticación OAuth 2.0
$auth_methods = get_config('core', 'auth');
if (strpos($auth_methods, 'oauth2') === false) {
    $new_auth = $auth_methods ? $auth_methods . ',oauth2' : 'oauth2';
    set_config('auth', $new_auth);
    echo "✅ Autenticación OAuth 2.0 habilitada\n";
}

// 2. Configurar restricciones de dominio GLOBALES
$allowed_domains = 'telesalud.gob.sv,goes.gob.sv';
set_config('auth_oauth2/alloweddomains', $allowed_domains);
echo "✅ Dominios permitidos configurados: $allowed_domains\n";

// 3. Configurar prevención de creación automática de cuentas
set_config('auth_oauth2/preventaccountcreation', 1);
echo "✅ Prevención de creación automática de cuentas habilitada\n";

// 4. Configuraciones básicas OAuth 2.0
$oauth_settings = [
    'auth_oauth2/autolinkedlogin' => '1',
    'auth_oauth2/mappingfield' => 'email',
    'auth_oauth2/removeuser' => 'suspend',
    'auth_oauth2/field_updatelocal_email' => 'onlogin',
    'auth_oauth2/field_updatelocal_firstname' => 'onlogin',
    'auth_oauth2/field_updatelocal_lastname' => 'onlogin',
    'auth_oauth2/field_updatelocal_institution' => 'onlogin',
    'auth_oauth2/field_updatelocal_department' => 'onlogin',
];

foreach ($oauth_settings as $name => $value) {
    set_config(str_replace('auth_oauth2/', '', $name), $value, 'auth_oauth2');
}

echo "✅ Configuraciones OAuth 2.0 básicas aplicadas\n";

// 5. Crear issuer de Google con restricciones
$google_issuer = new stdClass();
$google_issuer->name = 'Google Telesalud';
$google_issuer->image = 'https://developers.google.com/identity/images/g-logo.png';
$google_issuer->baseurl = 'https://accounts.google.com';
$google_issuer->enabled = 1;
$google_issuer->showonloginpage = 1;
$google_issuer->requireconfirmation = 0;
$google_issuer->loginscopes = 'openid email profile';
$google_issuer->loginscopesoffline = 'openid email profile';
$google_issuer->timecreated = time();
$google_issuer->timemodified = time();

// Verificar si ya existe el issuer
$existing_issuer = $DB->get_record('oauth2_issuer', ['name' => 'Google Telesalud']);
if ($existing_issuer) {
    $google_issuer->id = $existing_issuer->id;
    $DB->update_record('oauth2_issuer', $google_issuer);
    $issuer_id = $existing_issuer->id;
    echo "✅ Issuer Google Telesalud actualizado\n";
} else {
    $issuer_id = $DB->insert_record('oauth2_issuer', $google_issuer);
    echo "✅ Issuer Google Telesalud creado\n";
}

// 6. Configurar endpoints de Google
$google_endpoints = [
    'authorization_endpoint' => 'https://accounts.google.com/o/oauth2/v2/auth',
    'token_endpoint' => 'https://oauth2.googleapis.com/token',
    'userinfo_endpoint' => 'https://openidconnect.googleapis.com/v1/userinfo',
    'discovery_endpoint' => 'https://accounts.google.com/.well-known/openid_configuration'
];

// Limpiar endpoints existentes
$DB->delete_records('oauth2_endpoint', ['issuerid' => $issuer_id]);

foreach ($google_endpoints as $name => $url) {
    $endpoint = new stdClass();
    $endpoint->issuerid = $issuer_id;
    $endpoint->name = $name;
    $endpoint->url = $url;
    $DB->insert_record('oauth2_endpoint', $endpoint);
}

echo "✅ Endpoints de Google configurados\n";

// 7. Configurar mapeo de campos
$field_mappings = [
    'email' => 'email',
    'firstname' => 'given_name',
    'lastname' => 'family_name',
    'picture' => 'picture'
];

// Limpiar mapeos existentes
$DB->delete_records('oauth2_user_field_mapping', ['issuerid' => $issuer_id]);

foreach ($field_mappings as $internal => $external) {
    $mapping = new stdClass();
    $mapping->issuerid = $issuer_id;
    $mapping->internalfield = $internal;
    $mapping->externalfield = $external;
    $DB->insert_record('oauth2_user_field_mapping', $mapping);
}

echo "✅ Mapeo de campos configurado\n";

// 8. Configurar restricciones específicas del issuer
$issuer_configs = [
    'logindomain' => 'telesalud.gob.sv,goes.gob.sv',
    'alloweddomains' => 'telesalud.gob.sv,goes.gob.sv',
    'hd' => 'telesalud.gob.sv,goes.gob.sv', // Hosted Domain para Google
];

// Limpiar configuraciones existentes del issuer
$DB->delete_records('oauth2_issuer_config', ['issuerid' => $issuer_id]);

foreach ($issuer_configs as $name => $value) {
    $config = new stdClass();
    $config->issuerid = $issuer_id;
    $config->name = $name;
    $config->value = $value;
    $DB->insert_record('oauth2_issuer_config', $config);
}

echo "✅ Restricciones de dominio específicas configuradas\n";

// 9. Configurar dominios permitidos globalmente
set_config('allowemailaddresses', 'telesalud.gob.sv,goes.gob.sv');
echo "✅ Dominios de email permitidos configurados globalmente\n";

echo "\n🎯 Configuración completada exitosamente\n";
echo "📋 Resumen de configuración:\n";
echo "   • Proveedor: Google OAuth 2.0\n";
echo "   • Dominios permitidos: telesalud.gob.sv, goes.gob.sv\n";
echo "   • Creación automática de cuentas: DESHABILITADA\n";
echo "   • Actualización de perfiles: HABILITADA\n";
echo "   • Visibilidad en login: HABILITADA\n";

echo "\n🔑 IMPORTANTE - Configuración en Google Console:\n";
echo "1. Ve a: https://console.developers.google.com/\n";
echo "2. Crea credenciales OAuth 2.0\n";
echo "3. URI de redirección: http://34.72.133.6/admin/oauth2callback.php\n";
echo "4. Configurar Client ID y Client Secret (ver siguiente script)\n";

echo "\n⚠️  RECORDATORIO:\n";
echo "• Los usuarios deben existir previamente en Moodle\n";
echo "• Solo emails @telesalud.gob.sv y @goes.gob.sv pueden autenticarse\n";
echo "• Configurar Client ID y Secret antes de usar\n";

?>