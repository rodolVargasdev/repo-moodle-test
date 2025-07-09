<?php
require_once('config.php');

echo "=== Reparación de Permisos de Base de Datos ===\n";

try {
    // Conectar como usuario normal de Moodle
    $DB = new mysqli($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $CFG->dbname);
    
    if ($DB->connect_error) {
        echo "❌ Error de conexión con usuario Moodle: " . $DB->connect_error . "\n";
        echo "Intentando con usuario root...\n";
        
        // Intentar con root para reparar permisos
        $root_password = 'Moodle2025!'; // Contraseña root según tu configuración
        $DB_ROOT = new mysqli($CFG->dbhost, 'root', $root_password, $CFG->dbname);
        
        if ($DB_ROOT->connect_error) {
            echo "❌ Error de conexión con root: " . $DB_ROOT->connect_error . "\n";
            die("No se puede conectar a la base de datos\n");
        }
        
        echo "✅ Conexión con root exitosa\n";
        
        // Otorgar permisos completos al usuario de Moodle
        $grant_query = "GRANT ALL PRIVILEGES ON " . $CFG->dbname . ".* TO '" . $CFG->dbuser . "'@'%'";
        $result = $DB_ROOT->query($grant_query);
        
        if ($result) {
            echo "✅ Permisos otorgados a " . $CFG->dbuser . "\n";
        } else {
            echo "❌ Error otorgando permisos: " . $DB_ROOT->error . "\n";
        }
        
        // Flush privileges
        $DB_ROOT->query("FLUSH PRIVILEGES");
        echo "✅ Privilegios actualizados\n";
        
        // Mostrar permisos otorgados
        $show_grants = $DB_ROOT->query("SHOW GRANTS FOR '" . $CFG->dbuser . "'@'%'");
        echo "\n=== Permisos Actuales ===\n";
        while ($row = $show_grants->fetch_assoc()) {
            foreach ($row as $key => $value) {
                echo $value . "\n";
            }
        }
        
        $DB_ROOT->close();
        
        // Probar conexión nuevamente
        $DB = new mysqli($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $CFG->dbname);
        if ($DB->connect_error) {
            echo "❌ Aún hay problemas de conexión: " . $DB->connect_error . "\n";
        } else {
            echo "✅ Conexión reparada exitosamente\n";
        }
        
    } else {
        echo "✅ Conexión exitosa con usuario Moodle\n";
    }
    
    // Verificar permisos de escritura
    if (!$DB->connect_error) {
        $test_query = "INSERT INTO mdl_config (name, value) VALUES ('test_repair_permission', 'test_value') ON DUPLICATE KEY UPDATE value = 'test_value'";
        $write_result = $DB->query($test_query);
        
        if ($write_result) {
            echo "✅ Test de escritura: OK\n";
            // Limpiar el test
            $DB->query("DELETE FROM mdl_config WHERE name = 'test_repair_permission'");
        } else {
            echo "❌ Error de escritura: " . $DB->error . "\n";
        }
        
        $DB->close();
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>