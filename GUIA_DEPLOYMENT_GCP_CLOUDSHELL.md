# 🚀 Guía Completa: Despliegue de Cursos de Moodle en GCP Cloud Shell

## 📋 Análisis de Cursos Existentes

### Curso Principal Identificado
- **Nombre**: Curso Básico de Nivelación en Telemedicina
- **Código**: CBN-TELEMEDICINA-2025
- **Modalidad**: Virtual con enfoque en telemedicina
- **Duración**: 90 días
- **Usuarios objetivo**: Personal médico con dominios @telesalud.gob.sv y @goes.gob.sv

### Estructura del Curso
```
📚 Módulo A - Habilidades Tecnológicas (5 actividades)
├── Bienvenida y normas
├── Introducción a Chrome OS
├── Configuración de Chromebook
├── Google Chat y Meet
└── Organización en Google Drive

📚 Módulo B - Buenas Prácticas Digitales (2 actividades)
├── Portal de Práctica Médica
└── Casos reales de inconvenientes

📚 Módulo C - Aplicaciones Médicas (2 actividades)
├── Descarga de App Dr. ISSS
└── Navegación en la aplicación

📚 Módulo D - Evaluación Final (2 actividades)
├── Foro para consultas
└── Evaluación integral
```

### Características Técnicas
- **Acceso progresivo**: Cada actividad depende de la anterior
- **Sistema de insignias**: 4 insignias por módulo completado
- **Restricciones de dominio**: Solo emails @telesalud.gob.sv y @goes.gob.sv
- **Autenticación**: OAuth 2.0 con Google
- **Base de datos**: MySQL/MariaDB
- **Infraestructura**: GKE (Google Kubernetes Engine)

---

## 🛠️ Preparación del Entorno Cloud Shell

### 1. Activar Cloud Shell
```bash
# Abrir Cloud Shell en Google Cloud Console
# URL: https://console.cloud.google.com/

# Verificar proyecto actual
gcloud config get-value project

# Configurar proyecto si es necesario
gcloud config set project moodle-gcp-test
```

### 2. Conectar al Cluster GKE Existente
```bash
# Conectar al cluster
gcloud container clusters get-credentials moodle-cluster \
  --zone=us-central1-c \
  --project=moodle-gcp-test

# Verificar conexión
kubectl get nodes
kubectl get pods --all-namespaces
```

### 3. Crear Estructura de Directorios
```bash
# Crear estructura para los scripts
mkdir -p ~/moodle-telemedicina/{scripts,config,docs}
cd ~/moodle-telemedicina

# Crear directorios específicos
mkdir -p scripts/{course,oauth,users,content}
mkdir -p config/{mysql,oauth,courses}
mkdir -p docs/{guides,troubleshooting}
```

---

## 📁 Transferencia de Archivos sin GitHub

### Opción 1: Usar Cloud Shell Editor
```bash
# Abrir editor en Cloud Shell
cloudshell edit

# Crear archivos manualmente copiando el contenido
# Estructura sugerida:
# ~/moodle-telemedicina/
# ├── scripts/
# │   ├── setup_complete_telemedicina_course.php
# │   ├── create_telemedicina_course_structure.php
# │   ├── add_detailed_content_telemedicina.php
# │   ├── enroll_users_telemedicina.php
# │   ├── setup_google_oauth_telesalud.php
# │   └── verify_oauth_setup.php
# └── config/
#     └── deployment_config.yaml
```

### Opción 2: Usar gsutil para transferir archivos
```bash
# Si tienes los archivos en Cloud Storage
gsutil cp gs://tu-bucket/moodle-scripts/* ~/moodle-telemedicina/scripts/

# Crear bucket temporal si es necesario
gsutil mb gs://moodle-temp-transfer-$(date +%Y%m%d)
```

### Opción 3: Copiar archivos directamente
```bash
# Crear cada script individualmente
cat > ~/moodle-telemedicina/scripts/setup_complete_telemedicina_course.php << 'EOF'
<?php
/**
 * Script maestro para configurar completamente el Curso de Telemedicina
 * Ejecuta todo el proceso de configuración en secuencia
 */

define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');

echo "🚀 Configuración Completa del Curso Básico de Nivelación en Telemedicina\n";
echo "=" . str_repeat("=", 80) . "\n\n";

$start_time = time();

// Paso 1: Crear estructura del curso
echo "📚 PASO 1: Creando estructura del curso...\n";
echo "-" . str_repeat("-", 50) . "\n";
include 'create_telemedicina_course_structure.php';
echo "\n";

// Paso 2: Agregar contenido detallado
echo "📝 PASO 2: Agregando contenido detallado...\n";
echo "-" . str_repeat("-", 50) . "\n";
include 'add_detailed_content_telemedicina.php';
echo "\n";

// Paso 3: Inscribir usuarios
echo "👥 PASO 3: Inscribiendo usuarios...\n";
echo "-" . str_repeat("-", 50) . "\n";
include 'enroll_users_telemedicina.php';
echo "\n";

$end_time = time();
$duration = $end_time - $start_time;

echo "🎉 CONFIGURACIÓN COMPLETADA EXITOSAMENTE\n";
echo "=" . str_repeat("=", 80) . "\n";
echo "⏱️  Tiempo total: " . gmdate("H:i:s", $duration) . "\n";

// Buscar el curso para mostrar información final
$course = $DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
if ($course) {
    echo "🔗 Acceso al curso: http://34.72.133.6/course/view.php?id={$course->id}\n";
}

echo "\n✅ ¡El curso está listo para usar!\n";
?>
EOF
```

---

## 🔧 Scripts de Configuración Automática

### 1. Script de Estructura del Curso
```bash
cat > ~/moodle-telemedicina/scripts/create_telemedicina_course_structure.php << 'EOF'
<?php
/**
 * Crear estructura del Curso Básico de Nivelación en Telemedicina
 */

define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');

echo "🏥 Creando Curso Básico de Nivelación en Telemedicina...\n";

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
    $course->enddate = time() + (90 * 24 * 60 * 60); // 90 días
    $course->visible = 1;
    $course->enablecompletion = 1;
    $course->completionnotify = 1;
    $course->timecreated = time();
    $course->timemodified = time();
    
    $course->id = $DB->insert_record('course', $course);
    echo "✅ Curso 'CBN-TELEMEDICINA-2025' creado\n";
}

echo "🎯 Estructura del curso creada exitosamente\n";
echo "🔗 Acceso: http://34.72.133.6/course/view.php?id={$course->id}\n";
?>
EOF
```

### 2. Script de Configuración OAuth
```bash
cat > ~/moodle-telemedicina/scripts/setup_google_oauth_telesalud.php << 'EOF'
<?php
/**
 * Configuración OAuth 2.0 Google específica para Telesalud
 */

define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');

echo "🔧 Configurando OAuth 2.0 Google para Telesalud...\n";

// Habilitar OAuth 2.0
$auth_methods = get_config('core', 'auth');
if (strpos($auth_methods, 'oauth2') === false) {
    $new_auth = $auth_methods ? $auth_methods . ',oauth2' : 'oauth2';
    set_config('auth', $new_auth);
    echo "✅ Autenticación OAuth 2.0 habilitada\n";
}

// Configurar dominios permitidos
$allowed_domains = 'telesalud.gob.sv,goes.gob.sv';
set_config('auth_oauth2/alloweddomains', $allowed_domains);
set_config('allowemailaddresses', $allowed_domains);

echo "✅ Dominios permitidos configurados: $allowed_domains\n";

// Crear issuer de Google
$google_issuer = new stdClass();
$google_issuer->name = 'Google Telesalud';
$google_issuer->image = 'https://developers.google.com/identity/images/g-logo.png';
$google_issuer->baseurl = 'https://accounts.google.com';
$google_issuer->enabled = 1;
$google_issuer->showonloginpage = 1;
$google_issuer->requireconfirmation = 0;
$google_issuer->timecreated = time();
$google_issuer->timemodified = time();

$existing_issuer = $DB->get_record('oauth2_issuer', ['name' => 'Google Telesalud']);
if ($existing_issuer) {
    $google_issuer->id = $existing_issuer->id;
    $DB->update_record('oauth2_issuer', $google_issuer);
    $issuer_id = $existing_issuer->id;
} else {
    $issuer_id = $DB->insert_record('oauth2_issuer', $google_issuer);
}

echo "✅ Issuer Google Telesalud configurado\n";
echo "🔑 Configurar Client ID y Secret en Google Console\n";
echo "   URI de redirección: http://34.72.133.6/admin/oauth2callback.php\n";
?>
EOF
```

---

## 🚀 Proceso de Despliegue

### 1. Verificar Estado del Cluster
```bash
# Verificar que el cluster esté funcionando
kubectl get pods -n moodle
kubectl get services -n moodle

# Verificar acceso a Moodle
curl -I http://34.72.133.6
```

### 2. Acceder al Pod de Moodle
```bash
# Identificar el pod de Moodle
MOODLE_POD=$(kubectl get pods -n moodle -l app=moodle -o jsonpath='{.items[0].metadata.name}')
echo "Pod de Moodle: $MOODLE_POD"

# Acceder al pod
kubectl exec -it $MOODLE_POD -n moodle -- /bin/bash
```

### 3. Transferir Scripts al Pod
```bash
# Desde Cloud Shell, copiar scripts al pod
kubectl cp ~/moodle-telemedicina/scripts/ $MOODLE_POD:/bitnami/moodle/ -n moodle

# Verificar que los archivos se copiaron
kubectl exec -it $MOODLE_POD -n moodle -- ls -la /bitnami/moodle/scripts/
```

### 4. Ejecutar Scripts en el Pod
```bash
# Dentro del pod de Moodle
kubectl exec -it $MOODLE_POD -n moodle -- /bin/bash

# Navegar al directorio de Moodle
cd /bitnami/moodle/

# Ejecutar configuración completa
php scripts/setup_complete_telemedicina_course.php

# O ejecutar paso a paso:
# php scripts/create_telemedicina_course_structure.php
# php scripts/add_detailed_content_telemedicina.php
# php scripts/setup_google_oauth_telesalud.php
# php scripts/enroll_users_telemedicina.php
```

---

## 🔐 Configuración OAuth en Google Console

### 1. Crear Credenciales OAuth 2.0
```bash
# Desde Cloud Shell, obtener la configuración necesaria
echo "🔗 Configuración OAuth requerida:"
echo "   Proyecto: moodle-gcp-test"
echo "   URI de redirección: http://34.72.133.6/admin/oauth2callback.php"
echo "   Dominios autorizados: telesalud.gob.sv, goes.gob.sv"
```

### 2. Configurar Credenciales en Moodle
```bash
# Crear script para establecer credenciales
cat > ~/moodle-telemedicina/scripts/set_oauth_credentials.php << 'EOF'
<?php
define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');

// Configurar Client ID y Secret (reemplazar con valores reales)
$client_id = 'TU_CLIENT_ID_AQUI';
$client_secret = 'TU_CLIENT_SECRET_AQUI';

// Buscar el issuer de Google
$issuer = $DB->get_record('oauth2_issuer', ['name' => 'Google Telesalud']);
if ($issuer) {
    // Configurar Client ID
    $client_id_config = new stdClass();
    $client_id_config->issuerid = $issuer->id;
    $client_id_config->name = 'clientid';
    $client_id_config->value = $client_id;
    
    // Configurar Client Secret
    $client_secret_config = new stdClass();
    $client_secret_config->issuerid = $issuer->id;
    $client_secret_config->name = 'clientsecret';
    $client_secret_config->value = $client_secret;
    
    // Limpiar configuraciones existentes
    $DB->delete_records('oauth2_issuer_config', ['issuerid' => $issuer->id]);
    
    // Insertar nuevas configuraciones
    $DB->insert_record('oauth2_issuer_config', $client_id_config);
    $DB->insert_record('oauth2_issuer_config', $client_secret_config);
    
    echo "✅ Credenciales OAuth configuradas exitosamente\n";
}
?>
EOF

# Ejecutar en el pod
kubectl cp ~/moodle-telemedicina/scripts/set_oauth_credentials.php $MOODLE_POD:/bitnami/moodle/ -n moodle
kubectl exec -it $MOODLE_POD -n moodle -- php /bitnami/moodle/set_oauth_credentials.php
```

---

## 👥 Creación de Usuarios

### 1. Script para Crear Usuarios Específicos
```bash
cat > ~/moodle-telemedicina/scripts/create_users_telesalud.php << 'EOF'
<?php
define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');

echo "👥 Creando usuarios específicos para Telesalud...\n";

// Usuarios de ejemplo (reemplazar con usuarios reales)
$users = [
    [
        'username' => 'doctor.lopez',
        'email' => 'doctor.lopez@telesalud.gob.sv',
        'firstname' => 'Juan',
        'lastname' => 'López',
        'auth' => 'oauth2'
    ],
    [
        'username' => 'dra.martinez',
        'email' => 'dra.martinez@goes.gob.sv',
        'firstname' => 'María',
        'lastname' => 'Martínez',
        'auth' => 'oauth2'
    ]
];

foreach ($users as $user_data) {
    $existing_user = $DB->get_record('user', ['email' => $user_data['email']]);
    if (!$existing_user) {
        $user = new stdClass();
        $user->username = $user_data['username'];
        $user->email = $user_data['email'];
        $user->firstname = $user_data['firstname'];
        $user->lastname = $user_data['lastname'];
        $user->auth = $user_data['auth'];
        $user->confirmed = 1;
        $user->mnethostid = 1;
        $user->timecreated = time();
        $user->timemodified = time();
        
        $user_id = $DB->insert_record('user', $user);
        echo "✅ Usuario creado: {$user_data['email']}\n";
    } else {
        echo "⚠️  Usuario ya existe: {$user_data['email']}\n";
    }
}
?>
EOF
```

### 2. Script para Inscribir Usuarios
```bash
cat > ~/moodle-telemedicina/scripts/enroll_users_telemedicina.php << 'EOF'
<?php
define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/enrollib.php');

echo "📝 Inscribiendo usuarios en el curso de Telemedicina...\n";

// Buscar el curso
$course = $DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
if (!$course) {
    echo "❌ Error: Curso no encontrado\n";
    exit(1);
}

// Buscar usuarios con dominios permitidos
$users = $DB->get_records_sql("
    SELECT * FROM {user} 
    WHERE (email LIKE '%@telesalud.gob.sv' OR email LIKE '%@goes.gob.sv')
    AND deleted = 0
");

$enrol_plugin = enrol_get_plugin('manual');
$instance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'manual']);
$student_role = $DB->get_record('role', ['shortname' => 'student']);

$enrolled_count = 0;
foreach ($users as $user) {
    if (!$DB->record_exists('user_enrolments', ['userid' => $user->id, 'enrolid' => $instance->id])) {
        $enrol_plugin->enrol_user($instance, $user->id, $student_role->id);
        $enrolled_count++;
        echo "✅ Usuario inscrito: {$user->email}\n";
    }
}

echo "📊 Total usuarios inscritos: $enrolled_count\n";
?>
EOF
```

---

## 📊 Monitoreo y Verificación

### 1. Script de Verificación
```bash
cat > ~/moodle-telemedicina/scripts/verify_deployment.php << 'EOF'
<?php
define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');

echo "🔍 Verificando despliegue del curso...\n\n";

// Verificar curso
$course = $DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
if ($course) {
    echo "✅ Curso encontrado: {$course->fullname}\n";
    echo "   ID: {$course->id}\n";
    echo "   URL: http://34.72.133.6/course/view.php?id={$course->id}\n";
} else {
    echo "❌ Curso no encontrado\n";
}

// Verificar OAuth
$oauth_issuer = $DB->get_record('oauth2_issuer', ['name' => 'Google Telesalud']);
if ($oauth_issuer) {
    echo "✅ OAuth configurado: {$oauth_issuer->name}\n";
    echo "   Habilitado: " . ($oauth_issuer->enabled ? 'Sí' : 'No') . "\n";
} else {
    echo "❌ OAuth no configurado\n";
}

// Verificar usuarios
$user_count = $DB->count_records_sql("
    SELECT COUNT(*) FROM {user} 
    WHERE (email LIKE '%@telesalud.gob.sv' OR email LIKE '%@goes.gob.sv')
    AND deleted = 0
");
echo "👥 Usuarios de Telesalud: $user_count\n";

// Verificar inscripciones
if ($course) {
    $enrollment_count = $DB->count_records_sql("
        SELECT COUNT(*) FROM {user_enrolments} ue
        JOIN {enrol} e ON ue.enrolid = e.id
        WHERE e.courseid = ?
    ", [$course->id]);
    echo "📝 Usuarios inscritos: $enrollment_count\n";
}

echo "\n🎯 Verificación completada\n";
?>
EOF
```

### 2. Comandos de Monitoreo
```bash
# Verificar estado de pods
kubectl get pods -n moodle -o wide

# Verificar logs
kubectl logs -f $MOODLE_POD -n moodle

# Verificar recursos
kubectl top pods -n moodle

# Verificar servicios
kubectl get services -n moodle
```

---

## 🔄 Comandos de Mantenimiento

### 1. Backup del Curso
```bash
# Backup de base de datos
kubectl exec -it moodle-mariadb-0 -n moodle -- mysqldump -u root -p moodle > backup_telemedicina_$(date +%Y%m%d).sql

# Backup de archivos
kubectl exec -it $MOODLE_POD -n moodle -- tar -czf /tmp/course_backup.tar.gz /bitnami/moodle/course/
kubectl cp $MOODLE_POD:/tmp/course_backup.tar.gz ./course_backup_$(date +%Y%m%d).tar.gz -n moodle
```

### 2. Actualización del Curso
```bash
# Actualizar contenido
kubectl exec -it $MOODLE_POD -n moodle -- php /bitnami/moodle/scripts/add_detailed_content_telemedicina.php

# Limpiar caché
kubectl exec -it $MOODLE_POD -n moodle -- php /bitnami/moodle/admin/cli/purge_caches.php
```

---

## 🚨 Troubleshooting

### Problemas Comunes y Soluciones

1. **Error de conexión a la base de datos**
   ```bash
   kubectl exec -it $MOODLE_POD -n moodle -- php -r "require_once('/bitnami/moodle/config.php'); echo 'DB OK\n';"
   ```

2. **Problemas con OAuth**
   ```bash
   kubectl exec -it $MOODLE_POD -n moodle -- php /bitnami/moodle/scripts/verify_deployment.php
   ```

3. **Usuarios no pueden acceder**
   ```bash
   # Verificar dominios permitidos
   kubectl exec -it $MOODLE_POD -n moodle -- php -r "require_once('/bitnami/moodle/config.php'); echo get_config('core', 'allowemailaddresses');"
   ```

---

## ✅ Checklist de Despliegue

- [ ] Cluster GKE conectado
- [ ] Scripts transferidos al pod
- [ ] Estructura del curso creada
- [ ] OAuth configurado
- [ ] Usuarios creados e inscritos
- [ ] Contenido detallado agregado
- [ ] Verificación exitosa
- [ ] Acceso web funcionando
- [ ] Backup configurado

---

## 🔗 URLs Importantes

- **Moodle**: http://34.72.133.6
- **Curso**: http://34.72.133.6/course/view.php?id={COURSE_ID}
- **Admin**: http://34.72.133.6/admin
- **OAuth Callback**: http://34.72.133.6/admin/oauth2callback.php

---

**¡Tu curso de Telemedicina estará listo para usar siguiendo esta guía!** 🏥✨