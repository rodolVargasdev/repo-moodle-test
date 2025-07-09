# � Moodle Telemedicina - Curso de Nivelación

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
├── � guides/                     # Guías de uso
│   ├── GUIA_DEPLOYMENT_GCP_CLOUDSHELL.md
│   └── COMANDOS_GCLOUD_TELEMEDICINA.md
├── 📁 docs/                       # Documentación técnica
│   ├── 01_instalacion_y_configuracion.md
│   ├── 02_desarrollo_themes.md
│   ├── 03_plugins_personalizados.md
│   └── 04_apis_integraciones.md
├── 📁 config/                     # Archivos de configuración
│   └── example_config.php
├── 📁 utils/                      # Utilidades y comandos
│   └── useful_commands.sh
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

# Verificar curso usando utilidades
./utils/useful_commands.sh verify_course

# Ver logs
./utils/useful_commands.sh view_logs
```

### Mantenimiento
```bash
# Backup de base de datos
./utils/useful_commands.sh backup_db

# Limpiar caché
./utils/useful_commands.sh clear_cache

# Ejecutar cron
./utils/useful_commands.sh run_cron
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

## � Troubleshooting

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

## � Licencia

Este proyecto está bajo la licencia MIT. Ver archivo LICENSE para más detalles.

---

**Desarrollado para el fortalecimiento de la Telemedicina en El Salvador** 🇸🇻

*Última actualización: 2025-01-07*
