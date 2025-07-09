# 🔧 Solución para Scripts de Moodle en Kubernetes

## ❌ Problemas Identificados

1. **Pod no encontrado**: El pod `moodle-684db8486b-5c5zp` ya no existe
2. **Scripts ejecutados localmente**: Los scripts se ejecutaron en Cloud Shell en lugar del pod
3. **Ruta incorrecta**: Los scripts buscan `/bitnami/moodle/config.php` que no existe en el directorio local

## ✅ Solución Paso a Paso

### 1. Encontrar el Pod Correcto de Moodle

```bash
# Verificar pods actuales de Moodle
kubectl get pods -n moodle

# Ejemplo de salida esperada:
# NAME                      READY   STATUS    RESTARTS   AGE
# moodle-xxxxxxxxxx-xxxxx   1/1     Running   0          xxh
```

### 2. Conectar al Pod Correcto

```bash
# Usar el nombre del pod actual (reemplazar por el nombre real)
kubectl exec -it $(kubectl get pods -n moodle -o jsonpath='{.items[0].metadata.name}') -n moodle -- /bin/bash
```

### 3. Verificar Directorio de Moodle

```bash
# Dentro del pod, verificar la estructura
pwd
ls -la /

# Buscar directorio de Moodle
find / -name "config.php" -type f 2>/dev/null | head -10
```

### 4. Ubicar el Directorio Correcto

```bash
# Posibles ubicaciones de Moodle en contenedores Bitnami:
ls -la /opt/bitnami/moodle/
ls -la /bitnami/moodle/
ls -la /var/www/html/
```

### 5. Descargar Scripts en el Pod

```bash
# Dentro del pod, ir al directorio correcto de Moodle
cd /opt/bitnami/moodle/  # o la ruta correcta que encuentres

# Descargar scripts
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/setup_google_oauth_telesalud.php
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/set_google_credentials.php
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/create_telesalud_users.php
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/verify_oauth_setup.php
```

### 6. Ejecutar Scripts en Orden

```bash
# Ejecutar scripts desde el directorio correcto
php setup_google_oauth_telesalud.php
php set_google_credentials.php
php create_telesalud_users.php
php verify_oauth_setup.php
```

## 🔍 Comandos de Diagnóstico

### Verificar Pod Actual
```bash
# Listar pods de Moodle
kubectl get pods -n moodle -o wide

# Describir el pod para más información
kubectl describe pod <pod-name> -n moodle
```

### Verificar Archivos de Configuración
```bash
# Dentro del pod, verificar config.php
ls -la /opt/bitnami/moodle/config.php
cat /opt/bitnami/moodle/config.php | head -20
```

### Verificar PHP y Extensiones
```bash
# Dentro del pod
php -v
php -m | grep -i mysql
```

## 🎯 Comando Todo-en-Uno

```bash
# Ejecutar desde Cloud Shell
POD_NAME=$(kubectl get pods -n moodle -o jsonpath='{.items[0].metadata.name}')
echo "Pod encontrado: $POD_NAME"

# Conectar y ejecutar
kubectl exec -it $POD_NAME -n moodle -- bash -c "
cd /opt/bitnami/moodle/ && \
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/setup_google_oauth_telesalud.php && \
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/set_google_credentials.php && \
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/create_telesalud_users.php && \
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/verify_oauth_setup.php && \
echo 'Scripts descargados correctamente'
"
```

## 🔧 Solución Alternativa: Copy desde Local

Si prefieres copiar desde tu máquina local:

```bash
# Descargar scripts localmente
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/setup_google_oauth_telesalud.php
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/set_google_credentials.php
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/create_telesalud_users.php
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/verify_oauth_setup.php

# Copiar al pod
POD_NAME=$(kubectl get pods -n moodle -o jsonpath='{.items[0].metadata.name}')
kubectl cp setup_google_oauth_telesalud.php $POD_NAME:/opt/bitnami/moodle/ -n moodle
kubectl cp set_google_credentials.php $POD_NAME:/opt/bitnami/moodle/ -n moodle
kubectl cp create_telesalud_users.php $POD_NAME:/opt/bitnami/moodle/ -n moodle
kubectl cp verify_oauth_setup.php $POD_NAME:/opt/bitnami/moodle/ -n moodle

# Ejecutar dentro del pod
kubectl exec -it $POD_NAME -n moodle -- bash -c "
cd /opt/bitnami/moodle/ && \
php setup_google_oauth_telesalud.php && \
php set_google_credentials.php && \
php create_telesalud_users.php && \
php verify_oauth_setup.php
"
```

## 📝 Notas Importantes

1. **Directorio de Moodle**: Puede variar según la imagen, buscar con `find`
2. **Permisos**: Asegurarse de tener permisos de escritura en el directorio
3. **Configuración**: Verificar que `config.php` exista y sea accesible
4. **Credenciales**: Editar `set_google_credentials.php` con las credenciales correctas antes de ejecutar

## 🚨 Troubleshooting

### Si el pod no existe:
```bash
# Verificar deployment
kubectl get deployment -n moodle
kubectl get pods -n moodle --show-labels
```

### Si no encuentras config.php:
```bash
# Buscar en todo el sistema
kubectl exec -it <pod-name> -n moodle -- find / -name "config.php" -type f 2>/dev/null
```

### Si hay problemas de permisos:
```bash
# Verificar permisos
kubectl exec -it <pod-name> -n moodle -- ls -la /opt/bitnami/moodle/
```