# 🎓 Moodle en Kubernetes - Guía Paso a Paso

Este proyecto documenta el despliegue completo de Moodle en Google Kubernetes Engine (GKE) siguiendo la guía de [Carlos Rojas](https://medium.com/@carlos.rv125/moodle-en-kubernetes-gu%C3%ADa-paso-a-paso-68911c477f9d).

## 📋 Tabla de Contenidos

- [Prerrequisitos](#prerrequisitos)
- [Configuración del Clúster](#configuración-del-clúster)
- [Despliegue de Base de Datos](#despliegue-de-base-de-datos)
- [Configuración de Almacenamiento](#configuración-de-almacenamiento)
- [Despliegue de Moodle](#despliegue-de-moodle)
- [Configuración de Ingress](#configuración-de-ingress)
- [Pruebas y Acceso](#pruebas-y-acceso)
- [Mantenimiento](#mantenimiento)

## ✅ Prerrequisitos Completados

### Herramientas Instaladas
- [x] **kubectl** v1.29.2 - Cliente de línea de comandos para Kubernetes
- [x] **Helm** v3.14.3 - Gestor de paquetes para Kubernetes
- [x] **Google Cloud SDK** - Herramientas de línea de comandos para GCP
- [x] **Docker** v26.1.1 - Para desarrollo local (opcional)

### Configuración de GCP
- [x] **Cuenta de Google Cloud** configurada
- [x] **Proyecto**: `moodle-gcp-test`
- [x] **API de Kubernetes Engine** habilitada
- [x] **Facturación** habilitada

## 🚀 Configuración del Clúster

### Clúster GKE Creado
```bash
# Comandos ejecutados:
gcloud services enable container.googleapis.com --project=moodle-gcp-test
gcloud config set project moodle-gcp-test
gcloud container clusters create moodle-cluster --zone us-central1-c --num-nodes=2 --machine-type=e2-medium
gcloud container clusters get-credentials moodle-cluster --zone us-central1-c
```

### Especificaciones del Clúster
- **Nombre**: `moodle-cluster`
- **Zona**: `us-central1-c`
- **Nodos**: 2 (e2-medium)
- **Versión de Kubernetes**: 1.32.4-gke.1415000
- **Estado**: RUNNING

### Verificación
```bash
kubectl get nodes
# Resultado: 2 nodos en estado Ready
```

## 🗄️ Base de Datos MySQL

### MySQL Desplegado
```bash
# Comandos ejecutados:
helm repo add bitnami https://charts.bitnami.com/bitnami
helm repo update
kubectl create namespace moodle-db
helm install moodle-mysql bitnami/mysql --namespace moodle-db --values values/mysql-moodle.yaml
```

### Configuración de MySQL
- **Namespace**: `moodle-db`
- **Base de datos**: `moodle`
- **Usuario**: `moodle_user`
- **Contraseña**: `MoodleUser2025!`
- **Root Password**: `Moodle2025!`
- **Servicio**: `moodle-mysql.moodle-db.svc.cluster.local:3306`
- **Almacenamiento**: 8Gi (persistente)

### Verificación de MySQL
```bash
kubectl get pods -n moodle-db
# Resultado: moodle-mysql-0 en estado Running

kubectl get services -n moodle-db
# Resultado: moodle-mysql ClusterIP 3306
```

## 🎓 Moodle Desplegado

### Moodle Funcionando
```bash
# Comandos ejecutados:
helm install moodle bitnami/moodle --namespace moodle --set mariadb.enabled=true --set mariadb.auth.rootPassword=Moodle2025! --set mariadb.auth.database=moodle --set mariadb.auth.username=moodle_user --set mariadb.auth.password=MoodleUser2025! --set service.type=LoadBalancer
```

### Configuración de Moodle
- **Namespace**: `moodle`
- **URL de acceso**: http://34.72.133.6
- **Usuario administrador**: `admin`
- **Contraseña administrador**: `password`
- **Base de datos**: MariaDB integrado
- **Servicio**: LoadBalancer (IP externa asignada)

### Verificación de Moodle
```bash
kubectl get pods -n moodle
# Resultado: moodle-xxx Running, moodle-mariadb-0 Running

kubectl get services -n moodle
# Resultado: moodle LoadBalancer 34.72.133.6
```

### Acceso a Moodle
- **URL**: http://34.72.133.6
- **Credenciales**: admin / password
- **Estado**: ✅ Funcionando correctamente

## 🔄 Próximos Pasos (Pendientes)

### 1. Despliegue de Base de Datos MySQL
- [x] Crear namespace para la base de datos
- [x] Desplegar MySQL usando Helm
- [x] Configurar credenciales y base de datos
- [x] Verificar conectividad

### 2. Configuración de Almacenamiento
- [x] Crear PersistentVolumeClaim para Moodle
- [x] Configurar almacenamiento para archivos de Moodle
- [x] Verificar permisos de acceso

### 3. Despliegue de Moodle
- [x] Agregar repositorio de Helm de Bitnami
- [x] Configurar valores personalizados para Moodle
- [x] Desplegar Moodle usando Helm
- [x] Verificar el estado del despliegue

### 4. Configuración de Ingress
- [ ] Instalar controlador de Ingress (nginx-ingress)
- [ ] Configurar Ingress para Moodle
- [ ] Configurar certificados SSL (opcional)
- [ ] Configurar dominio personalizado

### 5. Pruebas y Acceso
- [ ] Obtener IP externa del servicio
- [ ] Acceder a Moodle desde el navegador
- [ ] Configurar usuario administrador
- [ ] Verificar funcionalidad básica

## 📁 Estructura del Proyecto

```
moodle-gcp-test2/
├── README.md                    # Este archivo
├── helm/                        # Archivos de Helm descargados
├── helm.zip                     # Archivo de Helm descargado
├── k8s/                         # (Pendiente) Manifiestos de Kubernetes
│   ├── mysql/                   # Configuración de MySQL
│   ├── moodle/                  # Configuración de Moodle
│   └── ingress/                 # Configuración de Ingress
└── values/                      # (Pendiente) Archivos de valores de Helm
    ├── mysql-values.yaml
    └── moodle-values.yaml
```

## 🔧 Comandos Útiles

### Verificar Estado del Clúster
```bash
kubectl get nodes
kubectl get pods --all-namespaces
kubectl get services --all-namespaces
```

### Verificar Helm
```bash
helm list
helm repo list
```

### Logs y Debugging
```bash
kubectl logs <pod-name> -n <namespace>
kubectl describe pod <pod-name> -n <namespace>
```

## 📚 Referencias

- [Guía Original](https://medium.com/@carlos.rv125/moodle-en-kubernetes-gu%C3%ADa-paso-a-paso-68911c477f9d)
- [Documentación de GKE](https://cloud.google.com/kubernetes-engine/docs)
- [Documentación de Helm](https://helm.sh/docs/)
- [Documentación de Moodle](https://docs.moodle.org/)

## 🚨 Notas Importantes

1. **Costos**: El clúster GKE genera costos por hora. Recuerda eliminarlo cuando no lo uses.
2. **Seguridad**: Las credenciales por defecto deben cambiarse en producción.
3. **Backup**: Configurar backups regulares de la base de datos.
4. **Escalabilidad**: El clúster puede escalar según las necesidades.

## 📝 Estado Actual

**Fecha**: 7 de Julio, 2025  
**Progreso**: 75% completado  
**Próximo paso**: Configuración de Ingress y acceso externo

---

*Este documento se actualiza automáticamente conforme avanzamos en el despliegue.* # repo-moodle-test
