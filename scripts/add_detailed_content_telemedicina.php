<?php
/**
 * Agregar contenido detallado al Curso de Telemedicina
 * Similar al contenido visto en Google Classroom
 */

define('CLI_SCRIPT', true);
require_once('/bitnami/moodle/config.php');
require_once($CFG->libdir . '/clilib.php');

echo "📝 Agregando contenido detallado al curso de Telemedicina...\n";

// Buscar el curso
$course = $DB->get_record('course', ['shortname' => 'CBN-TELEMEDICINA-2025']);
if (!$course) {
    echo "❌ Error: Curso no encontrado. Ejecuta primero create_telemedicina_course_structure.php\n";
    exit(1);
}

// Contenido detallado para cada actividad
$detailed_content = [
    'Bienvenidos al Curso Básico de Nivelación en Telemedicina' => [
        'type' => 'announce',
        'content' => '
        <h2>Bienvenidos al Curso Básico de Nivelación en Telemedicina</h2>
        
        <p>Nos complace que se una a esta experiencia de aprendizaje, el cual permitirá dar los primeros pasos para fortalecer los conocimientos en esta modalidad de atención médica a distancia, que cada vez cobra más relevancia en el mundo de la salud.</p>
        
        <p>Este curso está diseñado para ofrecer una comprensión integral de los conceptos esenciales de la Telemedicina: las herramientas digitales más utilizadas en el ámbito médico y las mejores prácticas para garantizar una atención de calidad y calidez a los pacientes.</p>
        
        <p>A lo largo de las siguientes semanas, exploraremos desde lo más básico, asegurándonos de que tenga una base sólida para poder adaptarse a este nuevo entorno digital de manera efectiva. Además contaremos con recursos interactivos, ejercicios prácticos y sesiones de discusión virtual asíncrona que permitirá aplicar lo aprendido y resolver dudas en tiempo real.</p>
        
        <p>Recuerda que, aunque este curso se realiza en modalidad virtual, la interacción y la participación activa son clave para un aprendizaje exitoso. Así que, no dude en hacer preguntas, compartir las experiencias y aprovechar al máximo los recursos disponibles.</p>
        
        <p>Estamos aquí para apoyarle en todo el proceso y esperamos que disfrute este viaje de aprendizaje hacia una telemedicina más efectiva y humana.</p>
        
        <p><strong>¡Vamos a comenzar!</strong></p>
        '
    ],
    
    'Normas de Uso del Aula Virtual' => [
        'type' => 'announce',
        'content' => '
        <h2>NORMAS DE USO DEL AULA VIRTUAL</h2>
        
        <p><strong>Respeto y cortesía.</strong> Mantener un tono respetuoso y cordial en todas las interacciones, ya sean foros, chats o mensajes. Recordar que todos somos parte de un entorno de aprendizaje</p>
        
        <p><strong>Asistencia y puntualidad.</strong> Conectarse en el tiempo establecido a las sesiones asíncronas y participar activamente en todas las actividades publicadas en el aula. Si no puedes cumplir con alguna de las actividades, informa con anticipación al facilitador y al supervisor.</p>
        
        <p><strong>Cumplimiento de plazos.</strong> Entregar las tareas, evaluaciones y actividades dentro de los plazos establecidos. La puntualidad es esencial para el buen desarrollo del curso.</p>
        
        <p><strong>Confidencialidad.</strong> No compartir materiales del curso, grabaciones o información personal de compañeros o docentes. El contenido de esta plataforma es de uso exclusivo de la institución y su difusión queda prohibida.</p>
        
        <p><strong>Uso adecuado de los recursos.</strong> Utilizar los materiales proporcionados por el curso de manera responsable y sin alterar su contenido.</p>
        
        <p><strong>Uso de tecnología adecuada.</strong> Asegurarse de contar con una conexión a internet estable y el equipo necesario para participar sin inconvenientes en las clases y actividades, utilizando el correo institucional.</p>
        
        <p><strong>Comportamiento responsable.</strong> Evitar el uso del aula virtual para actividades no relacionadas con el objetivo de esta, como la difusión de contenidos no académicos o inapropiados.</p>
        
        <p>Estas normas buscan crear un ambiente de aprendizaje productivo, respetuoso y seguro para todos los miembros del aula virtual.</p>
        
        <p><strong>Gracias por cumplir con los lineamientos detallados y responder a este anuncio de que has dado lectura a cada uno de las normas planteadas.</strong></p>
        '
    ],
    
    'UNIDAD 1. Introducción a Chrome OS y su uso en Telemedicina' => [
        'type' => 'resource',
        'content' => '
        <h2>UNIDAD 1. Introducción a Chrome OS y su uso en Telemedicina</h2>
        
        <h3>Objetivos de aprendizaje:</h3>
        <ul>
            <li>Conocer las características principales de Chrome OS</li>
            <li>Aprender a navegar eficientemente en el sistema operativo</li>
            <li>Configurar herramientas básicas para telemedicina</li>
            <li>Identificar aplicaciones útiles para atención médica virtual</li>
        </ul>
        
        <h3>Contenido:</h3>
        <ol>
            <li><strong>¿Qué es Chrome OS?</strong>
                <ul>
                    <li>Historia y características</li>
                    <li>Ventajas para entornos médicos</li>
                    <li>Requisitos del sistema</li>
                </ul>
            </li>
            
            <li><strong>Configuración inicial</strong>
                <ul>
                    <li>Primer arranque</li>
                    <li>Configuración de cuenta</li>
                    <li>Sincronización de datos</li>
                </ul>
            </li>
            
            <li><strong>Navegación básica</strong>
                <ul>
                    <li>Escritorio y barra de tareas</li>
                    <li>Gestor de archivos</li>
                    <li>Configuración del sistema</li>
                </ul>
            </li>
            
            <li><strong>Aplicaciones para telemedicina</strong>
                <ul>
                    <li>Google Meet para videoconsultas</li>
                    <li>Google Drive para almacenamiento</li>
                    <li>Aplicaciones médicas especializadas</li>
                </ul>
            </li>
        </ol>
        
        <h3>Recursos adicionales:</h3>
        <ul>
            <li>Manual oficial de Chrome OS</li>
            <li>Videos tutoriales</li>
            <li>Guías de configuración</li>
        </ul>
        '
    ],
    
    '1.1 ACTIVIDAD EVALUADA 1: Formulario de Configuraciones de Chromebook' => [
        'type' => 'assign',
        'content' => '
        <h2>1.1 ACTIVIDAD EVALUADA 1: Formulario de Configuraciones de Chromebook</h2>
        
        <h3>Objetivo:</h3>
        <p>Demostrar conocimiento sobre las configuraciones básicas de un Chromebook para uso en telemedicina.</p>
        
        <h3>Instrucciones:</h3>
        <ol>
            <li>Acceda a la configuración de su Chromebook</li>
            <li>Explore las diferentes opciones de configuración</li>
            <li>Complete el formulario con las configuraciones recomendadas</li>
            <li>Tome capturas de pantalla de las configuraciones aplicadas</li>
        </ol>
        
        <h3>Configuraciones a revisar:</h3>
        <ul>
            <li>Configuración de red Wi-Fi</li>
            <li>Configuración de cuentas de usuario</li>
            <li>Configuración de privacidad</li>
            <li>Configuración de accesibilidad</li>
            <li>Configuración de idioma y región</li>
        </ul>
        
        <h3>Entrega:</h3>
        <p>Suba un archivo PDF con sus respuestas y capturas de pantalla.</p>
        
        <h3>Criterios de evaluación:</h3>
        <ul>
            <li>Completitud de las respuestas (30%)</li>
            <li>Precisión técnica (30%)</li>
            <li>Calidad de las capturas de pantalla (20%)</li>
            <li>Puntualidad en la entrega (20%)</li>
        </ul>
        
        <p><strong>Fecha límite:</strong> 7 días a partir de la fecha de publicación</p>
        '
    ],
    
    'UNIDAD 2. Buenas Prácticas Digitales en la Teleconsulta' => [
        'type' => 'resource',
        'content' => '
        <h2>UNIDAD 2. Buenas Prácticas Digitales en la Teleconsulta</h2>
        
        <h3>Introducción:</h3>
        <p>La teleconsulta ha revolucionado la forma en que brindamos atención médica. Esta unidad se enfoca en las mejores prácticas para garantizar una atención de calidad en el entorno digital.</p>
        
        <h3>Objetivos de aprendizaje:</h3>
        <ul>
            <li>Identificar las mejores prácticas en teleconsulta</li>
            <li>Aplicar protocolos de seguridad digital</li>
            <li>Mejorar la comunicación paciente-médico virtual</li>
            <li>Gestionar eficientemente las consultas remotas</li>
        </ul>
        
        <h3>Contenido:</h3>
        
        <h4>2.1 Preparación para la teleconsulta</h4>
        <ul>
            <li>Configuración del espacio de trabajo</li>
            <li>Verificación de equipos y conectividad</li>
            <li>Revisión de la historia clínica previa</li>
            <li>Preparación de materiales de apoyo</li>
        </ul>
        
        <h4>2.2 Durante la consulta</h4>
        <ul>
            <li>Protocolo de saludo y presentación</li>
            <li>Técnicas de comunicación efectiva</li>
            <li>Manejo de la cámara y audio</li>
            <li>Documentación en tiempo real</li>
        </ul>
        
        <h4>2.3 Aspectos éticos y legales</h4>
        <ul>
            <li>Consentimiento informado digital</li>
            <li>Confidencialidad y privacidad</li>
            <li>Regulaciones específicas</li>
            <li>Manejo de datos sensibles</li>
        </ul>
        
        <h4>2.4 Seguimiento post-consulta</h4>
        <ul>
            <li>Documentación de la consulta</li>
            <li>Envío de prescripciones digitales</li>
            <li>Programación de seguimientos</li>
            <li>Comunicación con el paciente</li>
        </ul>
        
        <h3>Casos de estudio:</h3>
        <ol>
            <li>Consulta de medicina interna virtual</li>
            <li>Teleconsulta de urgencias</li>
            <li>Seguimiento de pacientes crónicos</li>
            <li>Consulta pediátrica remota</li>
        </ol>
        '
    ],
    
    'UNIDAD 3. Usabilidad de la aplicación DR. ISSS en línea' => [
        'type' => 'resource',
        'content' => '
        <h2>UNIDAD 3. Usabilidad de la aplicación DR. ISSS en línea</h2>
        
        <h3>Introducción:</h3>
        <p>La aplicación DR. ISSS en línea es una herramienta fundamental para la atención médica virtual en El Salvador. Esta unidad proporciona una guía completa para su uso efectivo.</p>
        
        <h3>Objetivos:</h3>
        <ul>
            <li>Conocer las funcionalidades de la aplicación DR. ISSS</li>
            <li>Navegar eficientemente por la interfaz</li>
            <li>Realizar consultas y procedimientos básicos</li>
            <li>Resolver problemas técnicos comunes</li>
        </ul>
        
        <h3>Contenido:</h3>
        
        <h4>3.1 Instalación y configuración inicial</h4>
        <ul>
            <li>Descarga desde tiendas oficiales</li>
            <li>Proceso de instalación paso a paso</li>
            <li>Configuración de cuenta y perfil</li>
            <li>Verificación de credenciales</li>
        </ul>
        
        <h4>3.2 Interfaz principal</h4>
        <ul>
            <li>Menú principal y navegación</li>
            <li>Panel de control del médico</li>
            <li>Gestión de agenda y citas</li>
            <li>Acceso a historias clínicas</li>
        </ul>
        
        <h4>3.3 Funcionalidades principales</h4>
        <ul>
            <li>Consultas virtuales</li>
            <li>Prescripción digital</li>
            <li>Gestión de expedientes</li>
            <li>Reportes y estadísticas</li>
        </ul>
        
        <h4>3.4 Integración con sistemas hospitalarios</h4>
        <ul>
            <li>Conexión con SIAP</li>
            <li>Sincronización de datos</li>
            <li>Interoperabilidad con otros sistemas</li>
            <li>Backup y recuperación</li>
        </ul>
        
        <h3>Ejercicios prácticos:</h3>
        <ol>
            <li>Configuración de perfil médico</li>
            <li>Creación de cita virtual</li>
            <li>Consulta de expediente digital</li>
            <li>Emisión de prescripción electrónica</li>
        </ol>
        
        <h3>Troubleshooting:</h3>
        <ul>
            <li>Problemas de conectividad</li>
            <li>Errores de sincronización</li>
            <li>Problemas de audio/video</li>
            <li>Contacto con soporte técnico</li>
        </ul>
        '
    ],
    
    'FORO PARA CONSULTAS' => [
        'type' => 'forum',
        'content' => '
        <h2>FORO PARA CONSULTAS</h2>
        
        <p>Este es el espacio designado para resolver dudas, compartir experiencias y discutir temas relacionados con el curso.</p>
        
        <h3>Normas del foro:</h3>
        <ul>
            <li>Mantener un tono respetuoso y profesional</li>
            <li>Utilizar títulos descriptivos para sus consultas</li>
            <li>Buscar en publicaciones anteriores antes de hacer una nueva consulta</li>
            <li>Compartir recursos útiles con compañeros</li>
            <li>Responder a consultas cuando sea posible</li>
        </ul>
        
        <h3>Tipos de consultas bienvenidas:</h3>
        <ul>
            <li>Dudas técnicas sobre configuraciones</li>
            <li>Problemas con aplicaciones médicas</li>
            <li>Consultas sobre mejores prácticas</li>
            <li>Compartir experiencias exitosas</li>
            <li>Sugerencias de mejora</li>
        </ul>
        
        <p><strong>Tiempo de respuesta:</strong> Las consultas serán respondidas en un plazo máximo de 48 horas.</p>
        
        <p><strong>Moderación:</strong> El foro es moderado por el equipo docente para garantizar un ambiente de aprendizaje productivo.</p>
        '
    ]
];

// Actualizar contenido de las actividades
$updated_count = 0;
foreach ($detailed_content as $activity_name => $data) {
    // Buscar la actividad por nombre
    $activity = $DB->get_record_sql("
        SELECT cm.*, m.name as modulename 
        FROM {course_modules} cm 
        JOIN {modules} m ON cm.module = m.id 
        WHERE cm.course = ? AND cm.name = ?
    ", [$course->id, $activity_name]);
    
    if ($activity) {
        // Actualizar según el tipo de actividad
        switch ($activity->modulename) {
            case 'label':
                // Actualizar contenido de etiqueta (anuncios)
                $DB->update_record('label', [
                    'id' => $activity->instance,
                    'intro' => $data['content']
                ]);
                break;
                
            case 'resource':
                // Actualizar contenido de recurso
                $DB->update_record('resource', [
                    'id' => $activity->instance,
                    'intro' => $data['content']
                ]);
                break;
                
            case 'assign':
                // Actualizar contenido de tarea
                $DB->update_record('assign', [
                    'id' => $activity->instance,
                    'intro' => $data['content']
                ]);
                break;
                
            case 'forum':
                // Actualizar contenido de foro
                $DB->update_record('forum', [
                    'id' => $activity->instance,
                    'intro' => $data['content']
                ]);
                break;
        }
        
        $updated_count++;
        echo "✅ Contenido actualizado: $activity_name\n";
    } else {
        echo "⚠️  Actividad no encontrada: $activity_name\n";
    }
}

echo "\n🎯 Contenido detallado agregado exitosamente\n";
echo "📊 Actividades actualizadas: $updated_count\n";
echo "🔗 Acceso al curso: http://34.72.133.6/course/view.php?id={$course->id}\n";

// Crear instrucciones para subir tareas
echo "\n📋 Creando instrucciones para subir tareas...\n";
create_task_instructions($course->id);

echo "\n✅ Proceso completado exitosamente\n";

/**
 * Crear instrucciones para subir tareas
 */
function create_task_instructions($course_id) {
    global $DB;
    
    $instructions_content = '
    <h2>INDICACIONES PARA SUBIR TAREAS</h2>
    
    <ol>
        <li>Una vez ubicado en la tarea, haga clic en: <strong>VER INSTRUCCIONES</strong></li>
        <li>En la parte inferior encontrará una sección llamada <strong>ESTADO DE LA ENTREGA</strong></li>
        <li>Haga clic en <strong>AGREGAR ENTREGA</strong></li>
        <li>Seleccione el tipo de entrega según se solicite:
            <ul>
                <li><strong>Texto en línea:</strong> Para escribir directamente</li>
                <li><strong>Archivo:</strong> Para subir documentos</li>
                <li><strong>Grabación de medios:</strong> Para audio/video</li>
            </ul>
        </li>
        <li>Complete su trabajo y haga clic en <strong>GUARDAR CAMBIOS</strong></li>
        <li>Finalmente, haga clic en <strong>ENVIAR TAREA</strong> para completar la entrega</li>
    </ol>
    
    <h3>Formatos aceptados:</h3>
    <ul>
        <li>Documentos: PDF, DOC, DOCX</li>
        <li>Imágenes: JPG, PNG (para capturas de pantalla)</li>
        <li>Videos: MP4, AVI (máximo 100MB)</li>
        <li>Audio: MP3, WAV (máximo 50MB)</li>
    </ul>
    
    <h3>Recomendaciones:</h3>
    <ul>
        <li>Verifique que su archivo se haya subido correctamente</li>
        <li>Nombrar los archivos de forma descriptiva</li>
        <li>Revisar las instrucciones antes de enviar</li>
        <li>Enviar antes de la fecha límite</li>
    </ul>
    
    <p><strong>Nota:</strong> Una vez enviada la tarea, no podrá modificarla a menos que el instructor habilite nuevas entregas.</p>
    ';
    
    // Crear una etiqueta con las instrucciones
    $label = new stdClass();
    $label->course = $course_id;
    $label->name = 'Instrucciones para subir tareas';
    $label->intro = $instructions_content;
    $label->introformat = 1;
    $label->timecreated = time();
    $label->timemodified = time();
    
    $label_id = $DB->insert_record('label', $label);
    
    // Crear el módulo del curso
    $module_info = $DB->get_record('modules', ['name' => 'label']);
    $section = $DB->get_record('course_sections', ['course' => $course_id, 'section' => 0]);
    
    $cm = new stdClass();
    $cm->course = $course_id;
    $cm->module = $module_info->id;
    $cm->instance = $label_id;
    $cm->section = $section->id;
    $cm->visible = 1;
    $cm->timecreated = time();
    $cm->timemodified = time();
    
    $DB->insert_record('course_modules', $cm);
    
    echo "✅ Instrucciones para subir tareas creadas\n";
}

?>