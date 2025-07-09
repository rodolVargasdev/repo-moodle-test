# 🔧 Solución: Sin curl en el Pod de Moodle

## ❌ Problema
El pod de Moodle no tiene `curl` instalado, lo que es común en contenedores minimalistas.

## ✅ Soluciones Alternativas

### Solución 1: Usar kubectl cp (Recomendado)

```bash
# Descargar scripts localmente en Cloud Shell
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/setup_google_oauth_telesalud.php
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/set_google_credentials.php
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/create_telesalud_users.php
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/verify_oauth_setup.php

# Obtener nombre del pod
POD_NAME=$(kubectl get pods -n moodle -o jsonpath='{.items[0].metadata.name}')

# Copiar archivos al pod
kubectl cp setup_google_oauth_telesalud.php $POD_NAME:/opt/bitnami/moodle/ -n moodle
kubectl cp set_google_credentials.php $POD_NAME:/opt/bitnami/moodle/ -n moodle
kubectl cp create_telesalud_users.php $POD_NAME:/opt/bitnami/moodle/ -n moodle
kubectl cp verify_oauth_setup.php $POD_NAME:/opt/bitnami/moodle/ -n moodle

# Ejecutar scripts en el pod
kubectl exec -it $POD_NAME -n moodle -- bash -c "
cd /opt/bitnami/moodle/ && \
php setup_google_oauth_telesalud.php && \
php set_google_credentials.php && \
php create_telesalud_users.php && \
php verify_oauth_setup.php
"
```

### Solución 2: Verificar si wget está disponible

```bash
# Verificar herramientas disponibles
kubectl exec -it $POD_NAME -n moodle -- bash -c "
which wget || which curl || which php || echo 'Verificando herramientas...'
"

# Si wget está disponible, úsalo:
kubectl exec -it $POD_NAME -n moodle -- bash -c "
cd /opt/bitnami/moodle/ && \
wget https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/setup_google_oauth_telesalud.php && \
wget https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/set_google_credentials.php && \
wget https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/create_telesalud_users.php && \
wget https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/verify_oauth_setup.php
"
```

### Solución 3: Usar PHP para descargar archivos

```bash
# Crear un script PHP para descargar
kubectl exec -it $POD_NAME -n moodle -- bash -c "
cd /opt/bitnami/moodle/ && \
cat > download_scripts.php << 'EOF'
<?php
\$scripts = [
    'setup_google_oauth_telesalud.php',
    'set_google_credentials.php', 
    'create_telesalud_users.php',
    'verify_oauth_setup.php'
];

foreach (\$scripts as \$script) {
    \$url = 'https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/' . \$script;
    \$content = file_get_contents(\$url);
    if (\$content !== false) {
        file_put_contents(\$script, \$content);
        echo \"Downloaded: \$script\n\";
    } else {
        echo \"Failed to download: \$script\n\";
    }
}
EOF

php download_scripts.php
"
```

### Solución 4: Comando Todo-en-Uno (Método kubectl cp)

```bash
# Ejecutar todo en una sola vez
POD_NAME=$(kubectl get pods -n moodle -o jsonpath='{.items[0].metadata.name}')
echo "Pod encontrado: $POD_NAME"

# Descargar archivos localmente
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/setup_google_oauth_telesalud.php
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/set_google_credentials.php
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/create_telesalud_users.php
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/verify_oauth_setup.php

# Verificar que se descargaron
ls -la *.php

# Copiar al pod
for file in setup_google_oauth_telesalud.php set_google_credentials.php create_telesalud_users.php verify_oauth_setup.php; do
    kubectl cp $file $POD_NAME:/opt/bitnami/moodle/ -n moodle
done

# Ejecutar en el pod
kubectl exec -it $POD_NAME -n moodle -- bash -c "
cd /opt/bitnami/moodle/ && \
ls -la *.php && \
echo 'Ejecutando scripts...' && \
php setup_google_oauth_telesalud.php && \
echo 'Script 1 completado' && \
php set_google_credentials.php && \
echo 'Script 2 completado' && \
php create_telesalud_users.php && \
echo 'Script 3 completado' && \
php verify_oauth_setup.php && \
echo 'Script 4 completado'
"
```

### Solución 5: Instalar curl temporalmente (si tienes permisos)

```bash
# Intentar instalar curl (puede no funcionar en contenedores restrictivos)
kubectl exec -it $POD_NAME -n moodle -- bash -c "
apt-get update && apt-get install -y curl
"

# Si funciona, usar el comando original
kubectl exec -it $POD_NAME -n moodle -- bash -c "
cd /opt/bitnami/moodle/ && \
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/setup_google_oauth_telesalud.php && \
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/set_google_credentials.php && \
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/create_telesalud_users.php && \
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/verify_oauth_setup.php
"
```

## 🚀 Comando Rápido (Recomendado)

```bash
# Ejecutar este comando completo
POD_NAME=$(kubectl get pods -n moodle -o jsonpath='{.items[0].metadata.name}') && \
echo "Pod: $POD_NAME" && \
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/setup_google_oauth_telesalud.php && \
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/set_google_credentials.php && \
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/create_telesalud_users.php && \
curl -O https://raw.githubusercontent.com/rodolVargasdev/repo-moodle-test/main/verify_oauth_setup.php && \
kubectl cp setup_google_oauth_telesalud.php $POD_NAME:/opt/bitnami/moodle/ -n moodle && \
kubectl cp set_google_credentials.php $POD_NAME:/opt/bitnami/moodle/ -n moodle && \
kubectl cp create_telesalud_users.php $POD_NAME:/opt/bitnami/moodle/ -n moodle && \
kubectl cp verify_oauth_setup.php $POD_NAME:/opt/bitnami/moodle/ -n moodle && \
echo "Archivos copiados. Ejecutando scripts..." && \
kubectl exec -it $POD_NAME -n moodle -- bash -c "cd /opt/bitnami/moodle/ && php setup_google_oauth_telesalud.php && php set_google_credentials.php && php create_telesalud_users.php && php verify_oauth_setup.php"
```

## 📝 Verificación

```bash
# Verificar que los archivos se copiaron correctamente
kubectl exec -it $POD_NAME -n moodle -- ls -la /opt/bitnami/moodle/*.php

# Verificar el directorio de configuración
kubectl exec -it $POD_NAME -n moodle -- ls -la /opt/bitnami/moodle/config.php
```

## 🔧 Troubleshooting

### Si el directorio no existe:
```bash
# Buscar el directorio correcto de Moodle
kubectl exec -it $POD_NAME -n moodle -- find / -name "config.php" -type f 2>/dev/null
```

### Si hay problemas de permisos:
```bash
# Verificar permisos
kubectl exec -it $POD_NAME -n moodle -- ls -la /opt/bitnami/moodle/
```

### Si necesitas editar credenciales:
```bash
# Editar el archivo de credenciales
kubectl exec -it $POD_NAME -n moodle -- nano /opt/bitnami/moodle/set_google_credentials.php
```