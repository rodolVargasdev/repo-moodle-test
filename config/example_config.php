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

// Configuración GCP
define('GCP_PROJECT_ID', 'moodle-gcp-test');
define('GKE_CLUSTER_NAME', 'moodle-cluster');
define('GKE_ZONE', 'us-central1-c');
define('KUBERNETES_NAMESPACE', 'moodle');

// Configuración de usuarios de prueba
$TEST_USERS = [
    [
        'username' => 'doctor.lopez',
        'email' => 'doctor.lopez@telesalud.gob.sv',
        'firstname' => 'Juan',
        'lastname' => 'López',
        'role' => 'student'
    ],
    [
        'username' => 'dra.martinez',
        'email' => 'dra.martinez@goes.gob.sv',
        'firstname' => 'María',
        'lastname' => 'Martínez',
        'role' => 'student'
    ],
    [
        'username' => 'dr.rodriguez',
        'email' => 'dr.rodriguez@telesalud.gob.sv',
        'firstname' => 'Carlos',
        'lastname' => 'Rodríguez',
        'role' => 'student'
    ]
];

// Configuración de módulos del curso
$COURSE_MODULES = [
    1 => [
        'name' => 'Módulo A - Habilidades Tecnológicas',
        'description' => 'Introducción a Chrome OS y herramientas digitales para telemedicina',
        'activities' => [
            'Bienvenidos al Curso Básico de Nivelación en Telemedicina',
            'Normas de Uso del Aula Virtual',
            'UNIDAD 1. Introducción a Chrome OS y su uso en Telemedicina',
            '1.1 ACTIVIDAD EVALUADA 1: Formulario de Configuraciones de Chromebook',
            '1.2 ACTIVIDAD EVALUADA 2: Formulario de Teclas especiales en Chromebook',
            '1.3 ACTIVIDAD EVALUADA 3: Envío de mensajes por medio de Google Chat',
            '1.4 ACTIVIDAD EVALUADA 4: Realización de videollamada en Google Meet',
            '1.5 ACTIVIDAD EVALUADA 5: Organización de carpetas en Google Drive'
        ]
    ],
    2 => [
        'name' => 'Módulo B - Buenas Prácticas Digitales',
        'description' => 'Buenas prácticas digitales en teleconsulta y telemedicina',
        'activities' => [
            'UNIDAD 2. Buenas Prácticas Digitales en la Teleconsulta',
            '2.1 ACTIVIDAD EVALUADA 1: Resumen de Portal de Práctica Médica (DEMO)',
            '2.2 ACTIVIDAD EVALUADA 2: Caso real de inconvenientes en Telemedicina'
        ]
    ],
    3 => [
        'name' => 'Módulo C - Aplicaciones Médicas',
        'description' => 'Usabilidad de aplicaciones médicas especializadas',
        'activities' => [
            'UNIDAD 3. Usabilidad de la aplicación DR. ISSS en línea',
            '3.1 ACTIVIDAD EVALUADA 1: Descarga de la Aplicación Dr. ISSS En línea',
            '3.2 ACTIVIDAD EVALUADA 2: Navegación en la aplicación Dr. ISSS'
        ]
    ],
    4 => [
        'name' => 'Módulo D - Evaluación Final',
        'description' => 'Evaluación integral de conocimientos adquiridos',
        'activities' => [
            'FORO PARA CONSULTAS',
            'Evaluación Final del Curso'
        ]
    ]
];

// Configuración de insignias
$COURSE_BADGES = [
    [
        'name' => 'Experto en Habilidades Tecnológicas',
        'description' => 'Completó exitosamente el Módulo A - Habilidades Tecnológicas',
        'section' => 1
    ],
    [
        'name' => 'Especialista en Buenas Prácticas',
        'description' => 'Completó exitosamente el Módulo B - Buenas Prácticas Digitales',
        'section' => 2
    ],
    [
        'name' => 'Usuario Avanzado Dr. ISSS',
        'description' => 'Completó exitosamente el Módulo C - Aplicaciones Médicas',
        'section' => 3
    ],
    [
        'name' => 'Certificado en Telemedicina',
        'description' => 'Completó exitosamente todo el Curso Básico de Nivelación',
        'section' => 4
    ]
];

// No mostrar errores en producción
error_reporting(0);
ini_set('display_errors', 0);
?>