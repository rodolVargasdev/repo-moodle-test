# Guía Completa: Dashboard de Moodle en Looker Studio con MySQL en GCP

## Introducción
Esta guía te permitirá conectar Looker Studio a tu base de datos MySQL de Moodle alojada en Google Cloud Platform y crear un dashboard interactivo para analizar el progreso y participación de estudiantes.

---

## Parte 1: Conexión de Looker Studio a Cloud SQL MySQL

### 1.1 Preparación de la Instancia Cloud SQL

#### Información de conexión necesaria:
- **Instance Connection Name**: `proyecto-id:región:nombre-instancia`
- **Database Name**: Nombre de la base de datos de Moodle (generalmente `moodle`)
- **Username**: Usuario con permisos de lectura
- **Password**: Contraseña del usuario
- **IP Address**: IP pública o privada de la instancia

#### Dónde encontrar esta información en GCP:
1. Ve a la **Consola de GCP** → **SQL** → **Instancias**
2. Selecciona tu instancia de Moodle
3. En la pestaña **Resumen**:
   - **Instance Connection Name**: Se muestra en la parte superior
   - **IP Address**: En la sección "Conectar a esta instancia"
4. En la pestaña **Bases de datos**: Verifica el nombre de la base de datos
5. En la pestaña **Usuarios**: Configura un usuario con permisos de lectura

### 1.2 Configuración de Conectividad

#### Opción 1: IP Pública (Recomendada para Looker Studio)
1. En tu instancia Cloud SQL, ve a **Conexiones** → **Redes**
2. Habilita **IP pública**
3. Añade las IPs de Looker Studio a **Redes autorizadas**:
   ```
   64.18.0.0/20
   64.233.160.0/19
   66.102.0.0/20
   66.249.80.0/20
   72.14.192.0/18
   74.125.0.0/16
   108.177.8.0/21
   173.194.0.0/16
   207.126.144.0/20
   209.85.128.0/17
   ```

#### Opción 2: Proxy de Autenticación SQL (Más segura)
1. Instala el proxy de autenticación SQL
2. Configura el proxy para conectar a través de IAM
3. Utiliza las credenciales de servicio para autenticación

### 1.3 Conectar desde Looker Studio

1. Abre **Looker Studio** (datastudio.google.com)
2. Crea un nuevo **Informe**
3. Selecciona **Crear fuente de datos**
4. Busca y selecciona **Cloud SQL para MySQL**
5. Completa los campos de conexión:
   - **Nombre del host**: IP de tu instancia
   - **Puerto**: 3306 (por defecto)
   - **Base de datos**: nombre_base_datos_moodle
   - **Nombre de usuario**: tu_usuario
   - **Contraseña**: tu_contraseña
6. Haz clic en **Autenticar**
7. Haz clic en **Conectar**

---

## Parte 2: Identificación y Selección de Datos de Moodle

### 2.1 Tablas Clave de Moodle

#### Tablas Principales:
- **mdl_course**: Información de cursos
- **mdl_user**: Datos de usuarios
- **mdl_role_assignments**: Asignaciones de roles
- **mdl_context**: Contextos de roles
- **mdl_enrol**: Métodos de inscripción
- **mdl_user_enrolments**: Inscripciones de usuarios
- **mdl_course_modules**: Módulos del curso
- **mdl_modules**: Tipos de módulos
- **mdl_grade_grades**: Calificaciones
- **mdl_grade_items**: Elementos de calificación
- **mdl_logstore_standard_log**: Logs de actividad

### 2.2 Consultas SQL para Datos Clave

#### Consulta 1: Estudiantes inscritos en un curso
```sql
SELECT DISTINCT
    u.id as user_id,
    u.firstname,
    u.lastname,
    u.email,
    c.fullname as course_name,
    c.id as course_id,
    FROM_UNIXTIME(ue.timestart) as enrollment_date,
    FROM_UNIXTIME(u.lastaccess) as last_access,
    CASE 
        WHEN u.lastaccess > 0 THEN 'Activo'
        ELSE 'Inactivo'
    END as status
FROM mdl_user u
JOIN mdl_user_enrolments ue ON u.id = ue.userid
JOIN mdl_enrol e ON ue.enrolid = e.id
JOIN mdl_course c ON e.courseid = c.id
JOIN mdl_role_assignments ra ON u.id = ra.userid
JOIN mdl_context ctx ON ra.contextid = ctx.id
WHERE ctx.contextlevel = 50 
    AND ctx.instanceid = c.id
    AND ra.roleid = 5  -- Rol de estudiante
    AND c.id = ? -- Parámetro del curso
ORDER BY u.lastname, u.firstname;
```

#### Consulta 2: Calificaciones por actividad
```sql
SELECT 
    c.fullname as course_name,
    gi.itemname as activity_name,
    gi.itemtype,
    u.firstname,
    u.lastname,
    u.email,
    gg.finalgrade,
    gg.rawgrademax,
    ROUND((gg.finalgrade / gg.rawgrademax) * 100, 2) as percentage,
    FROM_UNIXTIME(gg.timemodified) as grade_date
FROM mdl_grade_grades gg
JOIN mdl_grade_items gi ON gg.itemid = gi.id
JOIN mdl_user u ON gg.userid = u.id
JOIN mdl_course c ON gi.courseid = c.id
WHERE c.id = ? -- Parámetro del curso
    AND gg.finalgrade IS NOT NULL
    AND gi.itemtype != 'course'
ORDER BY gi.itemname, u.lastname, u.firstname;
```

#### Consulta 3: Actividad de estudiantes (logs)
```sql
SELECT 
    c.fullname as course_name,
    u.firstname,
    u.lastname,
    u.email,
    l.action,
    l.target,
    l.component,
    FROM_UNIXTIME(l.timecreated) as activity_time,
    DATE(FROM_UNIXTIME(l.timecreated)) as activity_date,
    COUNT(*) as activity_count
FROM mdl_logstore_standard_log l
JOIN mdl_user u ON l.userid = u.id
JOIN mdl_course c ON l.courseid = c.id
WHERE c.id = ? -- Parámetro del curso
    AND l.timecreated >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY))
GROUP BY c.fullname, u.firstname, u.lastname, u.email, 
         l.action, l.target, l.component, activity_date
ORDER BY activity_time DESC;
```

#### Consulta 4: Progreso de actividades del curso
```sql
SELECT 
    c.fullname as course_name,
    cm.id as module_id,
    m.name as module_type,
    cm.instance,
    CASE 
        WHEN m.name = 'assign' THEN a.name
        WHEN m.name = 'quiz' THEN q.name
        WHEN m.name = 'forum' THEN f.name
        ELSE 'Actividad sin nombre'
    END as activity_name,
    COUNT(DISTINCT cmc.userid) as completed_users,
    COUNT(DISTINCT ue.userid) as total_enrolled,
    ROUND((COUNT(DISTINCT cmc.userid) / COUNT(DISTINCT ue.userid)) * 100, 2) as completion_percentage
FROM mdl_course c
JOIN mdl_course_modules cm ON c.id = cm.course
JOIN mdl_modules m ON cm.module = m.id
LEFT JOIN mdl_course_modules_completion cmc ON cm.id = cmc.coursemoduleid 
    AND cmc.completionstate > 0
LEFT JOIN mdl_assign a ON cm.instance = a.id AND m.name = 'assign'
LEFT JOIN mdl_quiz q ON cm.instance = q.id AND m.name = 'quiz'
LEFT JOIN mdl_forum f ON cm.instance = f.id AND m.name = 'forum'
JOIN mdl_user_enrolments ue ON c.id = (
    SELECT courseid FROM mdl_enrol WHERE id = ue.enrolid
)
WHERE c.id = ? -- Parámetro del curso
    AND cm.visible = 1
GROUP BY c.fullname, cm.id, m.name, cm.instance, activity_name
ORDER BY completion_percentage DESC;
```

#### Consulta 5: KPIs principales del curso
```sql
SELECT 
    c.fullname as course_name,
    COUNT(DISTINCT ue.userid) as total_students,
    COUNT(DISTINCT CASE WHEN u.lastaccess > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 7 DAY)) 
                       THEN ue.userid END) as active_last_week,
    AVG(CASE WHEN gg.finalgrade IS NOT NULL THEN 
             (gg.finalgrade / gg.rawgrademax) * 100 END) as avg_grade_percentage,
    COUNT(DISTINCT gi.id) as total_activities,
    COUNT(DISTINCT CASE WHEN cmc.completionstate > 0 
                       THEN cmc.coursemoduleid END) as completed_activities
FROM mdl_course c
LEFT JOIN mdl_enrol e ON c.id = e.courseid
LEFT JOIN mdl_user_enrolments ue ON e.id = ue.enrolid
LEFT JOIN mdl_user u ON ue.userid = u.id
LEFT JOIN mdl_grade_items gi ON c.id = gi.courseid AND gi.itemtype != 'course'
LEFT JOIN mdl_grade_grades gg ON gi.id = gg.itemid AND ue.userid = gg.userid
LEFT JOIN mdl_course_modules cm ON c.id = cm.course
LEFT JOIN mdl_course_modules_completion cmc ON cm.id = cmc.coursemoduleid
WHERE c.id = ? -- Parámetro del curso
GROUP BY c.fullname;
```

---

## Parte 3: Creación del Dashboard en Looker Studio

### 3.1 Estructura del Dashboard

#### Diseño Propuesto:
```
┌─────────────────────────────────────────────────────────────────┐
│                    Dashboard: Análisis del Curso de Moodle      │
├─────────────────────────────────────────────────────────────────┤
│ Filtros: [Selector de Curso ▼] [Rango de Fechas]               │
├─────────────────────────────────────────────────────────────────┤
│ KPIs:                                                           │
│ [📊 Total Estudiantes] [📈 Tasa Actividad] [📋 Promedio Notas] │
├─────────────────────────────────────────────────────────────────┤
│ Visualizaciones:                                                │
│ ┌─────────────────────┐ ┌─────────────────────┐                │
│ │   Tabla Estudiantes │ │ Distribución Notas  │                │
│ │                     │ │   (Histograma)      │                │
│ └─────────────────────┘ └─────────────────────┘                │
│ ┌─────────────────────┐ ┌─────────────────────┐                │
│ │ Progreso Actividades│ │  Actividad Reciente │                │
│ │   (Gráfico Barras)  │ │   (Serie Temporal)  │                │
│ └─────────────────────┘ └─────────────────────┘                │
└─────────────────────────────────────────────────────────────────┘
```

### 3.2 Configuración de Componentes

#### Filtros Interactivos:
1. **Selector de Curso**:
   - Tipo: Lista desplegable
   - Campo: `course_name`
   - Permitir múltiples selecciones: No

2. **Rango de Fechas**:
   - Tipo: Control de fecha
   - Campo: `activity_date`
   - Valor por defecto: Últimos 30 días

#### KPIs Principales:
1. **Total Estudiantes**:
   - Tipo: Métrica
   - Campo: `COUNT(DISTINCT user_id)`
   - Formato: Número entero

2. **Tasa de Actividad**:
   - Tipo: Métrica
   - Campo: `(active_last_week / total_students) * 100`
   - Formato: Porcentaje

3. **Promedio de Calificaciones**:
   - Tipo: Métrica
   - Campo: `AVG(percentage)`
   - Formato: Número decimal (2 decimales)

4. **Actividades Completadas**:
   - Tipo: Métrica
   - Campo: `COUNT(DISTINCT activity_name)`
   - Formato: Número entero

#### Visualizaciones Detalladas:

1. **Tabla de Estudiantes**:
   - Tipo: Tabla
   - Columnas: `firstname`, `lastname`, `email`, `last_access`, `status`
   - Ordenar por: `last_access` (DESC)
   - Paginación: 20 filas por página

2. **Distribución de Calificaciones**:
   - Tipo: Histograma
   - Eje X: `percentage` (agrupado en intervalos de 10)
   - Eje Y: `COUNT(user_id)`
   - Color: Gradiente verde-rojo

3. **Progreso de Actividades**:
   - Tipo: Gráfico de barras horizontales
   - Eje X: `completion_percentage`
   - Eje Y: `activity_name`
   - Color: Azul

4. **Actividad Reciente**:
   - Tipo: Serie temporal
   - Eje X: `activity_date`
   - Eje Y: `SUM(activity_count)`
   - Línea: Color azul

### 3.3 Pasos para Crear el Dashboard

#### Paso 1: Configurar Fuentes de Datos
1. Crea una fuente de datos para cada consulta SQL
2. Nombra las fuentes descriptivamente:
   - `estudiantes_curso`
   - `calificaciones_actividades`
   - `logs_actividad`
   - `progreso_actividades`
   - `kpis_curso`

#### Paso 2: Crear Filtros
1. Añade control de filtro → Lista desplegable
2. Configura el filtro de curso
3. Añade control de fecha para rango temporal

#### Paso 3: Crear KPIs
1. Añade gráfico → Métrica
2. Configura cada KPI con su respectivo campo
3. Personaliza formato y estilo

#### Paso 4: Crear Visualizaciones
1. Para cada visualización:
   - Añade el tipo de gráfico correspondiente
   - Configura dimensiones y métricas
   - Aplica filtros necesarios
   - Personaliza estilo y colores

#### Paso 5: Organizar Layout
1. Organiza los componentes según el diseño propuesto
2. Ajusta tamaños y posiciones
3. Añade títulos y descripciones

---

## Parte 4: Consideraciones de Rendimiento

### 4.1 Optimización de Consultas

#### Recomendaciones:
1. **Índices en MySQL**:
   ```sql
   CREATE INDEX idx_user_enrolments_userid ON mdl_user_enrolments(userid);
   CREATE INDEX idx_logs_courseid_timecreated ON mdl_logstore_standard_log(courseid, timecreated);
   CREATE INDEX idx_grade_grades_userid_itemid ON mdl_grade_grades(userid, itemid);
   ```

2. **Limitar Datos**:
   - Usar filtros de fecha en consultas de logs
   - Limitar resultados con `LIMIT`
   - Usar `WHERE` para cursos específicos

### 4.2 Migración a BigQuery (Recomendado)

#### Beneficios:
- **Rendimiento**: Consultas más rápidas en datasets grandes
- **Escalabilidad**: Manejo eficiente de grandes volúmenes
- **Costo**: Modelo de pago por uso
- **Integración**: Conectividad nativa con Looker Studio

#### Proceso de Migración:
1. **Replicación de Datos**:
   ```bash
   # Usando Cloud SQL Datastream
   gcloud datastream streams create mysql-to-bigquery-stream \
     --location=us-central1 \
     --display-name="Moodle to BigQuery" \
     --source-config=mysql-source-config.json \
     --destination-config=bigquery-destination-config.json
   ```

2. **Configuración de Replicación**:
   - Configura replicación en tiempo real
   - Selecciona tablas relevantes de Moodle
   - Establece esquema de destino en BigQuery

3. **Ventajas del Approach BigQuery**:
   - Consultas sub-segundo en datasets grandes
   - Capacidades de ML integradas
   - Mejor handling de datos históricos
   - Cacheo automático de resultados

### 4.3 Configuración de Actualización

#### Opciones de Refresh:
1. **Tiempo Real**: Para datos críticos (logs, calificaciones)
2. **Cada Hora**: Para datos de progreso
3. **Diario**: Para datos estadísticos generales

#### Configuración en Looker Studio:
1. Ve a **Configuración de datos**
2. Selecciona **Actualización de datos**
3. Configura frecuencia según necesidad
4. Habilita caché para consultas frecuentes

---

## Parte 5: Monitoreo y Mantenimiento

### 5.1 Monitoreo de Rendimiento

#### Métricas a Monitorear:
- Tiempo de respuesta de consultas
- Uso de recursos en Cloud SQL
- Frecuencia de acceso al dashboard
- Errores de conexión

#### Herramientas de Monitoreo:
- **Cloud SQL Insights**: Para análisis de performance
- **Cloud Monitoring**: Para alertas y métricas
- **Looker Studio Activity**: Para uso del dashboard

### 5.2 Seguridad y Acceso

#### Mejores Prácticas:
1. **Principio de Menor Privilegio**:
   - Usuario de BD solo con permisos SELECT
   - Acceso limitado a tablas necesarias

2. **Cifrado**:
   - Conexiones SSL/TLS
   - Cifrado en tránsito y reposo

3. **Auditoría**:
   - Log de accesos al dashboard
   - Monitoreo de consultas ejecutadas

---

## Conclusión

Con esta guía tienes todo lo necesario para:
1. ✅ Conectar Looker Studio a tu base de datos MySQL de Moodle
2. ✅ Extraer y transformar datos relevantes
3. ✅ Crear un dashboard interactivo y funcional
4. ✅ Optimizar rendimiento y mantener el sistema

El dashboard resultante te permitirá:
- Monitorear el progreso de estudiantes en tiempo real
- Analizar patrones de participación
- Identificar estudiantes en riesgo
- Evaluar efectividad de actividades
- Tomar decisiones basadas en datos

¿Necesitas ayuda específica con algún paso? ¡Estaré aquí para apoyarte en la implementación!