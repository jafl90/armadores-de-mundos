<?php
/**
 * guardar.php
 * -----------------------------------------------------------------------------
 * Recibe una nueva historia (autor, narración e imagen opcional) desde el
 * formulario de index.html y la guarda de forma permanente en el host.
 *
 *   - La imagen se guarda en /imagenes
 *   - La historia se añade al inicio de data/stories.json
 *
 * Muro ABIERTO: la historia aparece de inmediato (sin moderación).
 *
 * No requiere base de datos ni configuración especial. Funciona en el
 * hosting compartido de Namecheap (cPanel) con PHP 7.x u 8.x.
 * -----------------------------------------------------------------------------
 */

// ---------------------------------------------------------------------------
// Configuración
// ---------------------------------------------------------------------------
const MAX_IMAGE_BYTES   = 4 * 1024 * 1024;   // 4 MB máximo por imagen
const MAX_TEXT_CHARS     = 20000;             // límite defensivo del relato
const MAX_AUTHOR_CHARS    = 120;
const DATA_DIR           = __DIR__ . '/data';
const STORIES_FILE       = DATA_DIR . '/stories.json';
const IMAGES_DIR         = __DIR__ . '/imagenes';

// Tipos de imagen permitidos: extensión => mime real esperado
$ALLOWED_IMAGE_TYPES = [
    'jpg'  => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'png'  => ['image/png'],
    'gif'  => ['image/gif'],
    'webp' => ['image/webp'],
];

// ---------------------------------------------------------------------------
// Utilidades
// ---------------------------------------------------------------------------
header('Content-Type: application/json; charset=utf-8');

function responder(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Fecha legible en español, sin depender de locales del servidor. */
function fechaEspanol(): string {
    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
    ];
    $d = (int) date('j');
    $m = (int) date('n');
    $y = date('Y');
    return "$d de {$meses[$m]}, $y";
}

/** Bloqueo con reintentos para evitar que dos envíos simultáneos se pisen. */
function leerHistorias(): array {
    if (!file_exists(STORIES_FILE)) {
        return [];
    }
    $raw = file_get_contents(STORIES_FILE);
    if ($raw === false || trim($raw) === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// ---------------------------------------------------------------------------
// Solo aceptamos POST
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['ok' => false, 'error' => 'Método no permitido.']);
}

// ---------------------------------------------------------------------------
// Validar campos de texto
// ---------------------------------------------------------------------------
$author = isset($_POST['author']) ? trim($_POST['author']) : '';
$text   = isset($_POST['text'])   ? trim($_POST['text'])   : '';

if ($author === '' || $text === '') {
    responder(400, ['ok' => false, 'error' => 'Faltan el nombre o la narración.']);
}
if (mb_strlen($author) > MAX_AUTHOR_CHARS) {
    $author = mb_substr($author, 0, MAX_AUTHOR_CHARS);
}
if (mb_strlen($text) > MAX_TEXT_CHARS) {
    responder(400, ['ok' => false, 'error' => 'La narración es demasiado larga.']);
}

// Neutralizamos etiquetas HTML para evitar inyección en el muro.
$author = strip_tags($author);
$text   = strip_tags($text);

// ---------------------------------------------------------------------------
// Preparar carpetas
// ---------------------------------------------------------------------------
if (!is_dir(DATA_DIR))   { @mkdir(DATA_DIR, 0755, true); }
if (!is_dir(IMAGES_DIR)) { @mkdir(IMAGES_DIR, 0755, true); }

if (!is_writable(DATA_DIR)) {
    responder(500, ['ok' => false, 'error' => 'El servidor no puede escribir en data/. Revisa permisos (755).']);
}

// ---------------------------------------------------------------------------
// Procesar imagen (opcional)
// ---------------------------------------------------------------------------
$imagePath = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        responder(400, ['ok' => false, 'error' => 'Hubo un problema al subir la imagen.']);
    }
    if ($file['size'] > MAX_IMAGE_BYTES) {
        responder(400, ['ok' => false, 'error' => 'La imagen supera los 4 MB.']);
    }

    // Verificar el tipo REAL del archivo (no confiar en la extensión enviada).
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $ext = null;
    foreach ($ALLOWED_IMAGE_TYPES as $extension => $mimes) {
        if (in_array($realMime, $mimes, true)) {
            $ext = $extension;
            break;
        }
    }
    if ($ext === null) {
        responder(400, ['ok' => false, 'error' => 'Formato de imagen no permitido (usa JPG, PNG, GIF o WEBP).']);
    }

    // Nombre único e imposible de adivinar; nunca usamos el nombre original.
    $fileName = 'historia-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = IMAGES_DIR . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        responder(500, ['ok' => false, 'error' => 'No se pudo guardar la imagen en el servidor.']);
    }
    @chmod($dest, 0644);
    $imagePath = 'imagenes/' . $fileName;
}

// ---------------------------------------------------------------------------
// Guardar la historia en stories.json (con bloqueo de archivo)
// ---------------------------------------------------------------------------
$nuevaHistoria = [
    'id'     => 'story-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)),
    'author' => $author,
    'date'   => fechaEspanol(),
    'image'  => $imagePath,
    'text'   => $text,
];

$fp = fopen(STORIES_FILE, 'c+');
if ($fp === false) {
    responder(500, ['ok' => false, 'error' => 'No se pudo abrir la base de datos de historias.']);
}

if (!flock($fp, LOCK_EX)) {
    fclose($fp);
    responder(500, ['ok' => false, 'error' => 'El servidor está ocupado. Intenta de nuevo.']);
}

$contenido = stream_get_contents($fp);
$historias = ($contenido !== false && trim($contenido) !== '') ? json_decode($contenido, true) : [];
if (!is_array($historias)) {
    $historias = [];
}

array_unshift($historias, $nuevaHistoria);

// Reescribir el archivo desde cero.
rewind($fp);
ftruncate($fp, 0);
fwrite($fp, json_encode($historias, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

responder(200, ['ok' => true, 'story' => $nuevaHistoria]);
