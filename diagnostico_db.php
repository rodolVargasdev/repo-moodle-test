<?php
require_once('config.php');

echo "=== Diagnóstico de Base de Datos ===\n";
echo "Tipo de DB: " . $CFG->dbtype . "\n";
echo "Host: " . $CFG->dbhost . "\n";
echo "Base de datos: " . $CFG->dbname . "\n";
echo "Usuario: " . $CFG->dbuser . "\n";

try {
    $DB = new mysqli($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $CFG->dbname);
    
    if ($DB->connect_error) {
        echo "❌ Error de conexión: " . $DB->connect_error . "\n";
    } else {
        echo "✅ Conexión exitosa\n";
        
        // Verificar tabla mdl_config
        $result = $DB->query("SHOW TABLES LIKE 'mdl_config'");
        if ($result->num_rows > 0) {
            echo "✅ Tabla mdl_config existe\n";
            
            // Contar registros
            $count_result = $DB->query("SELECT COUNT(*) as count FROM mdl_config");
            $count = $count_result->fetch_assoc()['count'];
            echo "📊 Registros en mdl_config: " . $count . "\n";
            
            // Verificar permisos de escritura
            $test_query = "INSERT INTO mdl_config (name, value) VALUES ('test_write_permission', 'test_value') ON DUPLICATE KEY UPDATE value = 'test_value'";
            $write_result = $DB->query($test_query);
            
            if ($write_result) {
                echo "✅ Permisos de escritura: OK\n";
                // Limpiar el test
                $DB->query("DELETE FROM mdl_config WHERE name = 'test_write_permission'");
            } else {
                echo "❌ Error de escritura: " . $DB->error . "\n";
            }
            
        } else {
            echo "❌ Tabla mdl_config no encontrada\n";
        }
        
        // Verificar permisos del usuario
        $result = $DB->query("SHOW GRANTS FOR CURRENT_USER()");
        echo "\n=== Permisos del Usuario ===\n";
        while ($row = $result->fetch_assoc()) {
            foreach ($row as $key => $value) {
                echo $value . "\n";
            }
        }
        
        $DB->close();
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>