#!/bin/bash

# Solución para problemas de base de datos en Moodle
echo "=== Solución de Problemas de Base de Datos Moodle ==="

# 1. Obtener nombre del pod
POD_NAME=$(kubectl get pods -n moodle -o jsonpath='{.items[0].metadata.name}')
echo "Pod encontrado: $POD_NAME"

# 2. Copiar scripts de diagnóstico al pod
echo "Copiando scripts de diagnóstico..."
kubectl cp diagnostico_db.php $POD_NAME:/opt/bitnami/moodle/ -n moodle
kubectl cp fix_db_permissions.php $POD_NAME:/opt/bitnami/moodle/ -n moodle

# 3. Ejecutar diagnóstico
echo "Ejecutando diagnóstico de base de datos..."
kubectl exec -it $POD_NAME -n moodle -- bash -c "cd /opt/bitnami/moodle/ && php diagnostico_db.php"

# 4. Reparar permisos si es necesario
echo "Reparando permisos de base de datos..."
kubectl exec -it $POD_NAME -n moodle -- bash -c "cd /opt/bitnami/moodle/ && php fix_db_permissions.php"

# 5. Verificar reparación
echo "Verificando reparación..."
kubectl exec -it $POD_NAME -n moodle -- bash -c "cd /opt/bitnami/moodle/ && php diagnostico_db.php"

# 6. Continuar con scripts OAuth si la reparación fue exitosa
echo "Continuando con scripts OAuth..."
kubectl exec -it $POD_NAME -n moodle -- bash -c "
cd /opt/bitnami/moodle/ && \
echo 'Ejecutando setup_google_oauth_telesalud.php...' && \
php setup_google_oauth_telesalud.php && \
echo 'Ejecutando set_google_credentials.php...' && \
php set_google_credentials.php && \
echo 'Ejecutando create_telesalud_users.php...' && \
php create_telesalud_users.php && \
echo 'Ejecutando verify_oauth_setup.php...' && \
php verify_oauth_setup.php
"

echo "=== Proceso completado ==="