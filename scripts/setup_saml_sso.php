<?php
/**
 * Configurar SAML 2.0 Single Sign-On para Moodle
 * Ideal para hospitales con Active Directory Federation Services
 */

define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');

echo "🔧 Configurando SAML 2.0 SSO...\n";

// Habilitar autenticación SAML
$DB->execute("UPDATE {config} SET value = 'manual,saml2' WHERE name = 'auth'");

// Configuraciones SAML básicas
$saml_settings = [
    'auth_saml2/idpname' => 'Hospital SSO',
    'auth_saml2/entityid' => 'http://34.72.133.6/auth/saml2/sp/metadata.php',
    'auth_saml2/idpmetadata' => '', // Se configurará después
    'auth_saml2/autocreate' => '1',
    'auth_saml2/anyauth' => '1',
    'auth_saml2/duallogin' => '1',
    'auth_saml2/alloweddomains' => 'hospital.com,clinica.com',
    'auth_saml2/debug' => '1', // Para debugging inicial
    
    // Mapeo de campos
    'auth_saml2/field_map_email' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress',
    'auth_saml2/field_map_firstname' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname',
    'auth_saml2/field_map_lastname' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname',
    'auth_saml2/field_map_idnumber' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/nameidentifier',
    
    // Configuraciones de seguridad
    'auth_saml2/requiresignature' => '1',
    'auth_saml2/requireencryption' => '1',
    'auth_saml2/logout' => '1',
    'auth_saml2/logouturl' => 'https://sso.hospital.com/logout',
];

foreach ($saml_settings as $name => $value) {
    $DB->execute("INSERT INTO {config} (name, value) VALUES (?, ?) 
                  ON DUPLICATE KEY UPDATE value = ?", 
                  [$name, $value, $value]);
}

echo "✅ SAML 2.0 configurado básicamente\n";

// Crear certificado para SAML
echo "🔑 Creando certificado SAML...\n";
$cert_config = [
    "digest_alg" => "sha256",
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
];

$dn = [
    "countryName" => "ES",
    "stateOrProvinceName" => "Madrid",
    "localityName" => "Madrid",
    "organizationName" => "Hospital Medical Training",
    "organizationalUnitName" => "IT Department",
    "commonName" => "34.72.133.6",
    "emailAddress" => "admin@hospital.com"
];

$privkey = openssl_pkey_new($cert_config);
$csr = openssl_csr_new($dn, $privkey, $cert_config);
$x509 = openssl_csr_sign($csr, null, $privkey, 365, $cert_config);

openssl_x509_export($x509, $cert_out);
openssl_pkey_export($privkey, $privkey_out);

// Guardar certificado y clave privada
$cert_dir = '/bitnami/moodle/auth/saml2/certs/';
if (!is_dir($cert_dir)) {
    mkdir($cert_dir, 0755, true);
}

file_put_contents($cert_dir . 'saml.crt', $cert_out);
file_put_contents($cert_dir . 'saml.key', $privkey_out);

echo "✅ Certificado SAML creado\n";

// Configurar metadata XML
$metadata_xml = '<?xml version="1.0" encoding="UTF-8"?>
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" 
                     entityID="http://34.72.133.6/auth/saml2/sp/metadata.php">
    <md:SPSSODescriptor AuthnRequestsSigned="true" 
                        WantAssertionsSigned="true" 
                        protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
        
        <md:KeyDescriptor use="signing">
            <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
                <ds:X509Data>
                    <ds:X509Certificate>' . base64_encode($cert_out) . '</ds:X509Certificate>
                </ds:X509Data>
            </ds:KeyInfo>
        </md:KeyDescriptor>
        
        <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" 
                                Location="http://34.72.133.6/auth/saml2/sp/saml2-logout.php"/>
        
        <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress</md:NameIDFormat>
        
        <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" 
                                     Location="http://34.72.133.6/auth/saml2/sp/saml2-acs.php" 
                                     index="1"/>
    </md:SPSSODescriptor>
</md:EntityDescriptor>';

file_put_contents($cert_dir . 'metadata.xml', $metadata_xml);

echo "📋 Configuración SAML completada\n";
echo "📄 Archivos creados:\n";
echo "   • Certificado: /bitnami/moodle/auth/saml2/certs/saml.crt\n";
echo "   • Clave privada: /bitnami/moodle/auth/saml2/certs/saml.key\n";
echo "   • Metadata: /bitnami/moodle/auth/saml2/certs/metadata.xml\n";

echo "\n🏥 Configuración en Active Directory Federation Services:\n";
echo "1. Añadir Relying Party Trust\n";
echo "2. Importar metadata desde: http://34.72.133.6/auth/saml2/sp/metadata.php\n";
echo "3. Configurar Claim Rules para email, nombre, apellido\n";
echo "4. Configurar grupos de seguridad por especialidad médica\n";

echo "\n🔐 Configuraciones de seguridad recomendadas:\n";
echo "• Habilitar MFA en ADFS\n";
echo "• Configurar acceso condicional\n";
echo "• Restringir por ubicación IP\n";
echo "• Configurar timeout de sesión\n";
echo "• Habilitar logging de auditoría\n";

?>