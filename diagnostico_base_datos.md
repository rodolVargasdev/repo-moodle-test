# 🔍 Diagnóstico: Error de Base de Datos en Moodle

## ✅ Progreso Positivo
- Scripts descargados correctamente
- Archivos copiados al pod exitosamente
- Configuraciones OAuth 2.0 parcialmente aplicadas:
  - Dominios permitidos: `telesalud.gob.sv`, `goes.gob.sv`
  - Prevención de creación automática de cuentas habilitada

## ❌ Problema Identificado
**Error**: `!!! Error writing to database !!!`
**Causa**: Problemas con permisos o conexión a la base de datos

## 🔧 Pasos de Diagnóstico

### 1. Verificar Conexión a la Base de Datos

```bash
POD_NAME=$(kubectl get pods -n moodle -o jsonpath='{.items[0].metadata.name}')

# Verificar configuración de base de datos
kubectl exec -it $POD_NAME -n moodle -- bash -c "
cd /opt/bitnami/moodle/ && \
head -30 config.php | grep -E '(dbtype|dbhost|dbname|dbuser|dbpass)'
"
```

### 2. Probar Conexión PHP a la Base de Datos

```bash
# Crear script de diagnóstico
kubectl exec -it $POD_NAME -n moodle -- bash -c "
cd /opt/bitnami/moodle/ && \
cat > test_db_connection.php << 'EOF'
<?php
require_once('config.php');

echo \"=== Diagnóstico de Base de Datos ===\\n\";
echo \"Tipo de DB: \" . \$CFG->dbtype . \"\\n\";
echo \"Host: \" . \$CFG->dbhost . \"\\n\";
echo \"Base de datos: \" . \$CFG->dbname . \"\\n\";
echo \"Usuario: \" . \$CFG->dbuser . \"\\n\";

try {
    \$DB = new mysqli(\$CFG->dbhost, \$CFG->dbuser, \$CFG->dbpass, \$CFG->dbname);
    
    if (\$DB->connect_error) {
        echo \"❌ Error de conexión: \" . \$DB->connect_error . \"\\n\";
    } else {
        echo \"✅ Conexión exitosa\\n\";
        
        // Verificar permisos
        \$result = \$DB->query(\"SHOW GRANTS FOR CURRENT_USER()\");
        echo \"\\n=== Permisos del Usuario ===\\n\";
        while (\$row = \$result->fetch_assoc()) {
            echo \$row['Grants for ' . \$CFG->dbuser . '@%'] . \"\\n\";
        }
        
        // Verificar tablas de configuración
        \$result = \$DB->query(\"SHOW TABLES LIKE 'mdl_config'\");
        if (\$result->num_rows > 0) {
            echo \"\\n✅ Tabla mdl_config existe\\n\";
            
            // Verificar si podemos escribir
            \$test_query = \"SELECT * FROM mdl_config WHERE name = 'oauth2_test' LIMIT 1\";
            \$result = \$DB->query(\$test_query);
            echo \"✅ Lectura de configuración exitosa\\n\";
            
        } else {
            echo \"\\n❌ Tabla mdl_config no encontrada\\n\";
        }
        
        \$DB->close();
    }
    
} catch (Exception \$e) {
    echo \"❌ Error: \" . \$e->getMessage() . \"\\n\";
}
EOF

php test_db_connection.php
"
```

### 3. Verificar Estado de la Base de Datos MySQL

```bash
# Verificar pods de base de datos
kubectl get pods -n moodle-db

# Verificar logs de MySQL
kubectl logs -n moodle-db $(kubectl get pods -n moodle-db -o jsonpath='{.items[0].metadata.name}')

# Conectar a MySQL directamente
kubectl exec -it $(kubectl get pods -n moodle-db -o jsonpath='{.items[0].metadata.name}') -n moodle-db -- mysql -u root -p
```

### 4. Verificar Permisos de Usuario de Base de Datos

```bash
# Conectar a MySQL y verificar permisos
kubectl exec -it $(kubectl get pods -n moodle-db -o jsonpath='{.items[0].metadata.name}') -n moodle-db -- mysql -u root -p -e "
SHOW GRANTS FOR 'moodle_user'@'%';
SELECT User, Host, Select_priv, Insert_priv, Update_priv, Delete_priv FROM mysql.user WHERE User='moodle_user';
"
```

### 5. Verificar Tablas de Configuración de Moodle

```bash
kubectl exec -it $POD_NAME -n moodle -- bash -c "
cd /opt/bitnami/moodle/ && \
cat > check_moodle_tables.php << 'EOF'
<?php
require_once('config.php');

try {
    \$DB = new mysqli(\$CFG->dbhost, \$CFG->dbuser, \$CFG->dbpass, \$CFG->dbname);
    
    if (\$DB->connect_error) {
        die(\"Error de conexión: \" . \$DB->connect_error);
    }
    
    // Verificar tablas importantes
    \$tables = ['mdl_config', 'mdl_config_plugins', 'mdl_user', 'mdl_oauth2_issuer'];
    
    foreach (\$tables as \$table) {
        \$result = \$DB->query(\"SHOW TABLES LIKE '\" . \$table . \"'\");
        if (\$result->num_rows > 0) {
            echo \"✅ Tabla \$table existe\\n\";
            
            // Contar registros
            \$count_result = \$DB->query(\"SELECT COUNT(*) as count FROM \$table\");
            \$count = \$count_result->fetch_assoc()['count'];
            echo \"   - Registros: \$count\\n\";
            
        } else {
            echo \"❌ Tabla \$table no existe\\n\";
        }
    }
    
    \$DB->close();
    
} catch (Exception \$e) {
    echo \"❌ Error: \" . \$e->getMessage() . \"\\n\";
}
EOF

php check_moodle_tables.php
"
```

## 🔧 Soluciones Posibles

### Solución 1: Reparar Permisos de Base de Datos

```bash
# Conectar a MySQL y otorgar permisos completos
kubectl exec -it $(kubectl get pods -n moodle-db -o jsonpath='{.items[0].metadata.name}') -n moodle-db -- mysql -u root -p -e "
GRANT ALL PRIVILEGES ON moodle.* TO 'moodle_user'@'%';
FLUSH PRIVILEGES;
SHOW GRANTS FOR 'moodle_user'@'%';
"
```

### Solución 2: Verificar y Reparar Configuración

```bash
# Verificar archivo de configuración
kubectl exec -it $POD_NAME -n moodle -- bash -c "
cd /opt/bitnami/moodle/ && \
grep -A 10 -B 10 'dbtype\|dbhost\|dbname\|dbuser' config.php
"
```

### Solución 3: Ejecutar Scripts con Modo Debug

```bash
# Ejecutar script con más información de debug
kubectl exec -it $POD_NAME -n moodle -- bash -c "
cd /opt/bitnami/moodle/ && \
php -d display_errors=1 -d error_reporting=E_ALL setup_google_oauth_telesalud.php
"
```

### Solución 4: Método Manual de Configuración

```bash
# Si los scripts fallan, configurar manualmente
kubectl exec -it $POD_NAME -n moodle -- bash -c "
cd /opt/bitnami/moodle/ && \
cat > manual_oauth_config.php << 'EOF'
<?php
require_once('config.php');

// Configurar OAuth 2.0 manualmente
echo \"Configurando OAuth 2.0 manualmente...\\n\";

try {
    \$DB = new mysqli(\$CFG->dbhost, \$CFG->dbuser, \$CFG->dbpass, \$CFG->dbname);
    
    if (\$DB->connect_error) {
        die(\"Error de conexión: \" . \$DB->connect_error);
    }
    
    // Verificar si ya existe configuración OAuth
    \$result = \$DB->query(\"SELECT * FROM mdl_config WHERE name LIKE 'oauth2%'\");
    echo \"Configuraciones OAuth existentes: \" . \$result->num_rows . \"\\n\";
    
    // Insertar configuración básica
    \$configs = [
        ['name' => 'oauth2_google_enabled', 'value' => '1'],
        ['name' => 'oauth2_google_clientid', 'value' => 'TU_CLIENT_ID'],
        ['name' => 'oauth2_google_clientsecret', 'value' => 'TU_CLIENT_SECRET']
    ];
    
    foreach (\$configs as \$config) {
        \$stmt = \$DB->prepare(\"INSERT INTO mdl_config (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)\");
        \$stmt->bind_param('ss', \$config['name'], \$config['value']);
        \$result = \$stmt->execute();
        
        if (\$result) {
            echo \"✅ Configuración \" . \$config['name'] . \" guardada\\n\";
        } else {
            echo \"❌ Error guardando \" . \$config['name'] . \": \" . \$DB->error . \"\\n\";
        }
    }
    
    \$DB->close();
    
} catch (Exception \$e) {
    echo \"❌ Error: \" . \$e->getMessage() . \"\\n\";
}
EOF

php manual_oauth_config.php
"
```

## 🚀 Comando de Diagnóstico Rápido

```bash
# Ejecutar diagnóstico completo
POD_NAME=$(kubectl get pods -n moodle -o jsonpath='{.items[0].metadata.name}') && \
echo "=== Diagnóstico de Base de Datos ===" && \
kubectl exec -it $POD_NAME -n moodle -- bash -c "
cd /opt/bitnami/moodle/ && \
echo '1. Verificando conexión a BD...' && \
php -r \"
require_once('config.php');
try {
    \$DB = new mysqli(\$CFG->dbhost, \$CFG->dbuser, \$CFG->dbpass, \$CFG->dbname);
    if (\$DB->connect_error) {
        echo 'Error: ' . \$DB->connect_error . PHP_EOL;
    } else {
        echo 'Conexión exitosa' . PHP_EOL;
        \$result = \$DB->query('SELECT COUNT(*) as count FROM mdl_config');
        \$count = \$result->fetch_assoc()['count'];
        echo 'Registros en mdl_config: ' . \$count . PHP_EOL;
    }
} catch (Exception \$e) {
    echo 'Error: ' . \$e->getMessage() . PHP_EOL;
}
\"
"
```

## 📝 Siguiente Paso

Ejecuta el **comando de diagnóstico rápido** y comparte el resultado para determinar la causa exacta del problema.