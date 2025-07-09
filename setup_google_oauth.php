<?php
/**
 * Configurar Google OAuth 2.0 para Moodle
 * Para personal médico con cuentas de Google Workspace
 */

define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');

echo "🔧 Configurando Google OAuth 2.0...\n";

// Configuración Google OAuth 2.0
$google_config = [
    'name' => 'Google',
    'clientid' => 'TU_GOOGLE_CLIENT_ID',
    'clientsecret' => 'TU_GOOGLE_CLIENT_SECRET',
    'baseurl' => 'https://accounts.google.com',
    'image' => 'https://developers.google.com/identity/images/g-logo.png'
];

// Crear issuer de Google
$issuer = new stdClass();
$issuer->name = $google_config['name'];
$issuer->image = $google_config['image'];
$issuer->baseurl = $google_config['baseurl'];
$issuer->enabled = 1;
$issuer->showonloginpage = 1;
$issuer->requireconfirmation = 0;
$issuer->timecreated = time();
$issuer->timemodified = time();

$issuer_id = $DB->insert_record('oauth2_issuer', $issuer);

// Configurar endpoints de Google
$google_endpoints = [
    'authorization_endpoint' => 'https://accounts.google.com/o/oauth2/v2/auth',
    'token_endpoint' => 'https://oauth2.googleapis.com/token',
    'userinfo_endpoint' => 'https://openidconnect.googleapis.com/v1/userinfo',
    'discovery_endpoint' => 'https://accounts.google.com/.well-known/openid_configuration'
];

foreach ($google_endpoints as $name => $url) {
    $endpoint = new stdClass();
    $endpoint->issuerid = $issuer_id;
    $endpoint->name = $name;
    $endpoint->url = $url;
    $DB->insert_record('oauth2_endpoint', $endpoint);
}

// Configurar mapeo de campos
$field_mappings = [
    'email' => 'email',
    'firstname' => 'given_name',
    'lastname' => 'family_name',
    'picture' => 'picture'
];

foreach ($field_mappings as $internal => $external) {
    $mapping = new stdClass();
    $mapping->issuerid = $issuer_id;
    $mapping->internalfield = $internal;
    $mapping->externalfield = $external;
    $DB->insert_record('oauth2_user_field_mapping', $mapping);
}

echo "✅ Google OAuth 2.0 configurado\n";
echo "📋 Configuración necesaria en Google Console:\n";
echo "   • URL de callback: http://34.72.133.6/admin/oauth2callback.php\n";
echo "   • Dominios autorizados: 34.72.133.6\n";
echo "   • Actualizar CLIENT_ID y CLIENT_SECRET en este script\n";

// Instrucciones para obtener credenciales
echo "\n🔑 Pasos para obtener credenciales de Google:\n";
echo "1. Ve a: https://console.developers.google.com/\n";
echo "2. Crea un nuevo proyecto o selecciona uno existente\n";
echo "3. Habilita Google+ API y Google OAuth2 API\n";
echo "4. Ve a 'Credenciales' > 'Crear credenciales' > 'ID de cliente OAuth 2.0'\n";
echo "5. Tipo: Aplicación web\n";
echo "6. URI de redirección: http://34.72.133.6/admin/oauth2callback.php\n";
echo "7. Copia Client ID y Client Secret\n";

?>