#!/bin/bash

# 📤 Script para subir cambios a GitHub
# Este script organiza todos los archivos creados y los prepara para GitHub

set -e

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_step() {
    echo -e "${BLUE}📤 $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

echo -e "${BLUE}
╔══════════════════════════════════════════════════════════════════════════════╗
║                    📤 SUBIR CAMBIOS A GITHUB                                ║
║                                                                              ║
║  Este script organiza todos los archivos del curso de Telemedicina          ║
║  y los prepara para ser subidos a GitHub                                    ║
╚══════════════════════════════════════════════════════════════════════════════╝
${NC}"

# Verificar si estamos en un repositorio git
if [ ! -d ".git" ]; then
    print_warning "No estás en un repositorio git. Inicializando..."
    git init
    print_success "Repositorio git inicializado"
fi

# Verificar configuración de git
print_step "Verificando configuración de git..."
if ! git config user.name > /dev/null 2>&1; then
    print_warning "Configuración de git no encontrada. Configurando..."
    echo "Ingresa tu nombre para git:"
    read -r GIT_NAME
    echo "Ingresa tu email para git:"
    read -r GIT_EMAIL
    git config user.name "$GIT_NAME"
    git config user.email "$GIT_EMAIL"
    print_success "Configuración de git establecida"
fi

# Crear estructura de directorios para GitHub
print_step "Organizando archivos para GitHub..."

# Crear directorios si no existen
mkdir -p {scripts,docs,config,guides}

# Organizar archivos PHP
if [ -f "setup_complete_telemedicina_course.php" ]; then
    mv setup_complete_telemedicina_course.php scripts/
fi

if [ -f "create_telemedicina_course_structure.php" ]; then
    mv create_telemedicina_course_structure.php scripts/
fi

if [ -f "add_detailed_content_telemedicina.php" ]; then
    mv add_detailed_content_telemedicina.php scripts/
fi

if [ -f "setup_google_oauth_telesalud.php" ]; then
    mv setup_google_oauth_telesalud.php scripts/
fi

if [ -f "enroll_users_telemedicina.php" ]; then
    mv enroll_users_telemedicina.php scripts/
fi

if [ -f "verify_oauth_setup.php" ]; then
    mv verify_oauth_setup.php scripts/
fi

if [ -f "create_specific_users_telemedicina.php" ]; then
    mv create_specific_users_telemedicina.php scripts/
fi

# Mover archivos de despliegue
if [ -f "deploy_telemedicina_complete.sh" ]; then
    mv deploy_telemedicina_complete.sh scripts/
fi

# Mover guías
if [ -f "GUIA_DEPLOYMENT_GCP_CLOUDSHELL.md" ]; then
    mv GUIA_DEPLOYMENT_GCP_CLOUDSHELL.md guides/
fi

if [ -f "COMANDOS_GCLOUD_TELEMEDICINA.md" ]; then
    mv COMANDOS_GCLOUD_TELEMEDICINA.md guides/
fi

# Crear .gitignore si no existe
if [ ! -f ".gitignore" ]; then
    cat > .gitignore << 'EOF'
# Archivos temporales
*.tmp
*.temp
temp/
.DS_Store
Thumbs.db

# Archivos de configuración sensibles
config/credentials.php
config/oauth_secrets.php
*.pem
*.key

# Logs
*.log
logs/

# Archivos de Cloud Shell
.cloudshell/

# Archivos de backup
backup_*.sql
*.tar.gz
*.zip

# Archivos específicos de Moodle
/bitnami/
/var/
moodledata/

# IDE
.vscode/
.idea/
*.swp
*.swo
*~

# Node modules (si se usan)
node_modules/
npm-debug.log*

# Python
__pycache__/
*.py[cod]
*$py.class
EOF
    print_success ".gitignore creado"
fi

# Actualizar README.md
print_step "Actualizando README.md..."
cat > README.md << 'EOF'
# 🏥 Moodle Telemedicina - Curso de Nivelación

## 📋 Descripción del Proyecto

Este repositorio contiene el **Curso Básico de Nivelación en Telemedicina** para el personal médico, desplegado en Google Cloud Platform usando Kubernetes (GKE).

## 🎯 Objetivo

Fortalecer conocimientos en telemedicina y herramientas digitales para personal médico de:
- 🏥 @telesalud.gob.sv
- 🏛️ @goes.gob.sv

## 📚 Estructura del Curso

### Módulos del Curso
```
📚 Módulo A - Habilidades Tecnológicas (5 actividades)
├── Bienvenida y normas
├── Introducción a Chrome OS
├── Configuración de Chromebook
├── Google Chat y Meet
└── Organización en Google Drive

📚 Módulo B - Buenas Prácticas Digitales (2 actividades)
├── Portal de Práctica Médica
└── Casos reales de inconvenientes

📚 Módulo C - Aplicaciones Médicas (2 actividades)
├── Descarga de App Dr. ISSS
└── Navegación en la aplicación

📚 Módulo D - Evaluación Final (2 actividades)
├── Foro para consultas
└── Evaluación integral
```

## 🏗️ Arquitectura Técnica

- **Plataforma**: Google Cloud Platform (GCP)
- **Orquestación**: Google Kubernetes Engine (GKE)
- **Base de datos**: MySQL/MariaDB
- **Autenticación**: OAuth 2.0 con Google
- **Dominio**: Restringido a organizaciones específicas

## 🚀 Despliegue Rápido

### Opción 1: Despliegue Automático (Recomendado)
```bash
# En Google Cloud Shell
chmod +x scripts/deploy_telemedicina_complete.sh
./scripts/deploy_telemedicina_complete.sh
```

### Opción 2: Despliegue Manual
```bash
# Conectar al cluster
gcloud container clusters get-credentials moodle-cluster --zone=us-central1-c --project=moodle-gcp-test

# Ejecutar scripts paso a paso
kubectl exec -it MOODLE_POD -- php /bitnami/moodle/scripts/setup_complete_telemedicina_course.php
```

## 📁 Estructura del Repositorio

```
📦 moodle-telemedicina/
├── 📁 scripts/                    # Scripts PHP y de despliegue
│   ├── setup_complete_telemedicina_course.php
│   ├── create_telemedicina_course_structure.php
│   ├── add_detailed_content_telemedicina.php
│   ├── setup_google_oauth_telesalud.php
│   ├── enroll_users_telemedicina.php
│   └── deploy_telemedicina_complete.sh
├── 📁 guides/                     # Guías de uso
│   ├── GUIA_DEPLOYMENT_GCP_CLOUDSHELL.md
│   └── COMANDOS_GCLOUD_TELEMEDICINA.md
├── 📁 docs/                       # Documentación técnica
│   ├── 01_instalacion_y_configuracion.md
│   ├── 02_desarrollo_themes.md
│   ├── 03_plugins_personalizados.md
│   └── 04_apis_integraciones.md
├── 📁 config/                     # Archivos de configuración
└── 📄 README.md                   # Este archivo
```

## 🔧 Configuración Inicial

### Prerrequisitos
- [x] Cuenta de Google Cloud Platform
- [x] Proyecto: `moodle-gcp-test`
- [x] Cluster GKE: `moodle-cluster`
- [x] Zona: `us-central1-c`
- [x] Moodle desplegado y funcionando

### Variables de Entorno
```bash
export PROJECT_ID="moodle-gcp-test"
export CLUSTER_NAME="moodle-cluster"
export ZONE="us-central1-c"
export NAMESPACE="moodle"
```

## 🔐 Configuración OAuth

### Dominios Permitidos
- `telesalud.gob.sv`
- `goes.gob.sv`

### Configuración en Google Console
1. Ir a: [Google Cloud Console](https://console.developers.google.com/)
2. Seleccionar proyecto: `moodle-gcp-test`
3. Crear credenciales OAuth 2.0
4. URI de redirección: `http://34.72.133.6/admin/oauth2callback.php`

## 🌐 Acceso al Curso

### URLs Importantes
- **Moodle Principal**: http://34.72.133.6
- **Administración**: http://34.72.133.6/admin
- **Curso de Telemedicina**: http://34.72.133.6/course/view.php?id={COURSE_ID}

### Usuarios de Prueba
- `doctor.lopez@telesalud.gob.sv`
- `dra.martinez@goes.gob.sv`
- `dr.rodriguez@telesalud.gob.sv`

## 📊 Comandos Útiles

### Verificación del Sistema
```bash
# Verificar estado del cluster
kubectl get pods -n moodle

# Verificar curso
kubectl exec -it MOODLE_POD -n moodle -- php /bitnami/moodle/scripts/verify_deployment.php

# Ver logs
kubectl logs MOODLE_POD -n moodle --tail=50
```

### Mantenimiento
```bash
# Backup de base de datos
kubectl exec -it moodle-mariadb-0 -n moodle -- mysqldump -u root -p moodle > backup.sql

# Limpiar caché
kubectl exec -it MOODLE_POD -n moodle -- php /bitnami/moodle/admin/cli/purge_caches.php

# Ejecutar cron
kubectl exec -it MOODLE_POD -n moodle -- php /bitnami/moodle/admin/cli/cron.php
```

## 🔄 Flujo de Trabajo

### Desarrollo Local
1. Modificar scripts en `scripts/`
2. Probar en entorno local
3. Subir cambios a GitHub

### Despliegue en GCP
1. Conectar a Cloud Shell
2. Clonar repositorio
3. Ejecutar script de despliegue
4. Verificar funcionamiento

## 📈 Monitoreo y Análisis

### Métricas del Curso
- Usuarios registrados por dominio
- Progreso por módulo
- Actividades completadas
- Tiempo de finalización

### Reportes Disponibles
- Dashboard de progreso
- Estadísticas de uso
- Reportes de finalización
- Analíticas de participación

## 🚨 Troubleshooting

### Problemas Comunes
1. **Error de conexión DB**: Verificar pods de MariaDB
2. **OAuth no funciona**: Revisar credenciales en Google Console
3. **Curso no accesible**: Verificar permisos y visibilidad

### Contacto y Soporte
Para problemas técnicos:
- Revisar logs del pod
- Verificar configuración OAuth
- Consultar documentación en `guides/`

## 🔒 Seguridad

### Medidas Implementadas
- Restricción por dominio de email
- Autenticación OAuth 2.0
- Acceso progresivo a contenido
- Logs de actividad

### Consideraciones
- Cambiar credenciales por defecto
- Configurar backups regulares
- Monitorear accesos no autorizados

## 📝 Changelog

### v1.0.0 (2025-01-07)
- ✅ Curso básico de Telemedicina creado
- ✅ OAuth configurado para dominios específicos
- ✅ Scripts de despliegue automatizados
- ✅ Documentación completa
- ✅ Estructura de 4 módulos implementada

## 🤝 Contribución

### Cómo Contribuir
1. Fork del repositorio
2. Crear rama para feature
3. Hacer cambios y pruebas
4. Enviar pull request

### Estándares de Código
- Comentarios en español
- Scripts bien documentados
- Seguir convenciones de Moodle

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Ver archivo LICENSE para más detalles.

---

**Desarrollado para el fortalecimiento de la Telemedicina en El Salvador** 🇸🇻

*Última actualización: 2025-01-07*
EOF

print_success "README.md actualizado"

# Crear archivo de configuración de ejemplo
cat > config/example_config.php << 'EOF'
<?php
/**
 * Configuración de ejemplo para el curso de Telemedicina
 * Copiar a config.php y personalizar según necesidades
 */

// Configuración de base de datos
define('DB_HOST', 'moodle-mysql.moodle-db.svc.cluster.local');
define('DB_NAME', 'moodle');
define('DB_USER', 'moodle_user');
define('DB_PASS', 'MoodleUser2025!');

// Configuración OAuth
define('OAUTH_CLIENT_ID', 'TU_CLIENT_ID_AQUI');
define('OAUTH_CLIENT_SECRET', 'TU_CLIENT_SECRET_AQUI');
define('ALLOWED_DOMAINS', 'telesalud.gob.sv,goes.gob.sv');

// Configuración del curso
define('COURSE_SHORTNAME', 'CBN-TELEMEDICINA-2025');
define('COURSE_FULLNAME', 'Curso Básico de Nivelación en Telemedicina');
define('COURSE_DURATION_DAYS', 90);

// URLs importantes
define('MOODLE_URL', 'http://34.72.133.6');
define('OAUTH_CALLBACK_URL', 'http://34.72.133.6/admin/oauth2callback.php');
?>
EOF

print_success "Archivo de configuración de ejemplo creado"

# Crear archivo de comandos útiles
cat > scripts/useful_commands.sh << 'EOF'
#!/bin/bash

# 🛠️ Comandos útiles para el curso de Telemedicina

# Configuración
PROJECT_ID="moodle-gcp-test"
CLUSTER_NAME="moodle-cluster"
ZONE="us-central1-c"
NAMESPACE="moodle"

# Conectar al cluster
connect_cluster() {
    echo "🔗 Conectando al cluster GKE..."
    gcloud container clusters get-credentials $CLUSTER_NAME --zone=$ZONE --project=$PROJECT_ID
}

# Obtener pod de Moodle
get_moodle_pod() {
    kubectl get pods -n $NAMESPACE -l app=moodle -o jsonpath='{.items[0].metadata.name}'
}

# Verificar estado del curso
verify_course() {
    echo "🔍 Verificando estado del curso..."
    MOODLE_POD=$(get_moodle_pod)
    kubectl exec -it $MOODLE_POD -n $NAMESPACE -- php /bitnami/moodle/scripts/verify_deployment.php
}

# Ver logs
view_logs() {
    echo "📄 Mostrando logs de Moodle..."
    MOODLE_POD=$(get_moodle_pod)
    kubectl logs $MOODLE_POD -n $NAMESPACE --tail=50
}

# Acceder al pod
access_pod() {
    echo "🚪 Accediendo al pod de Moodle..."
    MOODLE_POD=$(get_moodle_pod)
    kubectl exec -it $MOODLE_POD -n $NAMESPACE -- /bin/bash
}

# Backup de base de datos
backup_db() {
    echo "💾 Realizando backup de base de datos..."
    kubectl exec -it moodle-mariadb-0 -n $NAMESPACE -- mysqldump -u root -p moodle > backup_$(date +%Y%m%d_%H%M%S).sql
    echo "✅ Backup completado"
}

# Mostrar menú
show_menu() {
    echo "🏥 Comandos disponibles para el curso de Telemedicina:"
    echo "1. connect_cluster - Conectar al cluster GKE"
    echo "2. verify_course - Verificar estado del curso"
    echo "3. view_logs - Ver logs de Moodle"
    echo "4. access_pod - Acceder al pod de Moodle"
    echo "5. backup_db - Hacer backup de base de datos"
}

# Ejecutar función si se pasa como argumento
if [ $# -eq 0 ]; then
    show_menu
else
    $1
fi
EOF

chmod +x scripts/useful_commands.sh
print_success "Comandos útiles creados"

# Agregar archivos al staging
print_step "Agregando archivos al staging de git..."
git add .

# Mostrar estado
print_step "Estado actual del repositorio:"
git status

# Crear commit
print_step "Creando commit..."
COMMIT_MESSAGE="🏥 Implementación completa del Curso de Telemedicina

✨ Nuevas funcionalidades:
- Curso estructurado en 4 módulos
- OAuth configurado para dominios específicos
- Scripts de despliegue automatizados
- Documentación completa
- Guías de uso y troubleshooting

📚 Estructura del curso:
- Módulo A: Habilidades Tecnológicas
- Módulo B: Buenas Prácticas Digitales  
- Módulo C: Aplicaciones Médicas
- Módulo D: Evaluación Final

🔧 Archivos incluidos:
- Scripts PHP para configuración automática
- Guías de despliegue en GCP Cloud Shell
- Documentación técnica completa
- Comandos útiles para mantenimiento

🎯 Objetivo: Fortalecer conocimientos en telemedicina para personal médico"

git commit -m "$COMMIT_MESSAGE"
print_success "Commit creado exitosamente"

# Verificar si hay un remote configurado
if ! git remote -v | grep -q origin; then
    print_warning "No hay remote configurado. Agregando remote..."
    echo "Ingresa la URL de tu repositorio GitHub (ej: https://github.com/usuario/repo.git):"
    read -r REPO_URL
    git remote add origin "$REPO_URL"
    print_success "Remote agregado: $REPO_URL"
fi

# Mostrar instrucciones finales
echo -e "${GREEN}
╔══════════════════════════════════════════════════════════════════════════════╗
║                        📤 LISTO PARA SUBIR A GITHUB                        ║
╚══════════════════════════════════════════════════════════════════════════════╝

🎉 Archivos organizados y commit creado exitosamente!

📋 ARCHIVOS INCLUIDOS:
   ├── 📁 scripts/          # Scripts PHP y de despliegue
   ├── 📁 guides/           # Guías de uso
   ├── 📁 docs/             # Documentación técnica
   ├── 📁 config/           # Configuración de ejemplo
   └── 📄 README.md         # Documentación principal

🚀 PARA SUBIR A GITHUB:
   git push origin main
   
   O si es la primera vez:
   git push -u origin main

🔧 COMANDOS ÚTILES DESPUÉS DEL PUSH:
   • ./scripts/useful_commands.sh - Comandos útiles
   • ./scripts/deploy_telemedicina_complete.sh - Despliegue completo
   
📊 ESTADÍSTICAS DEL COMMIT:
   • $(git diff --cached --numstat | wc -l) archivos modificados
   • $(git log --oneline | wc -l) commits en total
   
${NC}"

print_success "¡Listo para subir a GitHub! Ejecuta: git push origin main"