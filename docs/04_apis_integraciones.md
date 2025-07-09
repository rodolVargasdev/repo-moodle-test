# 🔗 APIs e Integraciones Externas

## 🎯 Integraciones Clave

### 1. **APIs de Sistemas Hospitalarios** (HL7 FHIR)
### 2. **Organismos Certificadores** (Colegios Médicos)
### 3. **Plataformas de Teleconsulta** (Zoom, Teams)
### 4. **Bibliotecas Médicas** (PubMed, UpToDate)
### 5. **Sistemas de Análisis** (Google Analytics, Mixpanel)

---

## 🏥 API: Hospital Systems Integration

### Integración con HL7 FHIR

**classes/hospital_api.php**
```php
<?php
namespace local_medical_api;

class hospital_api {
    
    private $fhir_endpoint;
    private $auth_token;
    
    public function __construct($config) {
        $this->fhir_endpoint = $config['fhir_endpoint'];
        $this->auth_token = $config['auth_token'];
    }
    
    public function get_patient_data($patient_id) {
        $url = $this->fhir_endpoint . '/Patient/' . $patient_id;
        
        $response = $this->make_request('GET', $url);
        
        if ($response['status'] === 200) {
            return $this->parse_patient_data($response['data']);
        }
        
        return false;
    }
    
    public function get_practitioner_credentials($practitioner_id) {
        $url = $this->fhir_endpoint . '/Practitioner/' . $practitioner_id;
        
        $response = $this->make_request('GET', $url);
        
        if ($response['status'] === 200) {
            return $this->parse_practitioner_data($response['data']);
        }
        
        return false;
    }
    
    public function sync_user_credentials($moodle_user_id, $hospital_id) {
        global $DB;
        
        $practitioner_data = $this->get_practitioner_credentials($hospital_id);
        
        if ($practitioner_data) {
            // Actualizar datos del usuario en Moodle
            $user_update = new \stdClass();
            $user_update->id = $moodle_user_id;
            $user_update->institution = $practitioner_data['organization'];
            $user_update->department = $practitioner_data['specialty'];
            
            $DB->update_record('user', $user_update);
            
            // Sincronizar especialidades
            $this->sync_user_specialties($moodle_user_id, $practitioner_data['qualifications']);
            
            return true;
        }
        
        return false;
    }
    
    private function make_request($method, $url, $data = null) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->auth_token,
            'Content-Type: application/fhir+json',
            'Accept: application/fhir+json'
        ]);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status' => $status,
            'data' => json_decode($response, true)
        ];
    }
    
    private function parse_patient_data($fhir_data) {
        return [
            'id' => $fhir_data['id'],
            'name' => $fhir_data['name'][0]['given'][0] . ' ' . $fhir_data['name'][0]['family'],
            'birthDate' => $fhir_data['birthDate'],
            'gender' => $fhir_data['gender'],
            'phone' => $fhir_data['telecom'][0]['value'] ?? null,
            'address' => $fhir_data['address'][0] ?? null
        ];
    }
}
```

### API REST para Moodle

**api/medical_api.php**
```php
<?php
require_once('../../../config.php');

// API REST para integraciones externas
class medical_rest_api {
    
    public function handle_request() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_SERVER['PATH_INFO'] ?? '';
        
        // Autenticación
        if (!$this->authenticate()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        switch ($method) {
            case 'GET':
                $this->handle_get($path);
                break;
            case 'POST':
                $this->handle_post($path);
                break;
            case 'PUT':
                $this->handle_put($path);
                break;
            case 'DELETE':
                $this->handle_delete($path);
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    }
    
    private function handle_get($path) {
        switch ($path) {
            case '/users':
                $this->get_users();
                break;
            case '/specialties':
                $this->get_specialties();
                break;
            case '/learning-paths':
                $this->get_learning_paths();
                break;
            case '/certifications':
                $this->get_certifications();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
        }
    }
    
    private function get_users() {
        global $DB;
        
        $users = $DB->get_records_sql("
            SELECT u.id, u.username, u.firstname, u.lastname, u.email,
                   GROUP_CONCAT(ms.name) as specialties
            FROM {user} u
            LEFT JOIN {medical_user_specialties} mus ON u.id = mus.user_id
            LEFT JOIN {medical_specialties} ms ON mus.specialty_id = ms.id
            WHERE u.deleted = 0
            GROUP BY u.id
            LIMIT 100
        ");
        
        header('Content-Type: application/json');
        echo json_encode(array_values($users));
    }
    
    private function get_specialties() {
        global $DB;
        
        $specialties = $DB->get_records('medical_specialties');
        
        header('Content-Type: application/json');
        echo json_encode(array_values($specialties));
    }
    
    private function authenticate() {
        $headers = getallheaders();
        $auth_header = $headers['Authorization'] ?? '';
        
        if (strpos($auth_header, 'Bearer ') === 0) {
            $token = substr($auth_header, 7);
            return $this->validate_token($token);
        }
        
        return false;
    }
    
    private function validate_token($token) {
        global $DB;
        
        // Validar token en base de datos
        $valid_token = $DB->get_record('medical_api_tokens', [
            'token' => $token,
            'is_active' => 1
        ]);
        
        if ($valid_token && $valid_token->expires_at > time()) {
            return true;
        }
        
        return false;
    }
}

// Ejecutar API
$api = new medical_rest_api();
$api->handle_request();
```

---

## 🏆 Integración con Organismos Certificadores

### API para Colegios Médicos

**classes/certification_api.php**
```php
<?php
namespace local_certifications;

class certification_api {
    
    private $college_endpoints = [
        'mexico' => 'https://api.colegiomedico.mx',
        'colombia' => 'https://api.colmedica.co',
        'argentina' => 'https://api.colmedarg.org'
    ];
    
    public function validate_medical_license($license_number, $country) {
        $endpoint = $this->college_endpoints[$country] ?? null;
        
        if (!$endpoint) {
            return ['valid' => false, 'error' => 'Country not supported'];
        }
        
        $url = $endpoint . '/validate-license/' . $license_number;
        
        $response = $this->make_request('GET', $url);
        
        if ($response['status'] === 200) {
            return [
                'valid' => $response['data']['valid'],
                'doctor_name' => $response['data']['name'],
                'specialties' => $response['data']['specialties'],
                'license_status' => $response['data']['status'],
                'expiry_date' => $response['data']['expiry_date']
            ];
        }
        
        return ['valid' => false, 'error' => 'Validation failed'];
    }
    
    public function submit_certification($user_id, $certification_data) {
        global $DB;
        
        $user = $DB->get_record('user', ['id' => $user_id]);
        $country = $user->country;
        
        $endpoint = $this->college_endpoints[$country] ?? null;
        
        if (!$endpoint) {
            return false;
        }
        
        $url = $endpoint . '/submit-certification';
        
        $payload = [
            'license_number' => $certification_data['license_number'],
            'certification_type' => $certification_data['type'],
            'completion_date' => $certification_data['completion_date'],
            'hours_completed' => $certification_data['hours'],
            'institution' => 'Plataforma Médica Digital',
            'verification_code' => $this->generate_verification_code($certification_data)
        ];
        
        $response = $this->make_request('POST', $url, $payload);
        
        if ($response['status'] === 200) {
            // Actualizar estado en Moodle
            $this->update_certification_status($user_id, $certification_data, 'submitted');
            return true;
        }
        
        return false;
    }
    
    private function generate_verification_code($data) {
        return hash('sha256', json_encode($data) . time());
    }
}
```

---

## 📹 Integración con Plataformas de Teleconsulta

### Zoom Integration

**classes/zoom_integration.php**
```php
<?php
namespace local_medical_api;

class zoom_integration {
    
    private $zoom_api_key;
    private $zoom_api_secret;
    private $zoom_jwt_token;
    
    public function __construct($config) {
        $this->zoom_api_key = $config['zoom_api_key'];
        $this->zoom_api_secret = $config['zoom_api_secret'];
        $this->zoom_jwt_token = $this->generate_jwt_token();
    }
    
    public function create_medical_meeting($course_id, $doctor_id, $patient_id = null) {
        global $DB;
        
        $course = $DB->get_record('course', ['id' => $course_id]);
        $doctor = $DB->get_record('user', ['id' => $doctor_id]);
        
        $meeting_data = [
            'topic' => 'Consulta Médica - ' . $course->fullname,
            'type' => 2, // Scheduled meeting
            'start_time' => date('Y-m-d\TH:i:s\Z', time() + 3600),
            'duration' => 60,
            'timezone' => 'UTC',
            'settings' => [
                'host_video' => true,
                'participant_video' => true,
                'audio' => 'both',
                'auto_recording' => 'cloud',
                'waiting_room' => true,
                'join_before_host' => false
            ]
        ];
        
        $url = 'https://api.zoom.us/v2/users/' . $doctor->email . '/meetings';
        
        $response = $this->make_zoom_request('POST', $url, $meeting_data);
        
        if ($response['status'] === 201) {
            // Guardar información de la reunión
            $meeting = new \stdClass();
            $meeting->course_id = $course_id;
            $meeting->doctor_id = $doctor_id;
            $meeting->patient_id = $patient_id;
            $meeting->zoom_meeting_id = $response['data']['id'];
            $meeting->join_url = $response['data']['join_url'];
            $meeting->start_url = $response['data']['start_url'];
            $meeting->created_at = time();
            
            $DB->insert_record('medical_zoom_meetings', $meeting);
            
            return $meeting;
        }
        
        return false;
    }
    
    public function get_meeting_recordings($meeting_id) {
        $url = 'https://api.zoom.us/v2/meetings/' . $meeting_id . '/recordings';
        
        $response = $this->make_zoom_request('GET', $url);
        
        if ($response['status'] === 200) {
            return $response['data']['recording_files'];
        }
        
        return [];
    }
    
    private function make_zoom_request($method, $url, $data = null) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->zoom_jwt_token,
            'Content-Type: application/json'
        ]);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status' => $status,
            'data' => json_decode($response, true)
        ];
    }
    
    private function generate_jwt_token() {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'iss' => $this->zoom_api_key,
            'exp' => time() + 3600
        ]);
        
        $base64_header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64_payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64_header . "." . $base64_payload, $this->zoom_api_secret, true);
        $base64_signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64_header . "." . $base64_payload . "." . $base64_signature;
    }
}
```

---

## 📚 Integración con Bibliotecas Médicas

### PubMed API Integration

**classes/pubmed_api.php**
```php
<?php
namespace local_medical_library;

class pubmed_api {
    
    private $base_url = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/';
    private $api_key;
    
    public function __construct($api_key) {
        $this->api_key = $api_key;
    }
    
    public function search_articles($query, $max_results = 20) {
        $search_url = $this->base_url . 'esearch.fcgi';
        $params = [
            'db' => 'pubmed',
            'term' => $query,
            'retmax' => $max_results,
            'retmode' => 'json',
            'api_key' => $this->api_key
        ];
        
        $url = $search_url . '?' . http_build_query($params);
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        
        if (isset($data['esearchresult']['idlist'])) {
            return $this->get_article_details($data['esearchresult']['idlist']);
        }
        
        return [];
    }
    
    public function get_article_details($pmids) {
        $fetch_url = $this->base_url . 'efetch.fcgi';
        $params = [
            'db' => 'pubmed',
            'id' => implode(',', $pmids),
            'retmode' => 'xml',
            'api_key' => $this->api_key
        ];
        
        $url = $fetch_url . '?' . http_build_query($params);
        $xml_response = file_get_contents($url);
        
        return $this->parse_pubmed_xml($xml_response);
    }
    
    public function get_recommendations_for_specialty($specialty) {
        $specialty_queries = [
            'cardiology' => 'cardiology[MeSH] AND ("last 5 years"[PDat])',
            'neurology' => 'neurology[MeSH] AND ("last 5 years"[PDat])',
            'emergency' => 'emergency medicine[MeSH] AND ("last 5 years"[PDat])',
            'pediatrics' => 'pediatrics[MeSH] AND ("last 5 years"[PDat])'
        ];
        
        $query = $specialty_queries[$specialty] ?? $specialty;
        return $this->search_articles($query, 10);
    }
    
    private function parse_pubmed_xml($xml) {
        $articles = [];
        $xml_obj = simplexml_load_string($xml);
        
        foreach ($xml_obj->PubmedArticle as $article) {
            $pmid = (string)$article->MedlineCitation->PMID;
            $title = (string)$article->MedlineCitation->Article->ArticleTitle;
            $abstract = (string)$article->MedlineCitation->Article->Abstract->AbstractText;
            $authors = [];
            
            if (isset($article->MedlineCitation->Article->AuthorList)) {
                foreach ($article->MedlineCitation->Article->AuthorList->Author as $author) {
                    $authors[] = (string)$author->LastName . ', ' . (string)$author->ForeName;
                }
            }
            
            $articles[] = [
                'pmid' => $pmid,
                'title' => $title,
                'abstract' => $abstract,
                'authors' => $authors,
                'url' => 'https://pubmed.ncbi.nlm.nih.gov/' . $pmid
            ];
        }
        
        return $articles;
    }
    
    public function create_course_bibliography($course_id, $specialty) {
        global $DB;
        
        $articles = $this->get_recommendations_for_specialty($specialty);
        
        foreach ($articles as $article) {
            $bibliography = new \stdClass();
            $bibliography->course_id = $course_id;
            $bibliography->title = $article['title'];
            $bibliography->authors = implode('; ', $article['authors']);
            $bibliography->abstract = $article['abstract'];
            $bibliography->pubmed_id = $article['pmid'];
            $bibliography->url = $article['url'];
            $bibliography->created_at = time();
            
            $DB->insert_record('medical_course_bibliography', $bibliography);
        }
        
        return count($articles);
    }
}
```

---

## 📊 Integración con Sistemas de Análisis

### Google Analytics 4 Integration

**classes/analytics_integration.php**
```php
<?php
namespace local_medical_analytics;

class analytics_integration {
    
    private $ga4_measurement_id;
    private $ga4_api_secret;
    
    public function __construct($config) {
        $this->ga4_measurement_id = $config['ga4_measurement_id'];
        $this->ga4_api_secret = $config['ga4_api_secret'];
    }
    
    public function track_course_completion($user_id, $course_id, $specialty) {
        $event_data = [
            'client_id' => $this->get_client_id($user_id),
            'events' => [
                [
                    'name' => 'course_completion',
                    'params' => [
                        'course_id' => $course_id,
                        'specialty' => $specialty,
                        'user_id' => $user_id,
                        'value' => 1
                    ]
                ]
            ]
        ];
        
        $this->send_event($event_data);
    }
    
    public function track_simulation_attempt($user_id, $simulation_id, $score) {
        $event_data = [
            'client_id' => $this->get_client_id($user_id),
            'events' => [
                [
                    'name' => 'simulation_attempt',
                    'params' => [
                        'simulation_id' => $simulation_id,
                        'score' => $score,
                        'user_id' => $user_id,
                        'success' => $score >= 70 ? 'true' : 'false'
                    ]
                ]
            ]
        ];
        
        $this->send_event($event_data);
    }
    
    public function track_certification_earned($user_id, $certification_type, $specialty) {
        $event_data = [
            'client_id' => $this->get_client_id($user_id),
            'events' => [
                [
                    'name' => 'certification_earned',
                    'params' => [
                        'certification_type' => $certification_type,
                        'specialty' => $specialty,
                        'user_id' => $user_id,
                        'value' => 100
                    ]
                ]
            ]
        ];
        
        $this->send_event($event_data);
    }
    
    private function send_event($event_data) {
        $url = 'https://www.google-analytics.com/mp/collect';
        $params = [
            'measurement_id' => $this->ga4_measurement_id,
            'api_secret' => $this->ga4_api_secret
        ];
        
        $full_url = $url . '?' . http_build_query($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $full_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($event_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
    
    private function get_client_id($user_id) {
        return 'moodle_user_' . $user_id;
    }
}
```

---

## 🔐 Seguridad y Autenticación

### JWT Authentication

**classes/jwt_auth.php**
```php
<?php
namespace local_medical_api;

class jwt_auth {
    
    private $secret_key;
    
    public function __construct($secret_key) {
        $this->secret_key = $secret_key;
    }
    
    public function generate_token($user_id, $permissions = []) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $user_id,
            'permissions' => $permissions,
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 horas
        ]);
        
        $base64_header = $this->base64url_encode($header);
        $base64_payload = $this->base64url_encode($payload);
        
        $signature = hash_hmac('sha256', $base64_header . "." . $base64_payload, $this->secret_key, true);
        $base64_signature = $this->base64url_encode($signature);
        
        return $base64_header . "." . $base64_payload . "." . $base64_signature;
    }
    
    public function validate_token($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        $header = $this->base64url_decode($parts[0]);
        $payload = $this->base64url_decode($parts[1]);
        $signature = $this->base64url_decode($parts[2]);
        
        $expected_signature = hash_hmac('sha256', $parts[0] . "." . $parts[1], $this->secret_key, true);
        
        if (!hash_equals($expected_signature, $signature)) {
            return false;
        }
        
        $payload_data = json_decode($payload, true);
        
        if ($payload_data['exp'] < time()) {
            return false;
        }
        
        return $payload_data;
    }
    
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
```

---

## 📱 Webhook para Notificaciones

### Slack Integration

**classes/slack_notifications.php**
```php
<?php
namespace local_medical_notifications;

class slack_notifications {
    
    private $webhook_url;
    
    public function __construct($webhook_url) {
        $this->webhook_url = $webhook_url;
    }
    
    public function send_course_completion_notification($user_name, $course_name, $specialty) {
        $message = [
            'text' => '🎓 Nueva Certificación Completada',
            'attachments' => [
                [
                    'color' => 'good',
                    'fields' => [
                        [
                            'title' => 'Estudiante',
                            'value' => $user_name,
                            'short' => true
                        ],
                        [
                            'title' => 'Curso',
                            'value' => $course_name,
                            'short' => true
                        ],
                        [
                            'title' => 'Especialidad',
                            'value' => $specialty,
                            'short' => true
                        ],
                        [
                            'title' => 'Fecha',
                            'value' => date('Y-m-d H:i:s'),
                            'short' => true
                        ]
                    ]
                ]
            ]
        ];
        
        $this->send_message($message);
    }
    
    public function send_certification_alert($user_name, $certification, $expiry_date) {
        $message = [
            'text' => '⚠️ Certificación Próxima a Vencer',
            'attachments' => [
                [
                    'color' => 'warning',
                    'fields' => [
                        [
                            'title' => 'Médico',
                            'value' => $user_name,
                            'short' => true
                        ],
                        [
                            'title' => 'Certificación',
                            'value' => $certification,
                            'short' => true
                        ],
                        [
                            'title' => 'Fecha de Vencimiento',
                            'value' => date('Y-m-d', $expiry_date),
                            'short' => true
                        ]
                    ]
                ]
            ]
        ];
        
        $this->send_message($message);
    }
    
    private function send_message($message) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->webhook_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
}
```

---

## 🚀 Configuración de Integraciones

### Archivo de Configuración

**config/integrations.php**
```php
<?php
return [
    'hospital_api' => [
        'fhir_endpoint' => getenv('FHIR_ENDPOINT'),
        'auth_token' => getenv('FHIR_AUTH_TOKEN'),
        'enabled' => true
    ],
    'zoom' => [
        'api_key' => getenv('ZOOM_API_KEY'),
        'api_secret' => getenv('ZOOM_API_SECRET'),
        'enabled' => true
    ],
    'pubmed' => [
        'api_key' => getenv('PUBMED_API_KEY'),
        'enabled' => true
    ],
    'google_analytics' => [
        'measurement_id' => getenv('GA4_MEASUREMENT_ID'),
        'api_secret' => getenv('GA4_API_SECRET'),
        'enabled' => true
    ],
    'slack' => [
        'webhook_url' => getenv('SLACK_WEBHOOK_URL'),
        'enabled' => true
    ],
    'certification_apis' => [
        'mexico' => [
            'endpoint' => getenv('MEXICO_COLLEGE_API'),
            'api_key' => getenv('MEXICO_COLLEGE_KEY'),
            'enabled' => false
        ],
        'colombia' => [
            'endpoint' => getenv('COLOMBIA_COLLEGE_API'),
            'api_key' => getenv('COLOMBIA_COLLEGE_KEY'),
            'enabled' => false
        ]
    ]
];
```

### Script de Configuración

**scripts/setup_integrations.sh**
```bash
#!/bin/bash

# Configurar variables de entorno
export FHIR_ENDPOINT="https://your-hospital-fhir.com/fhir"
export FHIR_AUTH_TOKEN="your-fhir-token"
export ZOOM_API_KEY="your-zoom-api-key"
export ZOOM_API_SECRET="your-zoom-api-secret"
export PUBMED_API_KEY="your-pubmed-api-key"
export GA4_MEASUREMENT_ID="G-XXXXXXXXXX"
export GA4_API_SECRET="your-ga4-api-secret"
export SLACK_WEBHOOK_URL="https://hooks.slack.com/services/XXX/XXX/XXX"

# Crear tablas para integraciones
php admin/cli/install_database.php --component=local_medical_api

# Configurar cron jobs
crontab -e
# Añadir:
# 0 */6 * * * /usr/bin/php /var/www/html/local/medical_api/cli/sync_hospital_data.php
# 0 8 * * * /usr/bin/php /var/www/html/local/certifications/cli/check_expiring_certs.php

echo "Integraciones configuradas correctamente"
```

---

## ✅ Checklist de Integraciones

- [ ] API REST funcionando
- [ ] Integración HL7 FHIR configurada
- [ ] Zoom API integrada
- [ ] PubMed API funcionando
- [ ] Google Analytics configurado
- [ ] Slack notifications activas
- [ ] Certificaciones API listas
- [ ] Tokens de autenticación configurados
- [ ] Webhooks funcionando
- [ ] Cron jobs configurados

---

**Próximo paso**: Deployment y monitoreo en producción