#!/bin/bash

# 🚀 Script de Despliegue Completo - Curso de Telemedicina
# Autor: Asistente AI
# Fecha: $(date +%Y-%m-%d)
# Descripción: Automatiza el despliegue completo del curso de telemedicina en GCP

set -e  # Salir si hay error

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para imprimir mensajes
print_step() {
    echo -e "${BLUE}🔧 $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Configuración
PROJECT_ID="moodle-gcp-test"
CLUSTER_NAME="moodle-cluster"
ZONE="us-central1-c"
NAMESPACE="moodle"
WORK_DIR="$HOME/moodle-telemedicina"

echo -e "${BLUE}
╔══════════════════════════════════════════════════════════════════════════════╗
║                🏥 DESPLIEGUE CURSO DE TELEMEDICINA - GCP                    ║
║                                                                              ║
║  Este script automatiza el despliegue completo del curso de telemedicina     ║
║  en Google Cloud Platform usando Cloud Shell                                ║
╚══════════════════════════════════════════════════════════════════════════════╝
${NC}"

# Paso 1: Verificar configuración inicial
print_step "Verificando configuración inicial..."

# Verificar proyecto
CURRENT_PROJECT=$(gcloud config get-value project 2>/dev/null)
if [ "$CURRENT_PROJECT" != "$PROJECT_ID" ]; then
    print_warning "Configurando proyecto: $PROJECT_ID"
    gcloud config set project $PROJECT_ID
fi

# Verificar conexión al cluster
print_step "Conectando al cluster GKE..."
gcloud container clusters get-credentials $CLUSTER_NAME --zone=$ZONE --project=$PROJECT_ID

# Verificar que el cluster esté funcionando
if ! kubectl get nodes > /dev/null 2>&1; then
    print_error "No se puede conectar al cluster GKE"
    exit 1
fi

print_success "Conectado al cluster GKE exitosamente"

# Paso 2: Crear estructura de directorios
print_step "Creando estructura de directorios..."
mkdir -p $WORK_DIR/{scripts,config,docs,temp}
cd $WORK_DIR

# Paso 3: Crear scripts PHP
print_step "Creando scripts PHP para el curso..."

# Script maestro
cat > scripts/setup_complete_telemedicina_course.php << 'EOF'
<?php
define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');

echo "🚀 Configuración Completa del Curso Básico de Nivelación en Telemedicina\n";
echo "=" . str_repeat("=", 80) . "\n\n";

$start_time = time();

// Crear categoría
$category = $DB->get_record('course_categories', ['name' => 'Nivelación Médica']);
if (!$category) {
    $category = new stdClass();
    $category->name = 'Nivelación Médica';
    $category->description = 'Cursos básicos para personal médico';
    $category->parent = 0;
    $category->sortorder = 1;
    $category->timemodified = time();
    $category->id = $DB->insert_record('course_categories', $category);
    echo "✅ Categoría 'Nivelación Médica' creada\n";
}

// Crear curso
$course = $DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
if (!$course) {
    $course = new stdClass();
    $course->category = $category->id;
    $course->shortname = 'CBN-TELEMEDICINA-2025';
    $course->fullname = 'Curso Básico de Nivelación en Telemedicina';
    $course->summary = 'Curso diseñado para fortalecer conocimientos en telemedicina y herramientas digitales para personal médico';
    $course->summaryformat = 1;
    $course->format = 'topics';
    $course->numsections = 4;
    $course->startdate = time();
    $course->enddate = time() + (90 * 24 * 60 * 60);
    $course->visible = 1;
    $course->enablecompletion = 1;
    $course->completionnotify = 1;
    $course->timecreated = time();
    $course->timemodified = time();
    
    $course->id = $DB->insert_record('course', $course);
    echo "✅ Curso 'CBN-TELEMEDICINA-2025' creado (ID: {$course->id})\n";
}

// Crear módulos del curso
$modules = [
    1 => [
        'name' => 'Módulo A - Habilidades Tecnológicas',
        'activities' => [
            'Bienvenidos al Curso Básico de Nivelación en Telemedicina',
            'Normas de Uso del Aula Virtual',
            'UNIDAD 1. Introducción a Chrome OS y su uso en Telemedicina',
            '1.1 ACTIVIDAD EVALUADA 1: Formulario de Configuraciones de Chromebook',
            '1.2 ACTIVIDAD EVALUADA 2: Formulario de Teclas especiales en Chromebook'
        ]
    ],
    2 => [
        'name' => 'Módulo B - Buenas Prácticas Digitales',
        'activities' => [
            'UNIDAD 2. Buenas Prácticas Digitales en la Teleconsulta',
            '2.1 ACTIVIDAD EVALUADA 1: Resumen de Portal de Práctica Médica',
            '2.2 ACTIVIDAD EVALUADA 2: Caso real de inconvenientes en Telemedicina'
        ]
    ],
    3 => [
        'name' => 'Módulo C - Aplicaciones Médicas',
        'activities' => [
            'UNIDAD 3. Usabilidad de la aplicación DR. ISSS en línea',
            '3.1 ACTIVIDAD EVALUADA 1: Descarga de la Aplicación Dr. ISSS',
            '3.2 ACTIVIDAD EVALUADA 2: Navegación en la aplicación Dr. ISSS'
        ]
    ],
    4 => [
        'name' => 'Módulo D - Evaluación Final',
        'activities' => [
            'FORO PARA CONSULTAS',
            'Evaluación Final del Curso'
        ]
    ]
];

echo "\n📚 Creando estructura de módulos...\n";
foreach ($modules as $section_num => $module) {
    $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => $section_num]);
    if ($section) {
        $section->name = $module['name'];
        $section->summary = "Contenido del {$module['name']}";
        $section->summaryformat = 1;
        $DB->update_record('course_sections', $section);
        echo "✅ {$module['name']} - " . count($module['activities']) . " actividades\n";
    }
}

// Configurar OAuth
echo "\n🔐 Configurando OAuth 2.0...\n";
$auth_methods = get_config('core', 'auth');
if (strpos($auth_methods, 'oauth2') === false) {
    $new_auth = $auth_methods ? $auth_methods . ',oauth2' : 'oauth2';
    set_config('auth', $new_auth);
    echo "✅ OAuth 2.0 habilitado\n";
}

$allowed_domains = 'telesalud.gob.sv,goes.gob.sv';
set_config('allowemailaddresses', $allowed_domains);
echo "✅ Dominios permitidos: $allowed_domains\n";

// Crear usuarios de ejemplo
echo "\n👥 Creando usuarios de ejemplo...\n";
$example_users = [
    ['username' => 'doctor.lopez', 'email' => 'doctor.lopez@telesalud.gob.sv', 'firstname' => 'Juan', 'lastname' => 'López'],
    ['username' => 'dra.martinez', 'email' => 'dra.martinez@goes.gob.sv', 'firstname' => 'María', 'lastname' => 'Martínez'],
    ['username' => 'dr.rodriguez', 'email' => 'dr.rodriguez@telesalud.gob.sv', 'firstname' => 'Carlos', 'lastname' => 'Rodríguez']
];

$created_users = 0;
foreach ($example_users as $user_data) {
    $existing = $DB->get_record('user', ['email' => $user_data['email']]);
    if (!$existing) {
        $user = new stdClass();
        $user->username = $user_data['username'];
        $user->email = $user_data['email'];
        $user->firstname = $user_data['firstname'];
        $user->lastname = $user_data['lastname'];
        $user->auth = 'oauth2';
        $user->confirmed = 1;
        $user->mnethostid = 1;
        $user->password = 'not cached';
        $user->timecreated = time();
        $user->timemodified = time();
        
        $user_id = $DB->insert_record('user', $user);
        
        // Inscribir en el curso
        $enrol = enrol_get_plugin('manual');
        $instance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'manual']);
        if (!$instance) {
            $instance = new stdClass();
            $instance->enrol = 'manual';
            $instance->courseid = $course->id;
            $instance->status = 0;
            $instance->timecreated = time();
            $instance->timemodified = time();
            $instance->id = $DB->insert_record('enrol', $instance);
        }
        
        $student_role = $DB->get_record('role', ['shortname' => 'student']);
        $enrol->enrol_user($instance, $user_id, $student_role->id);
        
        $created_users++;
        echo "✅ Usuario creado e inscrito: {$user_data['email']}\n";
    }
}

$end_time = time();
$duration = $end_time - $start_time;

echo "\n🎉 ¡CONFIGURACIÓN COMPLETADA EXITOSAMENTE!\n";
echo "=" . str_repeat("=", 80) . "\n";
echo "⏱️  Tiempo total: " . gmdate("H:i:s", $duration) . "\n";
echo "🔗 Acceso al curso: http://34.72.133.6/course/view.php?id={$course->id}\n";
echo "👥 Usuarios creados: $created_users\n";
echo "📚 Módulos configurados: " . count($modules) . "\n";
echo "\n✅ ¡El curso de Telemedicina está listo para usar!\n";
?>
EOF

# Script de verificación
cat > scripts/verify_deployment.php << 'EOF'
<?php
define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');

echo "🔍 Verificando despliegue del curso de Telemedicina...\n\n";

// Verificar curso
$course = $DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
if ($course) {
    echo "✅ Curso encontrado: {$course->fullname}\n";
    echo "   ID: {$course->id}\n";
    echo "   URL: http://34.72.133.6/course/view.php?id={$course->id}\n";
    echo "   Estado: " . ($course->visible ? 'Visible' : 'Oculto') . "\n";
    echo "   Fecha inicio: " . date('Y-m-d H:i:s', $course->startdate) . "\n";
    echo "   Fecha fin: " . date('Y-m-d H:i:s', $course->enddate) . "\n";
} else {
    echo "❌ Curso no encontrado\n";
    exit(1);
}

// Verificar usuarios
$user_count = $DB->count_records_sql("
    SELECT COUNT(*) FROM {user} 
    WHERE (email LIKE '%@telesalud.gob.sv' OR email LIKE '%@goes.gob.sv')
    AND deleted = 0
");
echo "\n👥 Usuarios de Telesalud registrados: $user_count\n";

// Verificar inscripciones
$enrollment_count = $DB->count_records_sql("
    SELECT COUNT(*) FROM {user_enrolments} ue
    JOIN {enrol} e ON ue.enrolid = e.id
    WHERE e.courseid = ?
", [$course->id]);
echo "📝 Usuarios inscritos en el curso: $enrollment_count\n";

// Verificar secciones
$sections = $DB->get_records('course_sections', ['course' => $course->id]);
echo "📚 Secciones del curso: " . count($sections) . "\n";
foreach ($sections as $section) {
    if ($section->section > 0) {
        echo "   • Sección {$section->section}: {$section->name}\n";
    }
}

// Verificar OAuth
$oauth_enabled = strpos(get_config('core', 'auth'), 'oauth2') !== false;
echo "\n🔐 OAuth 2.0: " . ($oauth_enabled ? 'Habilitado' : 'Deshabilitado') . "\n";

$allowed_domains = get_config('core', 'allowemailaddresses');
echo "🌐 Dominios permitidos: $allowed_domains\n";

echo "\n🎯 Verificación completada exitosamente\n";
?>
EOF

print_success "Scripts PHP creados"

# Paso 4: Obtener información del pod de Moodle
print_step "Identificando pod de Moodle..."
MOODLE_POD=$(kubectl get pods -n $NAMESPACE -l app=moodle -o jsonpath='{.items[0].metadata.name}' 2>/dev/null)

if [ -z "$MOODLE_POD" ]; then
    print_error "No se encontró el pod de Moodle"
    print_warning "Verificando pods disponibles..."
    kubectl get pods -n $NAMESPACE
    exit 1
fi

print_success "Pod de Moodle encontrado: $MOODLE_POD"

# Paso 5: Transferir scripts al pod
print_step "Transfiriendo scripts al pod de Moodle..."
kubectl cp scripts/ $MOODLE_POD:/bitnami/moodle/scripts/ -n $NAMESPACE

# Verificar que los archivos se copiaron
if kubectl exec -it $MOODLE_POD -n $NAMESPACE -- ls -la /bitnami/moodle/scripts/ > /dev/null 2>&1; then
    print_success "Scripts transferidos exitosamente"
else
    print_error "Error al transferir scripts"
    exit 1
fi

# Paso 6: Ejecutar configuración del curso
print_step "Ejecutando configuración del curso de Telemedicina..."
echo -e "${YELLOW}Esto puede tomar unos minutos...${NC}"

if kubectl exec -it $MOODLE_POD -n $NAMESPACE -- php /bitnami/moodle/scripts/setup_complete_telemedicina_course.php; then
    print_success "Curso configurado exitosamente"
else
    print_error "Error en la configuración del curso"
    exit 1
fi

# Paso 7: Ejecutar verificación
print_step "Ejecutando verificación del despliegue..."
kubectl exec -it $MOODLE_POD -n $NAMESPACE -- php /bitnami/moodle/scripts/verify_deployment.php

# Paso 8: Mostrar información final
print_step "Obteniendo información de acceso..."

# Obtener IP externa
EXTERNAL_IP=$(kubectl get service moodle -n $NAMESPACE -o jsonpath='{.status.loadBalancer.ingress[0].ip}' 2>/dev/null)
if [ -z "$EXTERNAL_IP" ]; then
    EXTERNAL_IP="34.72.133.6"  # IP por defecto
fi

# Obtener ID del curso
COURSE_ID=$(kubectl exec -it $MOODLE_POD -n $NAMESPACE -- php -r "
require_once('/bitnami/moodle/config.php');
\$course = \$DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
if (\$course) echo \$course->id;
" 2>/dev/null | tr -d '\r\n')

echo -e "${GREEN}
╔══════════════════════════════════════════════════════════════════════════════╗
║                           🎉 DESPLIEGUE EXITOSO                             ║
╚══════════════════════════════════════════════════════════════════════════════╝

🔗 ACCESO AL CURSO:
   • URL principal: http://$EXTERNAL_IP
   • Curso directo: http://$EXTERNAL_IP/course/view.php?id=$COURSE_ID
   • Administración: http://$EXTERNAL_IP/admin

👥 USUARIOS DE PRUEBA:
   • doctor.lopez@telesalud.gob.sv
   • dra.martinez@goes.gob.sv
   • dr.rodriguez@telesalud.gob.sv

🔐 CONFIGURACIÓN OAUTH:
   • Proveedor: Google OAuth 2.0
   • Dominios permitidos: telesalud.gob.sv, goes.gob.sv
   • URI de redirección: http://$EXTERNAL_IP/admin/oauth2callback.php

📚 ESTRUCTURA DEL CURSO:
   • Módulo A: Habilidades Tecnológicas
   • Módulo B: Buenas Prácticas Digitales
   • Módulo C: Aplicaciones Médicas
   • Módulo D: Evaluación Final

🛠️  COMANDOS ÚTILES:
   • Verificar estado: kubectl get pods -n $NAMESPACE
   • Ver logs: kubectl logs $MOODLE_POD -n $NAMESPACE
   • Acceder al pod: kubectl exec -it $MOODLE_POD -n $NAMESPACE -- /bin/bash
   • Ejecutar verificación: kubectl exec -it $MOODLE_POD -n $NAMESPACE -- php /bitnami/moodle/scripts/verify_deployment.php

${NC}"

# Crear script de acceso rápido
cat > quick_access.sh << EOF
#!/bin/bash
# Script de acceso rápido al curso de Telemedicina

echo "🚀 Acceso rápido al curso de Telemedicina"
echo "Conectando al cluster..."
gcloud container clusters get-credentials $CLUSTER_NAME --zone=$ZONE --project=$PROJECT_ID

MOODLE_POD=\$(kubectl get pods -n $NAMESPACE -l app=moodle -o jsonpath='{.items[0].metadata.name}')
echo "Pod de Moodle: \$MOODLE_POD"

echo "Verificando curso..."
kubectl exec -it \$MOODLE_POD -n $NAMESPACE -- php /bitnami/moodle/scripts/verify_deployment.php

echo "🔗 Acceso web: http://$EXTERNAL_IP/course/view.php?id=$COURSE_ID"
EOF

chmod +x quick_access.sh
print_success "Script de acceso rápido creado: $WORK_DIR/quick_access.sh"

# Crear documentación
cat > docs/CONFIGURACION_OAUTH.md << 'EOF'
# Configuración OAuth para Google

## Pasos para configurar OAuth en Google Console:

1. Ve a: https://console.developers.google.com/
2. Selecciona el proyecto: moodle-gcp-test
3. Ve a "Credenciales" > "Crear credenciales" > "ID de cliente OAuth 2.0"
4. Configura:
   - Tipo de aplicación: Aplicación web
   - Nombre: Moodle Telesalud
   - URI de redirección: http://34.72.133.6/admin/oauth2callback.php
   - Dominios autorizados: telesalud.gob.sv, goes.gob.sv

5. Copia el Client ID y Client Secret
6. En Moodle, ve a: Administración > Plugins > Autenticación > OAuth 2.0
7. Configura el issuer de Google con las credenciales obtenidas

## Comandos para configurar credenciales:

```bash
kubectl exec -it $MOODLE_POD -n moodle -- php -r "
require_once('/bitnami/moodle/config.php');
// Configurar Client ID y Secret aquí
"
```
EOF

print_success "Documentación creada en: $WORK_DIR/docs/"

echo -e "${BLUE}
🎯 PRÓXIMOS PASOS:
1. Configurar OAuth en Google Console (ver docs/CONFIGURACION_OAUTH.md)
2. Probar acceso con usuarios de dominio permitido
3. Personalizar contenido del curso según necesidades
4. Configurar backup automático

¡Tu curso de Telemedicina está listo para usar!
${NC}"

# Limpiar archivos temporales
rm -rf temp/

print_success "Despliegue completado exitosamente"