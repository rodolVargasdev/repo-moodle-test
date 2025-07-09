#!/bin/bash

# 🚀 Script de Despliegue Completo - Curso de Telemedicina
# Descripción: Automatiza el despliegue completo del curso de telemedicina en GCP

set -e  # Salir si hay error

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para imprimir mensajes
print_step() {
    echo -e "${BLUE}🔧 $1${NC}"
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

# Configuración
PROJECT_ID="moodle-gcp-test"
CLUSTER_NAME="moodle-cluster"
ZONE="us-central1-c"
NAMESPACE="moodle"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo -e "${BLUE}
╔══════════════════════════════════════════════════════════════════════════════╗
║                🏥 DESPLIEGUE CURSO DE TELEMEDICINA - GCP                    ║
║                                                                              ║
║  Este script automatiza el despliegue completo del curso de telemedicina     ║
║  en Google Cloud Platform usando Cloud Shell                                ║
╚══════════════════════════════════════════════════════════════════════════════╝
${NC}"

# Paso 1: Verificar configuración inicial
print_step "Verificando configuración inicial..."

# Verificar proyecto
CURRENT_PROJECT=$(gcloud config get-value project 2>/dev/null)
if [ "$CURRENT_PROJECT" != "$PROJECT_ID" ]; then
    print_warning "Configurando proyecto: $PROJECT_ID"
    gcloud config set project $PROJECT_ID
fi

# Verificar conexión al cluster
print_step "Conectando al cluster GKE..."
gcloud container clusters get-credentials $CLUSTER_NAME --zone=$ZONE --project=$PROJECT_ID

# Verificar que el cluster esté funcionando
if ! kubectl get nodes > /dev/null 2>&1; then
    print_error "No se puede conectar al cluster GKE"
    exit 1
fi

print_success "Conectado al cluster GKE exitosamente"

# Paso 2: Obtener información del pod de Moodle
print_step "Identificando pod de Moodle..."
MOODLE_POD=$(kubectl get pods -n $NAMESPACE -l app=moodle -o jsonpath='{.items[0].metadata.name}' 2>/dev/null)

if [ -z "$MOODLE_POD" ]; then
    print_error "No se encontró el pod de Moodle"
    print_warning "Verificando pods disponibles..."
    kubectl get pods -n $NAMESPACE
    exit 1
fi

print_success "Pod de Moodle encontrado: $MOODLE_POD"

# Paso 3: Transferir scripts al pod
print_step "Transfiriendo scripts al pod de Moodle..."
kubectl cp $SCRIPT_DIR/ $MOODLE_POD:/bitnami/moodle/scripts/ -n $NAMESPACE

# Verificar que los archivos se copiaron
if kubectl exec -it $MOODLE_POD -n $NAMESPACE -- ls -la /bitnami/moodle/scripts/ > /dev/null 2>&1; then
    print_success "Scripts transferidos exitosamente"
else
    print_error "Error al transferir scripts"
    exit 1
fi

# Paso 4: Ejecutar configuración del curso
print_step "Ejecutando configuración del curso de Telemedicina..."
echo -e "${YELLOW}Esto puede tomar unos minutos...${NC}"

if kubectl exec -it $MOODLE_POD -n $NAMESPACE -- php /bitnami/moodle/scripts/setup_complete_telemedicina_course.php; then
    print_success "Curso configurado exitosamente"
else
    print_error "Error en la configuración del curso"
    exit 1
fi

# Paso 5: Ejecutar verificación
print_step "Ejecutando verificación del despliegue..."
kubectl exec -it $MOODLE_POD -n $NAMESPACE -- php /bitnami/moodle/scripts/verify_oauth_setup.php

# Paso 6: Mostrar información final
print_step "Obteniendo información de acceso..."

# Obtener IP externa
EXTERNAL_IP=$(kubectl get service moodle -n $NAMESPACE -o jsonpath='{.status.loadBalancer.ingress[0].ip}' 2>/dev/null)
if [ -z "$EXTERNAL_IP" ]; then
    EXTERNAL_IP="34.72.133.6"  # IP por defecto
fi

# Obtener ID del curso
COURSE_ID=$(kubectl exec -it $MOODLE_POD -n $NAMESPACE -- php -r "
require_once('/bitnami/moodle/config.php');
\$course = \$DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
if (\$course) echo \$course->id;
" 2>/dev/null | tr -d '\r\n')

echo -e "${GREEN}
╔══════════════════════════════════════════════════════════════════════════════╗
║                           🎉 DESPLIEGUE EXITOSO                             ║
╚══════════════════════════════════════════════════════════════════════════════╝

🔗 ACCESO AL CURSO:
   • URL principal: http://$EXTERNAL_IP
   • Curso directo: http://$EXTERNAL_IP/course/view.php?id=$COURSE_ID
   • Administración: http://$EXTERNAL_IP/admin

👥 USUARIOS DE PRUEBA:
   • doctor.lopez@telesalud.gob.sv
   • dra.martinez@goes.gob.sv
   • dr.rodriguez@telesalud.gob.sv

🔐 CONFIGURACIÓN OAUTH:
   • Proveedor: Google OAuth 2.0
   • Dominios permitidos: telesalud.gob.sv, goes.gob.sv
   • URI de redirección: http://$EXTERNAL_IP/admin/oauth2callback.php

📚 ESTRUCTURA DEL CURSO:
   • Módulo A: Habilidades Tecnológicas
   • Módulo B: Buenas Prácticas Digitales
   • Módulo C: Aplicaciones Médicas
   • Módulo D: Evaluación Final

🛠️  COMANDOS ÚTILES:
   • Verificar estado: kubectl get pods -n $NAMESPACE
   • Ver logs: kubectl logs $MOODLE_POD -n $NAMESPACE
   • Acceder al pod: kubectl exec -it $MOODLE_POD -n $NAMESPACE -- /bin/bash

${NC}"

print_success "Despliegue completado exitosamente"
echo -e "${BLUE}¡Tu curso de Telemedicina está listo para usar!${NC}"