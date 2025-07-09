# 👥 Guía: Usuarios Específicos para Telemedicina

## 🎯 Usuarios a configurar

1. **jose.vargas@telesalud.gob.sv** - Instructor principal
2. **rodolfovargasoff@gmail.com** - Administrador del sistema

---

## 🚀 Implementación Rápida

### 1. Conectar al cluster y pod

```bash
# Conectar al cluster GKE
gcloud container clusters get-credentials moodle-cluster \
  --zone=us-central1-c \
  --project=moodle-gcp-test

# Acceder al pod de Moodle
kubectl exec -it moodle-684db8486b-5c5zp -- /bin/bash
cd /bitnami/moodle/
```

### 2. Descargar scripts específicos

```bash
# Descargar script de creación de usuarios específicos
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/create_specific_users_telemedicina.php

# Descargar script de verificación
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/verify_specific_users.php
```

### 3. Ejecutar configuración

```bash
# Crear y enrollar usuarios específicos
php create_specific_users_telemedicina.php

# Verificar configuración
php verify_specific_users.php
```

---

## 👤 Detalles de los usuarios

### **José Vargas** (`jose.vargas@telesalud.gob.sv`)
- **Rol**: Instructor principal (editingteacher)
- **Institución**: Ministerio de Salud
- **Departamento**: Dirección de Telesalud
- **Grupo**: Grupo Telesalud
- **Permisos**: 
  - Crear y editar actividades
  - Calificar estudiantes
  - Gestionar contenido del curso
  - Ver reportes y analíticas

### **Rodolfo Vargas** (`rodolfovargasoff@gmail.com`)
- **Rol**: Administrador del sistema (manager)
- **Institución**: Administración del Sistema
- **Departamento**: Administración General
- **Grupo**: Grupo Administradores
- **Permisos**:
  - Control total del curso
  - Gestión de usuarios
  - Configuración del sistema
  - Acceso a todos los reportes

---

## 🔐 Configuración OAuth actualizada

### Dominios permitidos:
- ✅ `@telesalud.gob.sv` (original)
- ✅ `@goes.gob.sv` (original)
- ✅ `@gmail.com` (añadido para administrador)

### Configuración aplicada:
1. **Dominios globales**: Actualizados para incluir gmail.com
2. **OAuth issuer**: Configurado para los tres dominios
3. **Creación automática**: Deshabilitada (usuarios deben existir previamente)
4. **Mapeo de campos**: Habilitado para sincronización

---

## 📊 Verificación de acceso

### Comando para verificar usuarios:

```bash
# Verificar que los usuarios estén correctamente configurados
kubectl exec -it moodle-684db8486b-5c5zp -- php -r "
require_once('/bitnami/moodle/config.php');

echo '=== VERIFICACIÓN DE USUARIOS ESPECÍFICOS ===\n';

\$users = [
    'jose.vargas@telesalud.gob.sv',
    'rodolfovargasoff@gmail.com'
];

foreach (\$users as \$email) {
    \$user = \$DB->get_record('user', ['email' => \$email]);
    if (\$user) {
        echo \"✅ \$email: Usuario existe (ID: \$user->id)\n\";
        
        // Verificar enrollment
        \$course = \$DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
        \$enrolled = \$DB->get_record_sql('
            SELECT 1 FROM {user_enrolments} ue 
            JOIN {enrol} e ON ue.enrolid = e.id 
            WHERE e.courseid = ? AND ue.userid = ?
        ', [\$course->id, \$user->id]);
        
        echo \"   Enrollado: \" . (\$enrolled ? 'Sí' : 'No') . \"\n\";
    } else {
        echo \"❌ \$email: Usuario NO existe\n\";
    }
}
"
```

### Verificar dominios permitidos:

```bash
kubectl exec -it moodle-684db8486b-5c5zp -- php -r "
require_once('/bitnami/moodle/config.php');
\$domains = get_config('core', 'allowemailaddresses');
echo 'Dominios permitidos: ' . \$domains . \"\n\";
"
```

---

## 🌐 Instrucciones de acceso

### Para ambos usuarios:

1. **Ir a la página de login**:
   ```
   http://34.72.133.6/login/index.php
   ```

2. **Hacer clic en "Google Telesalud"**

3. **Ingresar credenciales de Google**:
   - `jose.vargas@telesalud.gob.sv` + contraseña de Google
   - `rodolfovargasoff@gmail.com` + contraseña de Google

4. **Acceso automático a Moodle**

5. **Acceder al curso desde el dashboard**

---

## 🔧 Troubleshooting

### Si no pueden acceder:

1. **Verificar que las cuentas de Google existan**:
   ```bash
   # Probar login manual en Google
   https://accounts.google.com/
   ```

2. **Verificar configuración OAuth**:
   ```bash
   kubectl exec -it moodle-684db8486b-5c5zp -- php verify_specific_users.php
   ```

3. **Revisar logs de Moodle**:
   ```bash
   kubectl logs moodle-684db8486b-5c5zp --tail=50 | grep -i oauth
   ```

4. **Verificar configuración Google Console**:
   - URI de callback: `http://34.72.133.6/admin/oauth2callback.php`
   - Dominios autorizados: `34.72.133.6`
   - Client ID y Secret configurados

### Errores comunes:

#### Error: "Domain not allowed"
```bash
# Verificar dominios
kubectl exec -it moodle-684db8486b-5c5zp -- php -r "
require_once('/bitnami/moodle/config.php');
echo get_config('core', 'allowemailaddresses');
"
```

#### Error: "User not found"
```bash
# Recrear usuarios
kubectl exec -it moodle-684db8486b-5c5zp -- php create_specific_users_telemedicina.php
```

#### Error: "Invalid redirect URI"
- Verificar configuración en Google Console
- URL debe ser exactamente: `http://34.72.133.6/admin/oauth2callback.php`

---

## 📈 Capacidades por rol

### José Vargas (editingteacher):
- ✅ Ver curso completo
- ✅ Editar actividades
- ✅ Calificar estudiantes
- ✅ Ver progreso de estudiantes
- ✅ Agregar contenido
- ✅ Configurar restricciones de acceso
- ❌ Gestionar usuarios del curso
- ❌ Configuración global del sistema

### Rodolfo Vargas (manager):
- ✅ Control total del curso
- ✅ Gestionar todos los usuarios
- ✅ Configuración del curso
- ✅ Acceso a todos los reportes
- ✅ Backup y restauración
- ✅ Configuración global
- ✅ Gestión de roles y permisos

---

## 📋 Comandos de verificación rápida

```bash
# Conectar y verificar en un solo comando
gcloud container clusters get-credentials moodle-cluster \
  --zone=us-central1-c \
  --project=moodle-gcp-test && \
kubectl exec -it moodle-684db8486b-5c5zp -- php /bitnami/moodle/verify_specific_users.php

# Verificar estado del curso
kubectl exec -it moodle-684db8486b-5c5zp -- php -r "
require_once('/bitnami/moodle/config.php');
\$course = \$DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
\$enrolled = \$DB->count_records_sql('SELECT COUNT(*) FROM {user_enrolments} ue JOIN {enrol} e ON ue.enrolid = e.id WHERE e.courseid = ?', [\$course->id]);
echo 'Curso ID: ' . \$course->id . \"\n\";
echo 'Total enrollados: ' . \$enrolled . \"\n\";
echo 'URL: http://34.72.133.6/course/view.php?id=' . \$course->id . \"\n\";
"
```

---

## ✅ Checklist de implementación

- [ ] Scripts descargados
- [ ] `create_specific_users_telemedicina.php` ejecutado
- [ ] `verify_specific_users.php` ejecutado sin errores
- [ ] Dominios permitidos incluyen gmail.com
- [ ] OAuth issuer configurado para 3 dominios
- [ ] Usuarios creados en Moodle
- [ ] Usuarios enrollados al curso
- [ ] Grupos asignados correctamente
- [ ] Roles configurados apropiadamente
- [ ] Acceso OAuth probado

---

## 🎯 Resultado esperado

Después de la implementación:

1. **José Vargas** puede:
   - Acceder con `jose.vargas@telesalud.gob.sv`
   - Ver y gestionar el curso como instructor
   - Crear y editar actividades
   - Calificar estudiantes

2. **Rodolfo Vargas** puede:
   - Acceder con `rodolfovargasoff@gmail.com`
   - Administrar completamente el sistema
   - Gestionar todos los usuarios
   - Configurar el curso

3. **Sistema configurado** para:
   - OAuth 2.0 con Google
   - 3 dominios permitidos
   - Acceso progresivo en el curso
   - Grupos automáticos por institución

**¡Usuarios listos para acceder al Curso de Telemedicina!** 🏥✨