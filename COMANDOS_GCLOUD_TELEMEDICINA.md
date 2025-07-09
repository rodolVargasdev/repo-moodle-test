# 🚀 Comandos GCloud para Curso de Telemedicina

## 📋 Guía Completa de Comandos para GCP

Esta guía contiene todos los comandos necesarios para configurar y gestionar el **Curso Básico de Nivelación en Telemedicina** en Google Cloud Platform.

---

## 🔧 CONFIGURACIÓN INICIAL

### 1. Conectar al cluster GKE

```bash
# Conectar al cluster de Moodle
gcloud container clusters get-credentials moodle-cluster \
  --zone=us-central1-c \
  --project=moodle-gcp-test

# Verificar conexión
kubectl get pods

# Verificar estado del cluster
kubectl cluster-info
```

### 2. Acceder al pod de Moodle

```bash
# Listar pods de Moodle
kubectl get pods -l app=moodle

# Acceder al pod principal
kubectl exec -it moodle-684db8486b-5c5zp -- /bin/bash

# Navegar al directorio de Moodle
cd /bitnami/moodle/

# Verificar que estamos en el directorio correcto
pwd
ls -la
```

---

## 📚 CONFIGURACIÓN DEL CURSO

### 3. Ejecutar scripts de configuración

```bash
# === OPCIÓN 1: Configuración completa automática ===
php setup_complete_telemedicina_course.php

# === OPCIÓN 2: Configuración paso a paso ===

# Paso 1: Crear estructura del curso
php create_telemedicina_course_structure.php

# Paso 2: Agregar contenido detallado
php add_detailed_content_telemedicina.php

# Paso 3: Inscribir usuarios
php enroll_users_telemedicina.php

# Verificar que los scripts se ejecutaron correctamente
echo "✅ Scripts ejecutados exitosamente"
```

### 4. Verificar la configuración

```bash
# Verificar que el curso se creó correctamente
kubectl exec -it moodle-684db8486b-5c5zp -- php -r "
require_once('/bitnami/moodle/config.php');
\$course = \$DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
echo 'Curso ID: ' . \$course->id . \"\n\";
echo 'Nombre: ' . \$course->fullname . \"\n\";
echo 'URL: http://34.72.133.6/course/view.php?id=' . \$course->id . \"\n\";
"

# Verificar usuarios inscritos
kubectl exec -it moodle-684db8486b-5c5zp -- php -r "
require_once('/bitnami/moodle/config.php');
\$count = \$DB->count_records_sql('
    SELECT COUNT(*) FROM {user_enrolments} ue 
    JOIN {enrol} e ON ue.enrolid = e.id 
    JOIN {course} c ON e.courseid = c.id 
    WHERE c.shortname = \"CBN-TELEMEDICINA-2025\"
');
echo 'Usuarios inscritos: ' . \$count . \"\n\";
"
```

---

## 🔐 CONFIGURACIÓN OAUTH (Si aún no está configurado)

### 5. Configurar OAuth 2.0 para dominios específicos

```bash
# Ejecutar configuración OAuth para telesalud.gob.sv y goes.gob.sv
php setup_google_oauth_telesalud.php

# Configurar credenciales (editar archivo primero)
nano set_google_credentials.php
# Reemplazar CLIENT_ID y CLIENT_SECRET con valores reales
php set_google_credentials.php

# Crear usuarios OAuth si no existen
php create_telesalud_users.php

# Verificar configuración OAuth
php verify_oauth_setup.php
```

---

## 📊 MONITOREO Y VERIFICACIÓN

### 6. Verificar estado del sistema

```bash
# Verificar estado de pods
kubectl get pods -l app=moodle
kubectl get pods -l app=mariadb

# Verificar recursos utilizados
kubectl top pods moodle-684db8486b-5c5zp
kubectl top pods moodle-mariadb-0

# Verificar logs
kubectl logs moodle-684db8486b-5c5zp --tail=50
kubectl logs moodle-mariadb-0 --tail=50

# Verificar configuración de red
kubectl get services
kubectl get ingress
```

### 7. Verificar base de datos

```bash
# Acceder a la base de datos
kubectl exec -it moodle-mariadb-0 -- mysql -u root -p moodle

# Dentro de MySQL, verificar tablas del curso:
USE moodle;
SHOW TABLES LIKE '%course%';

# Verificar curso específico
SELECT id, shortname, fullname FROM mdl_course WHERE shortname = 'CBN-TELEMEDICINA-2025';

# Verificar usuarios OAuth
SELECT COUNT(*) as oauth_users FROM mdl_user WHERE auth = 'oauth2';

# Verificar inscripciones
SELECT COUNT(*) as enrollments FROM mdl_user_enrolments ue 
JOIN mdl_enrol e ON ue.enrolid = e.id 
JOIN mdl_course c ON e.courseid = c.id 
WHERE c.shortname = 'CBN-TELEMEDICINA-2025';

# Salir de MySQL
EXIT;
```

---

## 🏥 GESTIÓN DE USUARIOS

### 8. Gestionar usuarios específicos

```bash
# Verificar usuarios por dominio
kubectl exec -it moodle-684db8486b-5c5zp -- php -r "
require_once('/bitnami/moodle/config.php');
\$telesalud = \$DB->count_records_sql('SELECT COUNT(*) FROM {user} WHERE email LIKE \"%@telesalud.gob.sv\"');
\$goes = \$DB->count_records_sql('SELECT COUNT(*) FROM {user} WHERE email LIKE \"%@goes.gob.sv\"');
echo 'Usuarios @telesalud.gob.sv: ' . \$telesalud . \"\n\";
echo 'Usuarios @goes.gob.sv: ' . \$goes . \"\n\";
"

# Listar usuarios específicos
kubectl exec -it moodle-684db8486b-5c5zp -- php -r "
require_once('/bitnami/moodle/config.php');
\$users = \$DB->get_records_sql('SELECT email, firstname, lastname FROM {user} WHERE email LIKE \"%@telesalud.gob.sv\" OR email LIKE \"%@goes.gob.sv\"');
foreach (\$users as \$user) {
    echo \$user->email . ' - ' . \$user->firstname . ' ' . \$user->lastname . \"\n\";
}
"

# Inscribir usuario específico manualmente
kubectl exec -it moodle-684db8486b-5c5zp -- php -r "
require_once('/bitnami/moodle/config.php');
require_once('/bitnami/moodle/lib/enrollib.php');
\$user = \$DB->get_record('user', ['email' => 'nuevo@telesalud.gob.sv']);
\$course = \$DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
if (\$user && \$course) {
    \$enrol = enrol_get_plugin('manual');
    \$instance = \$DB->get_record('enrol', ['courseid' => \$course->id, 'enrol' => 'manual']);
    \$studentrole = \$DB->get_record('role', ['shortname' => 'student']);
    \$enrol->enrol_user(\$instance, \$user->id, \$studentrole->id);
    echo 'Usuario inscrito exitosamente\n';
}
"
```

---

## 🔒 SEGURIDAD Y ACCESO

### 9. Configurar restricciones de acceso

```bash
# Verificar restricciones de acceso progresivo
kubectl exec -it moodle-684db8486b-5c5zp -- php -r "
require_once('/bitnami/moodle/config.php');
\$course = \$DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
\$activities = \$DB->get_records_sql('
    SELECT cm.id, cm.name, cm.availability 
    FROM {course_modules} cm 
    WHERE cm.course = ? AND cm.availability IS NOT NULL
', [\$course->id]);
echo 'Actividades con restricciones: ' . count(\$activities) . \"\n\";
"

# Verificar configuración OAuth
kubectl exec -it moodle-684db8486b-5c5zp -- php -r "
require_once('/bitnami/moodle/config.php');
\$oauth_enabled = get_config('core', 'auth');
echo 'Métodos de autenticación: ' . \$oauth_enabled . \"\n\";
\$allowed_domains = get_config('core', 'allowemailaddresses');
echo 'Dominios permitidos: ' . \$allowed_domains . \"\n\";
"

# Verificar issuer OAuth
kubectl exec -it moodle-684db8486b-5c5zp -- php -r "
require_once('/bitnami/moodle/config.php');
\$issuer = \$DB->get_record('oauth2_issuer', ['name' => 'Google Telesalud']);
if (\$issuer) {
    echo 'Issuer OAuth configurado: ' . \$issuer->name . \"\n\";
    echo 'Habilitado: ' . (\$issuer->enabled ? 'Sí' : 'No') . \"\n\";
}
"
```

---

## 📈 REPORTES Y ANALÍTICAS

### 10. Generar reportes del curso

```bash
# Estadísticas generales del curso
kubectl exec -it moodle-684db8486b-5c5zp -- php -r "
require_once('/bitnami/moodle/config.php');
\$course = \$DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);

// Contar actividades por tipo
\$activities = \$DB->get_records_sql('
    SELECT m.name as modulename, COUNT(*) as count
    FROM {course_modules} cm
    JOIN {modules} m ON cm.module = m.id
    WHERE cm.course = ?
    GROUP BY m.name
', [\$course->id]);

echo \"=== ESTADÍSTICAS DEL CURSO ===\n\";
echo \"Actividades por tipo:\n\";
foreach (\$activities as \$activity) {
    echo \"  • {\$activity->modulename}: {\$activity->count}\n\";
}

// Contar usuarios por rol
\$enrollments = \$DB->get_records_sql('
    SELECT r.shortname, COUNT(*) as count
    FROM {user_enrolments} ue
    JOIN {enrol} e ON ue.enrolid = e.id
    JOIN {role_assignments} ra ON ue.userid = ra.userid
    JOIN {role} r ON ra.roleid = r.id
    WHERE e.courseid = ?
    GROUP BY r.shortname
', [\$course->id]);

echo \"Usuarios por rol:\n\";
foreach (\$enrollments as \$enrollment) {
    echo \"  • {\$enrollment->shortname}: {\$enrollment->count}\n\";
}
"

# Verificar progreso de usuarios
kubectl exec -it moodle-684db8486b-5c5zp -- php -r "
require_once('/bitnami/moodle/config.php');
\$course = \$DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
\$completions = \$DB->get_records_sql('
    SELECT u.email, cc.timecompleted
    FROM {course_completions} cc
    JOIN {user} u ON cc.userid = u.id
    WHERE cc.course = ?
', [\$course->id]);

echo \"Usuarios que han completado el curso:\n\";
foreach (\$completions as \$completion) {
    echo \"  • {\$completion->email}: \" . date('Y-m-d H:i:s', \$completion->timecompleted) . \"\n\";
}
"
```

---

## 🔄 BACKUP Y MANTENIMIENTO

### 11. Realizar backup

```bash
# Backup de la base de datos
kubectl exec -it moodle-mariadb-0 -- mysqldump -u root -p moodle > backup_telemedicina_$(date +%Y%m%d_%H%M%S).sql

# Backup de archivos de Moodle
kubectl exec -it moodle-684db8486b-5c5zp -- tar -czf /tmp/moodle_backup_$(date +%Y%m%d_%H%M%S).tar.gz /bitnami/moodle/

# Copiar backup localmente
kubectl cp moodle-684db8486b-5c5zp:/tmp/moodle_backup_$(date +%Y%m%d_%H%M%S).tar.gz ./moodle_backup_$(date +%Y%m%d_%H%M%S).tar.gz

# Verificar backups
ls -la *backup*
```

### 12. Mantenimiento del sistema

```bash
# Limpiar caché de Moodle
kubectl exec -it moodle-684db8486b-5c5zp -- php /bitnami/moodle/admin/cli/purge_caches.php

# Ejecutar cron de Moodle
kubectl exec -it moodle-684db8486b-5c5zp -- php /bitnami/moodle/admin/cli/cron.php

# Verificar espacio en disco
kubectl exec -it moodle-684db8486b-5c5zp -- df -h
kubectl exec -it moodle-mariadb-0 -- df -h

# Verificar logs de errores
kubectl exec -it moodle-684db8486b-5c5zp -- tail -f /opt/bitnami/apache/logs/error_log
```

---

## 🌐 ACCESO EXTERNO

### 13. Configurar acceso externo

```bash
# Verificar servicios
kubectl get services

# Verificar ingress
kubectl get ingress

# Configurar dominio personalizado (opcional)
kubectl patch ingress moodle-ingress -p '{
  "spec": {
    "rules": [
      {
        "host": "telesalud.moodle.gob.sv",
        "http": {
          "paths": [
            {
              "path": "/",
              "pathType": "Prefix",
              "backend": {
                "service": {
                  "name": "moodle",
                  "port": {
                    "number": 80
                  }
                }
              }
            }
          ]
        }
      }
    ]
  }
}'

# Verificar certificado SSL (si está configurado)
kubectl get certificates
```

---

## 🚨 TROUBLESHOOTING

### 14. Solución de problemas comunes

```bash
# Verificar logs de errores
kubectl logs moodle-684db8486b-5c5zp --tail=100 | grep -i error
kubectl logs moodle-mariadb-0 --tail=100 | grep -i error

# Reiniciar pods si es necesario
kubectl rollout restart deployment/moodle
kubectl rollout restart statefulset/moodle-mariadb

# Verificar conectividad entre pods
kubectl exec -it moodle-684db8486b-5c5zp -- ping moodle-mariadb-0

# Verificar variables de entorno
kubectl exec -it moodle-684db8486b-5c5zp -- env | grep -i moodle
kubectl exec -it moodle-684db8486b-5c5zp -- env | grep -i mysql

# Verificar configuración de PHP
kubectl exec -it moodle-684db8486b-5c5zp -- php -m | grep -i mysql
kubectl exec -it moodle-684db8486b-5c5zp -- php -i | grep -i upload

# Verificar permisos de archivos
kubectl exec -it moodle-684db8486b-5c5zp -- ls -la /bitnami/moodle/
kubectl exec -it moodle-684db8486b-5c5zp -- ls -la /bitnami/moodle/moodledata/
```

---

## 📋 COMANDOS ÚTILES DE REFERENCIA

### 15. Comandos de referencia rápida

```bash
# === CONEXIÓN ===
gcloud container clusters get-credentials moodle-cluster --zone=us-central1-c --project=moodle-gcp-test
kubectl exec -it moodle-684db8486b-5c5zp -- /bin/bash
cd /bitnami/moodle/

# === CONFIGURACIÓN CURSO ===
php setup_complete_telemedicina_course.php

# === OAUTH ===
php setup_google_oauth_telesalud.php
php set_google_credentials.php
php verify_oauth_setup.php

# === USUARIOS ===
php create_telesalud_users.php
php enroll_users_telemedicina.php

# === VERIFICACIÓN ===
kubectl get pods -l app=moodle
kubectl logs moodle-684db8486b-5c5zp --tail=50
kubectl top pods

# === BASE DE DATOS ===
kubectl exec -it moodle-mariadb-0 -- mysql -u root -p moodle

# === BACKUP ===
kubectl exec -it moodle-mariadb-0 -- mysqldump -u root -p moodle > backup.sql

# === ACCESO WEB ===
echo "Moodle URL: http://34.72.133.6"
echo "Admin URL: http://34.72.133.6/admin"
echo "Curso URL: http://34.72.133.6/course/view.php?id=COURSE_ID"
```

---

## 📞 CONTACTO Y SOPORTE

Para problemas específicos:

1. **Logs de error**: Siempre incluir logs relevantes
2. **Configuración**: Verificar configuración actual
3. **Reprodución**: Pasos para reproducir el problema
4. **Entorno**: Información del entorno (GCP, versiones)

**URLs importantes:**
- **Moodle**: http://34.72.133.6
- **Admin**: http://34.72.133.6/admin
- **Curso**: http://34.72.133.6/course/view.php?id={COURSE_ID}

---

**¡El curso de Telemedicina está listo para usar!** 🏥✨