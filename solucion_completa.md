# 🔧 Solución Completa: Error de Base de Datos en Moodle

## 📋 Resumen del Problema
- **Error**: `!!! Error writing to database !!!`
- **Causa**: Problema de sintaxis PHP en comando de diagnóstico
- **Solución**: Scripts PHP separados para diagnóstico y reparación

## 🚀 Comandos para Ejecutar (En orden)

### 1. Descargar Scripts de Diagnóstico
```bash
# Descargar scripts de diagnóstico y reparación
curl -O https://raw.githubusercontent.com/assistants/workspace-files/main/diagnostico_db.php
curl -O https://raw.githubusercontent.com/assistants/workspace-files/main/fix_db_permissions.php
```

### 2. Copiar Scripts al Pod
```bash
# Obtener nombre del pod
POD_NAME=$(kubectl get pods -n moodle -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD_NAME"

# Copiar scripts al pod
kubectl cp diagnostico_db.php $POD_NAME:/opt/bitnami/moodle/ -n moodle
kubectl cp fix_db_permissions.php $POD_NAME:/opt/bitnami/moodle/ -n moodle
```

### 3. Ejecutar Diagnóstico
```bash
# Ejecutar diagnóstico de base de datos
kubectl exec -it $POD_NAME -n moodle -- bash -c "cd /opt/bitnami/moodle/ && php diagnostico_db.php"
```

### 4. Reparar Permisos (Si es necesario)
```bash
# Reparar permisos de base de datos
kubectl exec -it $POD_NAME -n moodle -- bash -c "cd /opt/bitnami/moodle/ && php fix_db_permissions.php"
```

### 5. Verificar Reparación
```bash
# Verificar que la reparación fue exitosa
kubectl exec -it $POD_NAME -n moodle -- bash -c "cd /opt/bitnami/moodle/ && php diagnostico_db.php"
```

### 6. Continuar con Scripts OAuth
```bash
# Ejecutar scripts OAuth originales
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
```

## 🎯 Comando Todo-en-Uno (Alternativa)
```bash
# Crear scripts localmente y ejecutar todo en secuencia
POD_NAME=$(kubectl get pods -n moodle -o jsonpath='{.items[0].metadata.name}') && \
echo "Pod: $POD_NAME" && \

# Crear script de diagnóstico
cat > diagnostico_db.php << 'EOF'
<?php
require_once('config.php');
echo "=== Diagnóstico de Base de Datos ===\n";
try {
    $DB = new mysqli($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $CFG->dbname);
    if ($DB->connect_error) {
        echo "❌ Error: " . $DB->connect_error . "\n";
    } else {
        echo "✅ Conexión exitosa\n";
        $result = $DB->query("SELECT COUNT(*) as count FROM mdl_config");
        $count = $result->fetch_assoc()['count'];
        echo "📊 Registros: " . $count . "\n";
        
        $test = $DB->query("INSERT INTO mdl_config (name, value) VALUES ('test_write', 'test') ON DUPLICATE KEY UPDATE value = 'test'");
        if ($test) {
            echo "✅ Escritura: OK\n";
            $DB->query("DELETE FROM mdl_config WHERE name = 'test_write'");
        } else {
            echo "❌ Error escritura: " . $DB->error . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
EOF

# Copiar y ejecutar
kubectl cp diagnostico_db.php $POD_NAME:/opt/bitnami/moodle/ -n moodle && \
kubectl exec -it $POD_NAME -n moodle -- bash -c "cd /opt/bitnami/moodle/ && php diagnostico_db.php"
```

## 🔍 Resultados Esperados

### Diagnóstico Exitoso:
```
=== Diagnóstico de Base de Datos ===
Tipo de DB: mysqli
Host: moodle-mysql.moodle-db.svc.cluster.local
Base de datos: moodle
Usuario: moodle_user
✅ Conexión exitosa
✅ Tabla mdl_config existe
📊 Registros en mdl_config: 250
✅ Permisos de escritura: OK
```

### Si hay Error de Permisos:
```
❌ Error de escritura: Access denied for user 'moodle_user'@'%' to table 'mdl_config'
```

## 🔧 Troubleshooting

### Si el diagnóstico falla:
1. Verificar que el pod esté ejecutando
2. Confirmar que los archivos se copiaron correctamente
3. Revisar logs del pod MySQL

### Si los permisos fallan:
1. Conectar directamente a MySQL para verificar permisos
2. Ejecutar script de reparación de permisos
3. Reiniciar el pod si es necesario

## 📝 Próximos Pasos

Una vez que el diagnóstico muestre "✅ Permisos de escritura: OK", continúa con los scripts OAuth originales que ya funcionaron parcialmente.

## ⚠️ Nota Importante

El script `set_google_credentials.php` necesitará las credenciales reales de Google OAuth antes de ejecutarse. Asegúrate de editarlo con tus credenciales de Google Cloud Console.