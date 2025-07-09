<?php
/**
 * Configurar Microsoft Azure AD OAuth 2.0 para Moodle
 * Ideal para hospitales con Office 365
 */

define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');

echo "🔧 Configurando Microsoft Azure AD OAuth 2.0...\n";

// Configuración Microsoft Azure AD
$tenant_id = 'TU_TENANT_ID'; // Reemplazar con tu Tenant ID
$microsoft_config = [
    'name' => 'Microsoft',
    'clientid' => 'TU_MICROSOFT_CLIENT_ID',
    'clientsecret' => 'TU_MICROSOFT_CLIENT_SECRET',
    'baseurl' => "https://login.microsoftonline.com/$tenant_id",
    'image' => 'https://docs.microsoft.com/en-us/azure/active-directory/develop/media/howto-add-branding-in-azure-ad-apps/ms-symbollockup_mssymbol_19.png'
];

// Crear issuer de Microsoft
$issuer = new stdClass();
$issuer->name = $microsoft_config['name'];
$issuer->image = $microsoft_config['image'];
$issuer->baseurl = $microsoft_config['baseurl'];
$issuer->enabled = 1;
$issuer->showonloginpage = 1;
$issuer->requireconfirmation = 0;
$issuer->timecreated = time();
$issuer->timemodified = time();

$issuer_id = $DB->insert_record('oauth2_issuer', $issuer);

// Configurar endpoints de Microsoft
$microsoft_endpoints = [
    'authorization_endpoint' => "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/authorize",
    'token_endpoint' => "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/token",
    'userinfo_endpoint' => 'https://graph.microsoft.com/v1.0/me',
    'discovery_endpoint' => "https://login.microsoftonline.com/$tenant_id/v2.0/.well-known/openid_configuration"
];

foreach ($microsoft_endpoints as $name => $url) {
    $endpoint = new stdClass();
    $endpoint->issuerid = $issuer_id;
    $endpoint->name = $name;
    $endpoint->url = $url;
    $DB->insert_record('oauth2_endpoint', $endpoint);
}

// Configurar mapeo de campos para Microsoft
$field_mappings = [
    'email' => 'mail',
    'firstname' => 'givenName',
    'lastname' => 'surname',
    'username' => 'userPrincipalName'
];

foreach ($field_mappings as $internal => $external) {
    $mapping = new stdClass();
    $mapping->issuerid = $issuer_id;
    $mapping->internalfield = $internal;
    $mapping->externalfield = $external;
    $DB->insert_record('oauth2_user_field_mapping', $mapping);
}

// Configurar scopes específicos para Microsoft
$scopes = [
    'openid',
    'profile',
    'email',
    'User.Read'
];

$scope_config = new stdClass();
$scope_config->issuerid = $issuer_id;
$scope_config->name = 'scope';
$scope_config->value = implode(' ', $scopes);
$DB->insert_record('oauth2_issuer_config', $scope_config);

echo "✅ Microsoft Azure AD OAuth 2.0 configurado\n";
echo "📋 Configuración necesaria en Azure Portal:\n";
echo "   • URL de callback: http://34.72.133.6/admin/oauth2callback.php\n";
echo "   • Permisos: User.Read, openid, profile, email\n";
echo "   • Actualizar TENANT_ID, CLIENT_ID y CLIENT_SECRET\n";

// Instrucciones para Azure AD
echo "\n🔑 Pasos para configurar en Azure Portal:\n";
echo "1. Ve a: https://portal.azure.com/\n";
echo "2. Azure Active Directory > App registrations > New registration\n";
echo "3. Nombre: Moodle Medical Training\n";
echo "4. Redirect URI: http://34.72.133.6/admin/oauth2callback.php\n";
echo "5. API permissions > Add Microsoft Graph permissions\n";
echo "6. Certificates & secrets > New client secret\n";
echo "7. Copia Application ID, Directory ID y Client Secret\n";

// Configuración adicional para organizaciones médicas
echo "\n🏥 Configuración adicional para entornos médicos:\n";
echo "• Habilitar acceso condicional basado en dispositivos\n";
echo "• Configurar MFA obligatorio para personal médico\n";
echo "• Restringir acceso por ubicación (solo hospital/clínica)\n";
echo "• Configurar grupos de seguridad por especialidad\n";

?>