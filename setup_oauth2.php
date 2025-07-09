<?php
/**
 * Script para configurar OAuth 2.0 en Moodle
 * Ejecutar desde CLI: php setup_oauth2.php
 */

define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');

// Habilitar OAuth 2.0
echo "🔧 Configurando OAuth 2.0 en Moodle...\n";

// 1. Habilitar autenticación OAuth 2.0
$DB->execute("UPDATE {config} SET value = 'manual,oauth2' WHERE name = 'auth'");

// 2. Configurar settings básicos
$oauth_settings = [
    'auth_oauth2/autolinkedlogin' => '1',
    'auth_oauth2/mappingfield' => 'email',
    'auth_oauth2/removeuser' => 'suspend',
    'auth_oauth2/field_updatelocal_email' => 'onlogin',
    'auth_oauth2/field_updatelocal_firstname' => 'onlogin',
    'auth_oauth2/field_updatelocal_lastname' => 'onlogin',
];

foreach ($oauth_settings as $name => $value) {
    $DB->execute("INSERT INTO {config} (name, value) VALUES (?, ?) 
                  ON DUPLICATE KEY UPDATE value = ?", 
                  [$name, $value, $value]);
}

echo "✅ OAuth 2.0 configurado exitosamente\n";

// 3. Crear función para agregar proveedores
function add_oauth_provider($name, $clientid, $secret, $baseurl) {
    global $DB;
    
    $issuer = new stdClass();
    $issuer->name = $name;
    $issuer->image = '';
    $issuer->baseurl = $baseurl;
    $issuer->enabled = 1;
    $issuer->showonloginpage = 1;
    $issuer->requireconfirmation = 0;
    $issuer->timecreated = time();
    $issuer->timemodified = time();
    
    $issuer->id = $DB->insert_record('oauth2_issuer', $issuer);
    
    // Agregar endpoints
    $endpoints = [
        'authorization_endpoint' => $baseurl . '/auth',
        'token_endpoint' => $baseurl . '/token',
        'userinfo_endpoint' => $baseurl . '/userinfo',
        'discovery_endpoint' => $baseurl . '/.well-known/openid_configuration'
    ];
    
    foreach ($endpoints as $name => $url) {
        $endpoint = new stdClass();
        $endpoint->issuerid = $issuer->id;
        $endpoint->name = $name;
        $endpoint->url = $url;
        $DB->insert_record('oauth2_endpoint', $endpoint);
    }
    
    return $issuer->id;
}

echo "🔧 Configuración OAuth 2.0 completada\n";
echo "📋 Próximos pasos:\n";
echo "   1. Configurar proveedores específicos\n";
echo "   2. Obtener Client ID y Secret\n";
echo "   3. Configurar URLs de callback\n";
?>