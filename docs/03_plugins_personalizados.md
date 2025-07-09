# 🔧 Plugins Personalizados para Funcionalidades Médicas

## 🎯 Plugins Esenciales a Desarrollar

### 1. **local_medical_dashboard** - Dashboard Médico
### 2. **local_learning_paths** - Rutas de Aprendizaje
### 3. **mod_medical_simulation** - Simuladores Médicos
### 4. **local_certifications** - Gestión de Certificaciones
### 5. **block_medical_progress** - Bloque de Progreso Médico

---

## 🏥 Plugin: Medical Dashboard

### Estructura Base

```bash
plugins/local/medical_dashboard/
├── version.php
├── db/
│   ├── access.php
│   ├── install.xml
│   └── upgrade.php
├── lang/
│   └── en/
│       └── local_medical_dashboard.php
├── classes/
│   ├── api.php
│   ├── dashboard.php
│   └── specialty_manager.php
├── templates/
│   ├── dashboard.mustache
│   └── specialty_card.mustache
├── amd/
│   └── src/
│       └── dashboard.js
└── index.php
```

### Código Principal

**version.php**
```php
<?php
defined('MOODLE_INTERNAL') || die();

$plugin->version = 2025070701;
$plugin->requires = 2020110900;
$plugin->component = 'local_medical_dashboard';
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.0.0';
```

**classes/api.php**
```php
<?php
namespace local_medical_dashboard;

class api {
    
    public static function get_user_dashboard_data($userid) {
        global $DB, $USER;
        
        $data = [
            'user' => $USER,
            'specialties' => self::get_user_specialties($userid),
            'learning_progress' => self::get_learning_progress($userid),
            'current_courses' => self::get_current_courses($userid),
            'certifications' => self::get_certifications($userid),
            'achievements' => self::get_achievements($userid),
            'stats' => self::get_user_stats($userid)
        ];
        
        return $data;
    }
    
    private static function get_user_specialties($userid) {
        global $DB;
        
        $sql = "SELECT ms.*, mus.level, mus.hours_completed 
                FROM {medical_specialties} ms
                JOIN {medical_user_specialties} mus ON ms.id = mus.specialty_id
                WHERE mus.user_id = ?";
                
        return $DB->get_records_sql($sql, [$userid]);
    }
    
    private static function get_learning_progress($userid) {
        global $DB;
        
        $sql = "SELECT mlp.name, mp.progress_percentage, mp.hours_completed
                FROM {medical_learning_paths} mlp
                JOIN {medical_user_progress} mp ON mlp.id = mp.learning_path_id
                WHERE mp.user_id = ? AND mp.progress_percentage > 0
                ORDER BY mp.last_activity DESC";
                
        return $DB->get_records_sql($sql, [$userid]);
    }
    
    private static function get_user_stats($userid) {
        global $DB;
        
        $stats = new \stdClass();
        $stats->total_courses = $DB->count_records('course_completions', ['userid' => $userid]);
        $stats->completed_courses = $DB->count_records('course_completions', 
            ['userid' => $userid, 'timecompleted' => ['!=', null]]);
        $stats->total_hours = $DB->get_field_sql(
            "SELECT SUM(hours_completed) FROM {medical_user_progress} WHERE user_id = ?", 
            [$userid]) ?: 0;
        $stats->achievements = $DB->count_records('medical_achievements', 
            ['userid' => $userid]);
            
        return $stats;
    }
}
```

**index.php**
```php
<?php
require_once('../../config.php');
require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/local/medical_dashboard/index.php');
$PAGE->set_title('Dashboard Médico');
$PAGE->set_heading('Dashboard Médico');

$dashboard_data = \local_medical_dashboard\api::get_user_dashboard_data($USER->id);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_medical_dashboard/dashboard', $dashboard_data);
echo $OUTPUT->footer();
```

---

## 🛣️ Plugin: Learning Paths

### Funcionalidades Clave

**classes/learning_path.php**
```php
<?php
namespace local_learning_paths;

class learning_path {
    
    public function create_path($name, $specialty_id, $courses) {
        global $DB;
        
        $path = new \stdClass();
        $path->name = $name;
        $path->specialty_id = $specialty_id;
        $path->description = '';
        $path->total_hours = $this->calculate_total_hours($courses);
        $path->difficulty_level = $this->calculate_difficulty($courses);
        $path->created_at = time();
        $path->updated_at = time();
        
        $path_id = $DB->insert_record('medical_learning_paths', $path);
        
        // Asociar cursos a la ruta
        foreach ($courses as $order => $course_id) {
            $path_course = new \stdClass();
            $path_course->learning_path_id = $path_id;
            $path_course->course_id = $course_id;
            $path_course->order_in_path = $order + 1;
            $path_course->is_required = true;
            
            $DB->insert_record('medical_learning_path_courses', $path_course);
        }
        
        return $path_id;
    }
    
    public function get_user_progress($userid, $path_id) {
        global $DB;
        
        $sql = "SELECT 
                    mlp.name as path_name,
                    mp.progress_percentage,
                    mp.hours_completed,
                    mp.started_at,
                    mp.completed_at,
                    COUNT(mlpc.id) as total_courses,
                    SUM(CASE WHEN cc.timecompleted IS NOT NULL THEN 1 ELSE 0 END) as completed_courses
                FROM {medical_learning_paths} mlp
                JOIN {medical_user_progress} mp ON mlp.id = mp.learning_path_id
                LEFT JOIN {medical_learning_path_courses} mlpc ON mlp.id = mlpc.learning_path_id
                LEFT JOIN {course_completions} cc ON mlpc.course_id = cc.course AND cc.userid = ?
                WHERE mp.user_id = ? AND mlp.id = ?
                GROUP BY mlp.id, mp.id";
                
        return $DB->get_record_sql($sql, [$userid, $userid, $path_id]);
    }
    
    public function enroll_user($userid, $path_id) {
        global $DB;
        
        // Verificar si ya está inscrito
        if ($DB->record_exists('medical_user_progress', 
            ['user_id' => $userid, 'learning_path_id' => $path_id])) {
            return false;
        }
        
        $enrollment = new \stdClass();
        $enrollment->user_id = $userid;
        $enrollment->learning_path_id = $path_id;
        $enrollment->progress_percentage = 0;
        $enrollment->hours_completed = 0;
        $enrollment->started_at = time();
        
        $DB->insert_record('medical_user_progress', $enrollment);
        
        // Inscribir en todos los cursos de la ruta
        $courses = $DB->get_records('medical_learning_path_courses', 
            ['learning_path_id' => $path_id]);
            
        foreach ($courses as $path_course) {
            enrol_try_internal_enrol($path_course->course_id, $userid);
        }
        
        return true;
    }
}
```

**Rutas Predefinidas por Especialidad**
```php
<?php
// scripts/create_default_paths.php

$cardiology_path = [
    'name' => 'Cardiología Básica',
    'specialty' => 'cardiology',
    'courses' => [
        'Anatomía Cardiovascular',
        'Fisiología Cardíaca',
        'Electrocardiografía',
        'Ecocardiografía',
        'Cateterismo Cardíaco',
        'Emergencias Cardiológicas'
    ]
];

$neurology_path = [
    'name' => 'Neurología Clínica',
    'specialty' => 'neurology',
    'courses' => [
        'Neuroanatomía',
        'Neurofisiología',
        'Semiología Neurológica',
        'Neuroimagen',
        'Trastornos del Movimiento',
        'Epilepsia'
    ]
];

$emergency_path = [
    'name' => 'Medicina de Emergencia',
    'specialty' => 'emergency',
    'courses' => [
        'Triaje Hospitalario',
        'RCP Avanzado',
        'Manejo de Vía Aérea',
        'Shock y Resucitación',
        'Traumatología de Emergencia',
        'Toxicología Clínica'
    ]
];
```

---

## 🔬 Plugin: Medical Simulation

### Simulador Interactivo

**classes/simulation_engine.php**
```php
<?php
namespace mod_medical_simulation;

class simulation_engine {
    
    public function create_case_study($title, $specialty, $scenario) {
        global $DB;
        
        $case = new \stdClass();
        $case->title = $title;
        $case->specialty_id = $specialty;
        $case->scenario_data = json_encode($scenario);
        $case->difficulty_level = $scenario['difficulty'];
        $case->estimated_time = $scenario['time_minutes'];
        $case->created_at = time();
        
        return $DB->insert_record('medical_simulations', $case);
    }
    
    public function process_user_response($simulation_id, $user_id, $responses) {
        global $DB;
        
        $simulation = $DB->get_record('medical_simulations', ['id' => $simulation_id]);
        $scenario = json_decode($simulation->scenario_data, true);
        
        $score = $this->calculate_score($responses, $scenario['correct_answers']);
        $feedback = $this->generate_feedback($responses, $scenario);
        
        $attempt = new \stdClass();
        $attempt->simulation_id = $simulation_id;
        $attempt->user_id = $user_id;
        $attempt->responses = json_encode($responses);
        $attempt->score = $score;
        $attempt->feedback = json_encode($feedback);
        $attempt->completed_at = time();
        
        $DB->insert_record('medical_simulation_attempts', $attempt);
        
        return [
            'score' => $score,
            'feedback' => $feedback,
            'passed' => $score >= $scenario['passing_score']
        ];
    }
    
    private function calculate_score($responses, $correct_answers) {
        $total_questions = count($correct_answers);
        $correct_count = 0;
        
        foreach ($responses as $question_id => $response) {
            if (isset($correct_answers[$question_id])) {
                if ($correct_answers[$question_id] === $response) {
                    $correct_count++;
                }
            }
        }
        
        return ($correct_count / $total_questions) * 100;
    }
}
```

**templates/simulation.mustache**
```mustache
<div class="medical-simulation">
    <div class="simulation-header">
        <h2>{{title}}</h2>
        <div class="simulation-info">
            <span class="specialty">{{specialty_name}}</span>
            <span class="difficulty">Dificultad: {{difficulty_level}}/5</span>
            <span class="time">⏱️ {{estimated_time}} min</span>
        </div>
    </div>
    
    <div class="simulation-content">
        <div class="case-presentation">
            <h3>Presentación del Caso</h3>
            <div class="patient-info">
                <div class="patient-avatar">
                    <img src="{{patient_image}}" alt="Paciente">
                </div>
                <div class="patient-details">
                    <p><strong>Edad:</strong> {{patient_age}} años</p>
                    <p><strong>Género:</strong> {{patient_gender}}</p>
                    <p><strong>Motivo de consulta:</strong> {{chief_complaint}}</p>
                </div>
            </div>
            
            <div class="case-history">
                <h4>Historia Clínica</h4>
                <p>{{case_history}}</p>
            </div>
        </div>
        
        <div class="simulation-questions">
            {{#questions}}
            <div class="question-block">
                <h4>{{question_text}}</h4>
                
                {{#is_multiple_choice}}
                <div class="multiple-choice">
                    {{#options}}
                    <label class="option">
                        <input type="radio" name="question_{{question_id}}" value="{{option_id}}">
                        <span>{{option_text}}</span>
                    </label>
                    {{/options}}
                </div>
                {{/is_multiple_choice}}
                
                {{#is_interactive}}
                <div class="interactive-element">
                    <div class="body-diagram" data-question="{{question_id}}">
                        <img src="{{diagram_image}}" alt="Diagrama corporal">
                        {{#clickable_areas}}
                        <div class="clickable-area" 
                             data-area="{{area_id}}"
                             style="left: {{x}}%; top: {{y}}%; width: {{width}}%; height: {{height}}%;">
                        </div>
                        {{/clickable_areas}}
                    </div>
                </div>
                {{/is_interactive}}
            </div>
            {{/questions}}
        </div>
        
        <div class="simulation-actions">
            <button class="btn btn-primary" onclick="submitSimulation()">
                Completar Simulación
            </button>
            <button class="btn btn-secondary" onclick="saveProgress()">
                Guardar Progreso
            </button>
        </div>
    </div>
</div>
```

---

## 🏆 Plugin: Certifications Manager

### Gestión de Certificaciones

**classes/certification_manager.php**
```php
<?php
namespace local_certifications;

class certification_manager {
    
    public function award_certification($user_id, $certification_type, $specialty_id) {
        global $DB;
        
        $certification = new \stdClass();
        $certification->user_id = $user_id;
        $certification->specialty_id = $specialty_id;
        $certification->certification_name = $certification_type;
        $certification->issuing_body = 'Plataforma Médica';
        $certification->issue_date = time();
        $certification->expiry_date = $this->calculate_expiry_date($certification_type);
        $certification->certificate_url = $this->generate_certificate_url($certification);
        $certification->is_active = 1;
        
        $cert_id = $DB->insert_record('medical_certifications', $certification);
        
        // Generar certificado PDF
        $this->generate_certificate_pdf($cert_id);
        
        // Enviar notificación
        $this->send_certification_notification($user_id, $certification_type);
        
        return $cert_id;
    }
    
    public function check_certification_requirements($user_id, $certification_type) {
        global $DB;
        
        $requirements = $this->get_certification_requirements($certification_type);
        $user_progress = $this->get_user_progress($user_id);
        
        $met_requirements = [];
        $pending_requirements = [];
        
        foreach ($requirements as $requirement) {
            if ($this->is_requirement_met($user_progress, $requirement)) {
                $met_requirements[] = $requirement;
            } else {
                $pending_requirements[] = $requirement;
            }
        }
        
        return [
            'eligible' => empty($pending_requirements),
            'met_requirements' => $met_requirements,
            'pending_requirements' => $pending_requirements,
            'completion_percentage' => count($met_requirements) / count($requirements) * 100
        ];
    }
    
    private function get_certification_requirements($certification_type) {
        $requirements = [
            'basic_cardiology' => [
                'courses' => ['cardiology_basics', 'ecg_interpretation', 'cardiac_procedures'],
                'hours' => 40,
                'simulations' => ['cardiac_emergency', 'catheterization_sim'],
                'passing_score' => 80
            ],
            'emergency_medicine' => [
                'courses' => ['emergency_protocols', 'trauma_management', 'cpr_advanced'],
                'hours' => 60,
                'simulations' => ['trauma_case', 'cardiac_arrest', 'poisoning_case'],
                'passing_score' => 85
            ]
        ];
        
        return $requirements[$certification_type] ?? [];
    }
    
    private function generate_certificate_pdf($cert_id) {
        global $DB;
        
        $cert = $DB->get_record('medical_certifications', ['id' => $cert_id]);
        $user = $DB->get_record('user', ['id' => $cert->user_id]);
        
        // Usar biblioteca PDF como TCPDF
        require_once('tcpdf/tcpdf.php');
        
        $pdf = new \TCPDF();
        $pdf->AddPage();
        
        // Diseño del certificado
        $html = $this->get_certificate_template($cert, $user);
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Guardar archivo
        $filename = 'certificate_' . $cert_id . '.pdf';
        $filepath = $this->get_certificates_directory() . '/' . $filename;
        $pdf->Output($filepath, 'F');
        
        return $filepath;
    }
}
```

---

## 📊 Plugin: Medical Progress Block

### Bloque de Progreso Visual

**block_medical_progress.php**
```php
<?php
class block_medical_progress extends block_base {
    
    public function init() {
        $this->title = get_string('pluginname', 'block_medical_progress');
    }
    
    public function get_content() {
        global $USER, $DB;
        
        if ($this->content !== null) {
            return $this->content;
        }
        
        $this->content = new stdClass();
        
        // Obtener datos de progreso
        $progress_data = $this->get_user_progress_data($USER->id);
        
        $this->content->text = $this->render_progress_content($progress_data);
        $this->content->footer = '';
        
        return $this->content;
    }
    
    private function get_user_progress_data($userid) {
        global $DB;
        
        $sql = "SELECT 
                    ms.name as specialty_name,
                    ms.color as specialty_color,
                    mp.progress_percentage,
                    mp.hours_completed,
                    mlp.total_hours,
                    mlp.name as path_name
                FROM {medical_user_progress} mp
                JOIN {medical_learning_paths} mlp ON mp.learning_path_id = mlp.id
                JOIN {medical_specialties} ms ON mlp.specialty_id = ms.id
                WHERE mp.user_id = ?
                ORDER BY mp.last_activity DESC";
                
        return $DB->get_records_sql($sql, [$userid]);
    }
    
    private function render_progress_content($progress_data) {
        $html = '<div class="medical-progress-block">';
        
        foreach ($progress_data as $progress) {
            $html .= '<div class="progress-item">';
            $html .= '<div class="progress-header">';
            $html .= '<span class="specialty-name" style="color: ' . $progress->specialty_color . '">';
            $html .= $progress->specialty_name . '</span>';
            $html .= '<span class="progress-percentage">' . $progress->progress_percentage . '%</span>';
            $html .= '</div>';
            
            $html .= '<div class="progress-bar-container">';
            $html .= '<div class="progress-bar" style="width: ' . $progress->progress_percentage . '%; background-color: ' . $progress->specialty_color . '"></div>';
            $html .= '</div>';
            
            $html .= '<div class="progress-details">';
            $html .= '<small>' . $progress->path_name . '</small>';
            $html .= '<small>' . $progress->hours_completed . '/' . $progress->total_hours . ' horas</small>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
```

---

## 🚀 Instalación y Configuración

### Script de Instalación Automática

**scripts/install_plugins.sh**
```bash
#!/bin/bash

# Instalar plugins médicos
echo "Instalando plugins médicos..."

# Copiar plugins a Moodle
cp -r plugins/local/medical_dashboard /var/www/html/local/
cp -r plugins/local/learning_paths /var/www/html/local/
cp -r plugins/mod/medical_simulation /var/www/html/mod/
cp -r plugins/local/certifications /var/www/html/local/
cp -r plugins/blocks/medical_progress /var/www/html/blocks/

# Ejecutar actualización de base de datos
php admin/cli/upgrade.php --non-interactive

# Configurar permisos
chown -R www-data:www-data /var/www/html/local/
chown -R www-data:www-data /var/www/html/mod/
chown -R www-data:www-data /var/www/html/blocks/

echo "Plugins instalados correctamente"
```

### Configuración Inicial

**scripts/setup_medical_data.php**
```php
<?php
// Crear especialidades médicas por defecto
$specialties = [
    ['name' => 'Cardiología', 'code' => 'CARD', 'icon' => 'heart', 'color' => '#e74c3c'],
    ['name' => 'Neurología', 'code' => 'NEURO', 'icon' => 'brain', 'color' => '#9b59b6'],
    ['name' => 'Medicina de Emergencia', 'code' => 'EMERG', 'icon' => 'ambulance', 'color' => '#c0392b'],
    ['name' => 'Pediatría', 'code' => 'PED', 'icon' => 'child', 'color' => '#f39c12'],
    ['name' => 'Cirugía General', 'code' => 'SURG', 'icon' => 'cut', 'color' => '#34495e'],
];

foreach ($specialties as $specialty) {
    $DB->insert_record('medical_specialties', (object)$specialty);
}

// Crear rutas de aprendizaje por defecto
$learning_paths = [
    ['name' => 'Cardiología Básica', 'specialty_id' => 1, 'total_hours' => 40],
    ['name' => 'Neurología Clínica', 'specialty_id' => 2, 'total_hours' => 35],
    ['name' => 'Medicina de Emergencia', 'specialty_id' => 3, 'total_hours' => 50],
];

foreach ($learning_paths as $path) {
    $DB->insert_record('medical_learning_paths', (object)$path);
}

echo "Datos médicos inicializados correctamente";
```

---

## 🔧 Configuración del Deployment

### Docker para Desarrollo

**docker-compose.plugins.yml**
```yaml
version: '3.8'
services:
  moodle-dev:
    build: .
    ports:
      - "8080:80"
    volumes:
      - ./plugins:/var/www/html/local
      - ./themes:/var/www/html/theme
    environment:
      - MOODLE_PLUGINS_AUTO_INSTALL=true
    depends_on:
      - db
      - redis
```

### Kubernetes Deployment

**k8s/medical-plugins-deployment.yaml**
```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: moodle-medical
spec:
  replicas: 2
  selector:
    matchLabels:
      app: moodle-medical
  template:
    metadata:
      labels:
        app: moodle-medical
    spec:
      containers:
      - name: moodle
        image: moodle-medical:latest
        ports:
        - containerPort: 80
        env:
        - name: MOODLE_DATABASE_HOST
          value: "moodle-mysql.moodle-db.svc.cluster.local"
        volumeMounts:
        - name: plugins-volume
          mountPath: /var/www/html/local
        - name: themes-volume
          mountPath: /var/www/html/theme
      volumes:
      - name: plugins-volume
        configMap:
          name: medical-plugins
      - name: themes-volume
        configMap:
          name: medical-themes
```

---

## ✅ Checklist de Desarrollo

- [ ] Plugin Medical Dashboard instalado
- [ ] Rutas de aprendizaje configuradas
- [ ] Simuladores médicos funcionando
- [ ] Sistema de certificaciones activo
- [ ] Bloques de progreso desplegados
- [ ] Datos médicos inicializados
- [ ] Tests unitarios pasando
- [ ] Documentación actualizada

---

**Próximo paso**: APIs y integraciones externas