#!/bin/bash

# 🛠️ Comandos útiles para el curso de Telemedicina

# Configuración
PROJECT_ID="moodle-gcp-test"
CLUSTER_NAME="moodle-cluster"
ZONE="us-central1-c"
NAMESPACE="moodle"

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Conectar al cluster
connect_cluster() {
    echo -e "${BLUE}🔗 Conectando al cluster GKE...${NC}"
    gcloud container clusters get-credentials $CLUSTER_NAME --zone=$ZONE --project=$PROJECT_ID
    echo -e "${GREEN}✅ Conectado exitosamente${NC}"
}

# Obtener pod de Moodle
get_moodle_pod() {
    kubectl get pods -n $NAMESPACE -l app=moodle -o jsonpath='{.items[0].metadata.name}'
}

# Verificar estado del curso
verify_course() {
    echo -e "${BLUE}🔍 Verificando estado del curso...${NC}"
    MOODLE_POD=$(get_moodle_pod)
    if [ -z "$MOODLE_POD" ]; then
        echo -e "${RED}❌ No se encontró el pod de Moodle${NC}"
        return 1
    fi
    kubectl exec -it $MOODLE_POD -n $NAMESPACE -- php /bitnami/moodle/scripts/verify_oauth_setup.php
}

# Ver logs
view_logs() {
    echo -e "${BLUE}📄 Mostrando logs de Moodle...${NC}"
    MOODLE_POD=$(get_moodle_pod)
    if [ -z "$MOODLE_POD" ]; then
        echo -e "${RED}❌ No se encontró el pod de Moodle${NC}"
        return 1
    fi
    kubectl logs $MOODLE_POD -n $NAMESPACE --tail=50
}

# Acceder al pod
access_pod() {
    echo -e "${BLUE}🚪 Accediendo al pod de Moodle...${NC}"
    MOODLE_POD=$(get_moodle_pod)
    if [ -z "$MOODLE_POD" ]; then
        echo -e "${RED}❌ No se encontró el pod de Moodle${NC}"
        return 1
    fi
    kubectl exec -it $MOODLE_POD -n $NAMESPACE -- /bin/bash
}

# Backup de base de datos
backup_db() {
    echo -e "${BLUE}💾 Realizando backup de base de datos...${NC}"
    BACKUP_FILE="backup_telemedicina_$(date +%Y%m%d_%H%M%S).sql"
    if kubectl exec -it moodle-mariadb-0 -n $NAMESPACE -- mysqldump -u root -p moodle > $BACKUP_FILE; then
        echo -e "${GREEN}✅ Backup completado: $BACKUP_FILE${NC}"
    else
        echo -e "${RED}❌ Error en el backup${NC}"
    fi
}

# Limpiar caché
clear_cache() {
    echo -e "${BLUE}🧹 Limpiando caché de Moodle...${NC}"
    MOODLE_POD=$(get_moodle_pod)
    if [ -z "$MOODLE_POD" ]; then
        echo -e "${RED}❌ No se encontró el pod de Moodle${NC}"
        return 1
    fi
    kubectl exec -it $MOODLE_POD -n $NAMESPACE -- php /bitnami/moodle/admin/cli/purge_caches.php
    echo -e "${GREEN}✅ Caché limpiado${NC}"
}

# Ejecutar cron
run_cron() {
    echo -e "${BLUE}⏰ Ejecutando cron de Moodle...${NC}"
    MOODLE_POD=$(get_moodle_pod)
    if [ -z "$MOODLE_POD" ]; then
        echo -e "${RED}❌ No se encontró el pod de Moodle${NC}"
        return 1
    fi
    kubectl exec -it $MOODLE_POD -n $NAMESPACE -- php /bitnami/moodle/admin/cli/cron.php
    echo -e "${GREEN}✅ Cron ejecutado${NC}"
}

# Mostrar estado del cluster
show_status() {
    echo -e "${BLUE}📊 Estado del cluster:${NC}"
    kubectl get pods -n $NAMESPACE
    echo ""
    kubectl get services -n $NAMESPACE
    echo ""
    kubectl top pods -n $NAMESPACE 2>/dev/null || echo "Métricas no disponibles"
}

# Mostrar información del curso
show_course_info() {
    echo -e "${BLUE}📚 Información del curso:${NC}"
    MOODLE_POD=$(get_moodle_pod)
    if [ -z "$MOODLE_POD" ]; then
        echo -e "${RED}❌ No se encontró el pod de Moodle${NC}"
        return 1
    fi
    
    EXTERNAL_IP=$(kubectl get service moodle -n $NAMESPACE -o jsonpath='{.status.loadBalancer.ingress[0].ip}' 2>/dev/null)
    if [ -z "$EXTERNAL_IP" ]; then
        EXTERNAL_IP="34.72.133.6"
    fi
    
    COURSE_ID=$(kubectl exec -it $MOODLE_POD -n $NAMESPACE -- php -r "
    require_once('/bitnami/moodle/config.php');
    \$course = \$DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
    if (\$course) echo \$course->id;
    " 2>/dev/null | tr -d '\r\n')
    
    echo -e "${GREEN}🔗 URLs importantes:${NC}"
    echo "   • Moodle: http://$EXTERNAL_IP"
    echo "   • Curso: http://$EXTERNAL_IP/course/view.php?id=$COURSE_ID"
    echo "   • Admin: http://$EXTERNAL_IP/admin"
}

# Mostrar menú
show_menu() {
    echo -e "${BLUE}🏥 Comandos disponibles para el curso de Telemedicina:${NC}"
    echo "1. connect_cluster - Conectar al cluster GKE"
    echo "2. verify_course - Verificar estado del curso"
    echo "3. view_logs - Ver logs de Moodle"
    echo "4. access_pod - Acceder al pod de Moodle"
    echo "5. backup_db - Hacer backup de base de datos"
    echo "6. clear_cache - Limpiar caché de Moodle"
    echo "7. run_cron - Ejecutar cron de Moodle"
    echo "8. show_status - Mostrar estado del cluster"
    echo "9. show_course_info - Mostrar información del curso"
    echo ""
    echo -e "${YELLOW}Uso: ./useful_commands.sh [comando]${NC}"
}

# Ejecutar función si se pasa como argumento
if [ $# -eq 0 ]; then
    show_menu
else
    case $1 in
        connect_cluster|verify_course|view_logs|access_pod|backup_db|clear_cache|run_cron|show_status|show_course_info)
            $1
            ;;
        *)
            echo -e "${RED}❌ Comando no reconocido: $1${NC}"
            show_menu
            ;;
    esac
fi