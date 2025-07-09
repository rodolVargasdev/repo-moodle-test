# 🔐 Guía Paso a Paso: OAuth 2.0 Google para Telesalud

## 📋 Resumen de implementación

**Objetivo**: Configurar OAuth 2.0 con Google para permitir solo usuarios con dominios `@telesalud.gob.sv` y `@goes.gob.sv`

**Características implementadas**:
- ✅ OAuth 2.0 con Google
- ✅ Restricciones de dominio específicas
- ✅ Prevención de creación automática de cuentas
- ✅ Usuarios deben existir previamente en Moodle
- ✅ Actualización automática de perfiles

---

## 🎯 PASO 1: Configurar Google Cloud Console

### 1.1 Crear proyecto en Google Cloud Console

1. **Acceder a Google Cloud Console**
   ```
   https://console.developers.google.com/
   ```

2. **Crear nuevo proyecto**
   - Nombre: "Moodle Telesalud El Salvador"
   - ID del proyecto: `moodle-telesalud-sv`

3. **Habilitar APIs necesarias**
   - Ve a "APIs y servicios" → "Biblioteca"
   - Buscar y habilitar:
     - **Google+ API** (necesaria para OAuth)
     - **Google People API**
     - **Google OAuth2 API**

### 1.2 Configurar credenciales OAuth 2.0

1. **Crear credenciales**
   - Ve a "APIs y servicios" → "Credenciales"
   - Clic en "Crear credenciales" → "ID de cliente OAuth 2.0"

2. **Configurar aplicación web**
   - Tipo de aplicación: **Aplicación web**
   - Nombre: "Moodle Telesalud OAuth"
   - URI de redirección autorizados:
     ```
     http://34.72.133.6/admin/oauth2callback.php
     ```

3. **Configurar pantalla de consentimiento**
   - Nombre de la aplicación: "Moodle Telesalud"
   - Dominios autorizados: `34.72.133.6`
   - Alcances: `email`, `profile`, `openid`

4. **Obtener credenciales**
   - Copia el **Client ID** y **Client Secret**
   - Los necesitarás en el paso 3

---

## 🔧 PASO 2: Configurar Moodle (Configuración inicial)

### 2.1 Conectar al pod de Moodle

```bash
# Conectar al pod de Moodle
kubectl exec -it moodle-684db8486b-5c5zp -- /bin/bash

# Navegar al directorio de Moodle
cd /bitnami/moodle/
```

### 2.2 Ejecutar configuración inicial

```bash
# Ejecutar script de configuración OAuth 2.0
php setup_google_oauth_telesalud.php
```

**Resultado esperado**:
```
🔧 Configurando OAuth 2.0 Google para Telesalud...
✅ Autenticación OAuth 2.0 habilitada
✅ Dominios permitidos configurados: telesalud.gob.sv,goes.gob.sv
✅ Prevención de creación automática de cuentas habilitada
✅ Configuraciones OAuth 2.0 básicas aplicadas
✅ Issuer Google Telesalud creado
✅ Endpoints de Google configurados
✅ Mapeo de campos configurado
✅ Restricciones de dominio específicas configuradas
✅ Dominios de email permitidos configurados globalmente
```

---

## 🔑 PASO 3: Configurar credenciales de Google

### 3.1 Editar archivo de credenciales

```bash
# Editar el archivo de credenciales
nano set_google_credentials.php
```

### 3.2 Reemplazar credenciales

Buscar estas líneas:
```php
$GOOGLE_CLIENT_ID = 'TU_GOOGLE_CLIENT_ID_AQUI';
$GOOGLE_CLIENT_SECRET = 'TU_GOOGLE_CLIENT_SECRET_AQUI';
```

Reemplazar con las credenciales obtenidas de Google Console:
```php
$GOOGLE_CLIENT_ID = '123456789-abcdefgh.apps.googleusercontent.com';
$GOOGLE_CLIENT_SECRET = 'GOCSPX-tu_client_secret_aqui';
```

### 3.3 Ejecutar configuración de credenciales

```bash
# Ejecutar script de credenciales
php set_google_credentials.php
```

**Resultado esperado**:
```
🔧 Configurando credenciales de Google OAuth 2.0...
✅ Client ID configurado
✅ Client Secret configurado
🎯 Credenciales configuradas exitosamente
```

---

## 👥 PASO 4: Crear usuarios de ejemplo

### 4.1 Ejecutar script de creación de usuarios

```bash
# Crear usuarios de ejemplo
php create_telesalud_users.php
```

**Resultado esperado**:
```
👥 Creando usuarios de ejemplo para Telesalud...
✅ Usuario creado: director@telesalud.gob.sv
✅ Usuario creado: cardiologo@telesalud.gob.sv
✅ Usuario creado: coordinador@goes.gob.sv
✅ Usuario creado: paramedico@goes.gob.sv
📊 Resumen de usuarios creados:
   • Usuarios nuevos: 8
   • Usuarios actualizados: 0
```

**Usuarios creados**:
- **@telesalud.gob.sv**: director, cardiologo, enfermera.jefe, tecnico.radiologia
- **@goes.gob.sv**: coordinador, paramedico, instructor, voluntario

---

## 🔍 PASO 5: Verificar configuración

### 5.1 Ejecutar script de verificación

```bash
# Verificar que todo esté configurado correctamente
php verify_oauth_setup.php
```

**Resultado esperado** (sin errores):
```
🔍 Verificando configuración OAuth 2.0 para Telesalud...

1. Verificando autenticación OAuth 2.0...
   ✅ OAuth 2.0 está habilitado
2. Verificando dominios permitidos globalmente...
   ✅ Dominios permitidos configurados: telesalud.gob.sv,goes.gob.sv
3. Verificando issuer de Google...
   ✅ Issuer 'Google Telesalud' encontrado
...
🎉 ¡CONFIGURACIÓN OAUTH 2.0 CORRECTA!
```

### 5.2 Si hay errores

Si encuentras errores, ejecuta los scripts faltantes:
```bash
# Para errores de configuración inicial
php setup_google_oauth_telesalud.php

# Para errores de credenciales
php set_google_credentials.php

# Para errores de usuarios
php create_telesalud_users.php
```

---

## 🧪 PASO 6: Probar autenticación

### 6.1 Acceder a la página de login

1. **Abrir navegador**
   ```
   http://34.72.133.6/login/index.php
   ```

2. **Verificar botón de Google**
   - Deberías ver un botón "Google Telesalud"
   - Si no aparece, revisar configuración

### 6.2 Probar con usuario válido

1. **Clic en "Google Telesalud"**
2. **Ingresar credenciales de Google**
   - Usuario: `director@telesalud.gob.sv`
   - Contraseña: [su contraseña de Google]
3. **Verificar acceso**
   - Debe redireccionar a Moodle
   - Usuario debe estar logueado

### 6.3 Probar con usuario no válido

1. **Intentar con Gmail personal**
   - Usuario: `test@gmail.com`
   - Debe ser **rechazado**
2. **Intentar con otro dominio**
   - Usuario: `test@otrodominio.com`
   - Debe ser **rechazado**

---

## 📋 PASO 7: Configuración adicional (Opcional)

### 7.1 Personalizar botón de login

```php
// Personalizar texto del botón
$issuer = $DB->get_record('oauth2_issuer', ['name' => 'Google Telesalud']);
$issuer->name = 'Iniciar sesión con Google';
$DB->update_record('oauth2_issuer', $issuer);
```

### 7.2 Configurar grupos automáticos

```php
// Asignar usuarios automáticamente a grupos por dominio
if (strpos($user->email, '@telesalud.gob.sv') !== false) {
    // Asignar al grupo "Telesalud"
    groups_add_member($telesalud_group_id, $user->id);
} elseif (strpos($user->email, '@goes.gob.sv') !== false) {
    // Asignar al grupo "GOES"
    groups_add_member($goes_group_id, $user->id);
}
```

---

## 🚨 SOLUCIÓN DE PROBLEMAS

### ❌ Error: "Invalid redirect URI"

**Causa**: URL de callback mal configurada en Google Console

**Solución**:
1. Ve a Google Console → Credenciales
2. Edita las credenciales OAuth 2.0
3. Verifica que la URI sea exactamente: `http://34.72.133.6/admin/oauth2callback.php`

### ❌ Error: "User not found"

**Causa**: Usuario no existe en Moodle

**Solución**:
```bash
# Crear usuario manualmente
php create_telesalud_users.php
```

### ❌ Error: "Domain not allowed"

**Causa**: Dominios no configurados correctamente

**Solución**:
```bash
# Verificar configuración
php verify_oauth_setup.php

# Reconfigurar si es necesario
php setup_google_oauth_telesalud.php
```

### ❌ Error: "Token validation failed"

**Causa**: Problema con credenciales de Google

**Solución**:
```bash
# Verificar credenciales
php set_google_credentials.php
```

---

## ⚙️ CONFIGURACIÓN AVANZADA

### 🔐 Forzar HTTPS (Recomendado para producción)

```bash
# Configurar SSL en Kubernetes
kubectl create secret tls moodle-tls \
  --cert=path/to/cert.crt \
  --key=path/to/cert.key

# Actualizar ingress para HTTPS
kubectl patch ingress moodle-ingress -p '{"spec":{"tls":[{"hosts":["moodle.telesalud.gob.sv"],"secretName":"moodle-tls"}]}}'
```

### 🏥 Configurar dominios personalizados

```bash
# Actualizar dominios permitidos
set_config('allowemailaddresses', 'telesalud.gob.sv,goes.gob.sv,hospital.gob.sv');
```

### 📊 Habilitar logging detallado

```php
// En config.php
$CFG->debug = DEBUG_DEVELOPER;
$CFG->debugdisplay = 1;
```

---

## 📋 CHECKLIST DE IMPLEMENTACIÓN

### ✅ Google Cloud Console
- [ ] Proyecto creado
- [ ] APIs habilitadas
- [ ] Credenciales OAuth 2.0 configuradas
- [ ] URI de callback correcta
- [ ] Pantalla de consentimiento configurada

### ✅ Moodle Backend
- [ ] Script `setup_google_oauth_telesalud.php` ejecutado
- [ ] Script `set_google_credentials.php` ejecutado
- [ ] Script `create_telesalud_users.php` ejecutado
- [ ] Script `verify_oauth_setup.php` sin errores

### ✅ Pruebas
- [ ] Botón de Google visible en login
- [ ] Autenticación funciona con dominios válidos
- [ ] Autenticación rechaza dominios no válidos
- [ ] Usuarios se crean/actualizan correctamente

### ✅ Seguridad
- [ ] Solo dominios permitidos pueden acceder
- [ ] Creación automática de cuentas deshabilitada
- [ ] Usuarios deben existir previamente
- [ ] HTTPS configurado (producción)

---

## 📞 SOPORTE Y MANTENIMIENTO

### 📊 Monitoreo

```bash
# Ver logs de autenticación
kubectl logs moodle-684db8486b-5c5zp | grep oauth

# Verificar usuarios OAuth
SELECT email, auth, timecreated FROM mdl_user WHERE auth = 'oauth2';

# Verificar configuración OAuth
SELECT * FROM mdl_oauth2_issuer WHERE name = 'Google Telesalud';
```

### 🔄 Mantenimiento regular

1. **Mensual**: Revisar logs de autenticación
2. **Trimestral**: Verificar usuarios activos
3. **Anual**: Rotar credenciales de Google

### 📋 Respaldo de configuración

```bash
# Exportar configuración OAuth
mysqldump -u root -p moodle mdl_oauth2_issuer mdl_oauth2_endpoint mdl_oauth2_issuer_config > oauth_backup.sql
```

---

## 🎯 RESULTADO FINAL

**¡Configuración OAuth 2.0 completada exitosamente!**

✅ **Funcionalidades implementadas**:
- OAuth 2.0 con Google habilitado
- Restricciones de dominio: `@telesalud.gob.sv` y `@goes.gob.sv`
- Usuarios deben existir previamente en Moodle
- Botón de Google visible en página de login
- Actualización automática de perfiles
- Verificación de seguridad implementada

🔗 **URLs importantes**:
- **Login**: http://34.72.133.6/login/index.php
- **Admin**: http://34.72.133.6/admin/
- **Callback**: http://34.72.133.6/admin/oauth2callback.php

🔐 **Seguridad**:
- Solo usuarios con dominios específicos pueden acceder
- Sin creación automática de cuentas
- Validación de dominios a nivel global y OAuth
- Mapeo seguro de campos de usuario

**¡El sistema está listo para uso en producción!** 🚀