# 🔧 Instalación y Configuración del Entorno

## 📋 Entorno de Desarrollo Local

### 1. Configuración del Entorno Local

**Requisitos**
```bash
# Instalar Docker y Docker Compose
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Instalar Node.js (para desarrollo frontend)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Instalar PHP y Composer
sudo apt-get install php8.1 php8.1-cli php8.1-common php8.1-mysql php8.1-zip php8.1-gd php8.1-mbstring php8.1-curl php8.1-xml php8.1-bcmath
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

**Docker Compose para Desarrollo**
```yaml
# docker-compose.dev.yml
version: '3.8'
services:
  moodle:
    image: moodle/moodle:latest
    ports:
      - "8080:80"
    environment:
      - MOODLE_DATABASE_HOST=db
      - MOODLE_DATABASE_NAME=moodle
      - MOODLE_DATABASE_USER=moodle
      - MOODLE_DATABASE_PASSWORD=moodle
    volumes:
      - ./moodle-data:/var/www/html
      - ./themes:/var/www/html/theme
      - ./plugins:/var/www/html/local
    depends_on:
      - db
      - redis

  db:
    image: mysql:8.0
    environment:
      - MYSQL_ROOT_PASSWORD=root
      - MYSQL_DATABASE=moodle
      - MYSQL_USER=moodle
      - MYSQL_PASSWORD=moodle
    volumes:
      - db-data:/var/lib/mysql
    ports:
      - "3306:3306"

  redis:
    image: redis:7
    ports:
      - "6379:6379"

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    environment:
      - PMA_HOST=db
      - PMA_USER=root
      - PMA_PASSWORD=root
    ports:
      - "8081:80"
    depends_on:
      - db

volumes:
  db-data:
```

### 2. Configuración de Moodle para Desarrollo

**config.php personalizado**
```php
<?php
// config.php
$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'moodle';
$CFG->dbpass    = 'moodle';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
    'dbpersist' => 0,
    'dbport' => 3306,
    'dbsocket' => '',
    'dbcollation' => 'utf8mb4_unicode_ci',
);

$CFG->wwwroot   = 'http://localhost:8080';
$CFG->dataroot  = '/var/www/moodledata';
$CFG->admin     = 'admin';

// Configuración para desarrollo
$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;
$CFG->passwordpolicy = 0;
$CFG->cachejs = false;
$CFG->themedesignermode = true;
$CFG->langstringcache = false;

// Configuración de caché con Redis
$CFG->session_handler_class = '\core\session\redis';
$CFG->session_redis_host = 'redis';
$CFG->session_redis_port = 6379;
$CFG->session_redis_database = 0;
$CFG->session_redis_prefix = 'moodle_session_';
$CFG->session_redis_acquire_lock_timeout = 120;
$CFG->session_redis_lock_expire = 7200;

// Configuración de email para desarrollo
$CFG->smtphosts = 'localhost:1025';
$CFG->smtpuser = '';
$CFG->smtppass = '';
$CFG->smtpmaxbulk = 1;
$CFG->noemailever = false;

require_once(__DIR__ . '/lib/setup.php');
```

### 3. Estructura de Directorios

```bash
# Crear estructura de proyecto
mkdir -p moodle-medico/{themes,plugins,scripts,docs,tests}
mkdir -p moodle-medico/themes/{medico_theme,mobile_theme}
mkdir -p moodle-medico/plugins/{local,mod,block,auth,enrol}
mkdir -p moodle-medico/scripts/{deployment,backup,monitoring}
mkdir -p moodle-medico/docs/{api,development,user}
mkdir -p moodle-medico/tests/{unit,integration,e2e}
```

### 4. Configuración de GCP para Desarrollo

**Configurar kubectl para tu clúster**
```bash
# Conectar con tu clúster existente
gcloud container clusters get-credentials moodle-cluster --zone us-central1-c --project moodle-gcp-test

# Crear namespace para desarrollo
kubectl create namespace moodle-dev

# Configurar context
kubectl config set-context moodle-dev --cluster=gke_moodle-gcp-test_us-central1-c_moodle-cluster --namespace=moodle-dev --user=gke_moodle-gcp-test_us-central1-c_moodle-cluster
kubectl config use-context moodle-dev
```

**Deployment para desarrollo**
```yaml
# k8s/dev-deployment.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: moodle-dev
  namespace: moodle-dev
spec:
  replicas: 1
  selector:
    matchLabels:
      app: moodle-dev
  template:
    metadata:
      labels:
        app: moodle-dev
    spec:
      containers:
      - name: moodle
        image: moodle/moodle:latest
        ports:
        - containerPort: 80
        env:
        - name: MOODLE_DATABASE_HOST
          value: "moodle-mysql.moodle-db.svc.cluster.local"
        - name: MOODLE_DATABASE_NAME
          value: "moodle"
        - name: MOODLE_DATABASE_USER
          value: "moodle_user"
        - name: MOODLE_DATABASE_PASSWORD
          value: "MoodleUser2025!"
        - name: MOODLE_DEBUG
          value: "true"
        volumeMounts:
        - name: moodle-data
          mountPath: /var/www/html
        - name: custom-config
          mountPath: /var/www/html/config.php
          subPath: config.php
      volumes:
      - name: moodle-data
        persistentVolumeClaim:
          claimName: moodle-dev-pvc
      - name: custom-config
        configMap:
          name: moodle-dev-config
```

### 5. Herramientas de Desarrollo

**VS Code Extensions**
```json
{
  "recommendations": [
    "ms-vscode.vscode-typescript-next",
    "ms-python.python",
    "ms-vscode.vscode-json",
    "bradlc.vscode-tailwindcss",
    "formulahendry.auto-rename-tag",
    "ms-kubernetes-tools.vscode-kubernetes-tools",
    "ms-azuretools.vscode-docker",
    "bmewburn.vscode-intelephense-client",
    "xdebug.php-debug"
  ]
}
```

**Scripts de Desarrollo**
```bash
#!/bin/bash
# scripts/dev-setup.sh

# Instalar dependencias
composer install
npm install

# Configurar base de datos
php admin/cli/install_database.php --agree-license --fullname="Moodle Médico Dev" --shortname="moodle-dev" --adminuser="admin" --adminpass="Admin123!" --adminemail="admin@example.com"

# Habilitar modo desarrollador
php admin/cli/cfg.php --name=debug --set=32767
php admin/cli/cfg.php --name=debugdisplay --set=1
php admin/cli/cfg.php --name=themedesignermode --set=1

# Instalar plugins personalizados
php admin/cli/install_plugins.php --plugins=local_medical_dashboard,mod_medical_simulation

echo "Entorno de desarrollo configurado correctamente"
```

### 6. Configuración de Base de Datos

**Esquema adicional para funcionalidades médicas**
```sql
-- Tablas para funcionalidades médicas
CREATE TABLE mdl_medical_specialties (
    id BIGINT(10) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(100),
    color VARCHAR(7),
    required_hours INT(11) DEFAULT 0,
    PRIMARY KEY (id)
);

CREATE TABLE mdl_medical_learning_paths (
    id BIGINT(10) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    specialty_id BIGINT(10) NOT NULL,
    description TEXT,
    total_hours INT(11) NOT NULL,
    difficulty_level TINYINT(1) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at BIGINT(10) NOT NULL,
    updated_at BIGINT(10) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (specialty_id) REFERENCES mdl_medical_specialties(id)
);

CREATE TABLE mdl_medical_certifications (
    id BIGINT(10) NOT NULL AUTO_INCREMENT,
    user_id BIGINT(10) NOT NULL,
    specialty_id BIGINT(10) NOT NULL,
    certification_name VARCHAR(255) NOT NULL,
    issuing_body VARCHAR(255),
    issue_date BIGINT(10) NOT NULL,
    expiry_date BIGINT(10),
    certificate_url VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES mdl_user(id),
    FOREIGN KEY (specialty_id) REFERENCES mdl_medical_specialties(id)
);

CREATE TABLE mdl_medical_user_progress (
    id BIGINT(10) NOT NULL AUTO_INCREMENT,
    user_id BIGINT(10) NOT NULL,
    learning_path_id BIGINT(10) NOT NULL,
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    hours_completed INT(11) DEFAULT 0,
    last_activity BIGINT(10),
    started_at BIGINT(10) NOT NULL,
    completed_at BIGINT(10),
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES mdl_user(id),
    FOREIGN KEY (learning_path_id) REFERENCES mdl_medical_learning_paths(id)
);
```

### 7. Configuración de Monitoreo

**Prometheus y Grafana**
```yaml
# k8s/monitoring.yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: prometheus-config
  namespace: moodle-dev
data:
  prometheus.yml: |
    global:
      scrape_interval: 15s
    scrape_configs:
    - job_name: 'moodle'
      static_configs:
      - targets: ['moodle-dev:80']
    - job_name: 'mysql'
      static_configs:
      - targets: ['moodle-mysql.moodle-db.svc.cluster.local:3306']
---
apiVersion: apps/v1
kind: Deployment
metadata:
  name: prometheus
  namespace: moodle-dev
spec:
  replicas: 1
  selector:
    matchLabels:
      app: prometheus
  template:
    metadata:
      labels:
        app: prometheus
    spec:
      containers:
      - name: prometheus
        image: prom/prometheus:latest
        ports:
        - containerPort: 9090
        volumeMounts:
        - name: prometheus-config
          mountPath: /etc/prometheus
      volumes:
      - name: prometheus-config
        configMap:
          name: prometheus-config
```

### 8. Scripts de Automatización

**Backup automático**
```bash
#!/bin/bash
# scripts/backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/tmp/moodle_backups"
mkdir -p $BACKUP_DIR

# Backup de base de datos
kubectl exec -n moodle-db moodle-mysql-0 -- mysqldump -u moodle_user -pMoodleUser2025! moodle > $BACKUP_DIR/moodle_db_$DATE.sql

# Backup de archivos
kubectl exec -n moodle deployment/moodle -- tar -czf /tmp/moodle_files_$DATE.tar.gz /var/www/html
kubectl cp moodle/$(kubectl get pods -n moodle -l app=moodle -o jsonpath='{.items[0].metadata.name}'):/tmp/moodle_files_$DATE.tar.gz $BACKUP_DIR/

# Subir a Google Cloud Storage
gsutil cp $BACKUP_DIR/* gs://moodle-backups/

echo "Backup completado: $DATE"
```

### 9. Testing

**PHPUnit para plugins**
```php
<?php
// tests/local/medical_dashboard/medical_dashboard_test.php
class medical_dashboard_test extends advanced_testcase {
    
    public function setUp(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
    }
    
    public function test_dashboard_creation() {
        global $DB;
        
        // Crear especialidad de prueba
        $specialty = new stdClass();
        $specialty->name = 'Cardiología';
        $specialty->code = 'CARD';
        $specialty->description = 'Especialidad en cardiología';
        $specialty_id = $DB->insert_record('medical_specialties', $specialty);
        
        $this->assertNotEmpty($specialty_id);
        
        // Verificar que se puede recuperar
        $retrieved = $DB->get_record('medical_specialties', ['id' => $specialty_id]);
        $this->assertEquals('Cardiología', $retrieved->name);
    }
}
```

### 10. Configuración de CI/CD

**GitHub Actions**
```yaml
# .github/workflows/ci.yml
name: CI/CD Pipeline
on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: moodle_test
        ports:
          - 3306:3306
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: mysqli, gd, zip, mbstring
    
    - name: Install dependencies
      run: composer install
    
    - name: Run PHPUnit tests
      run: vendor/bin/phpunit tests/
    
    - name: Run code quality checks
      run: vendor/bin/phpcs --standard=moodle plugins/

  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup Google Cloud SDK
      uses: google-github-actions/setup-gcloud@v0
      with:
        service_account_key: ${{ secrets.GCP_SA_KEY }}
        project_id: moodle-gcp-test
    
    - name: Deploy to GKE
      run: |
        gcloud container clusters get-credentials moodle-cluster --zone us-central1-c
        kubectl set image deployment/moodle moodle=gcr.io/moodle-gcp-test/moodle:${{ github.sha }}
```

---

## ✅ Checklist de Configuración

- [ ] Entorno Docker local funcionando
- [ ] Conexión a clúster GKE configurada
- [ ] Base de datos con esquema médico
- [ ] Herramientas de desarrollo instaladas
- [ ] Scripts de automatización creados
- [ ] Monitoreo configurado
- [ ] Pipeline CI/CD funcionando
- [ ] Tests básicos ejecutándose

---

**Próximo paso**: Desarrollo del tema médico personalizado