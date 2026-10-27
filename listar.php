<?php
/**
 * listar.php
 * -----------------------------------------------------------------------------
 * Devuelve todas las historias del muro en formato JSON para que index.html
 * las muestre. Si aún no hay historias guardadas, entrega las iniciales.
 * -----------------------------------------------------------------------------
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store'); // siempre la versión más reciente

const STORIES_FILE = __DIR__ . '/data/stories.json';

// Historias iniciales (se muestran si el archivo todavía no existe).
$historiasIniciales = [
    [
        'id'     => 'story-1',
        'author' => 'Comunidad del Pantanal',
        'date'   => '14 de mayo, 2024',
        'image'  => 'https://images.unsplash.com/photo-1542273917363-3b1817f69a2d?auto=format&fit=crop&w=600&q=80',
        'text'   => 'En las noches más calurosas del estío vimos a una hembra de Priodontes salir pausadamente de su pozo. Al amanecer, medimos la temperatura interior de la galería: diez grados menos que en la superficie ardiente. Más de seis especies distintas aguardaban turno en los alrededores para entrar al fresco.',
    ],
    [
        'id'     => 'story-2',
        'author' => 'Bióloga Clara Mendoza',
        'date'   => '22 de junio, 2024',
        'image'  => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=600&q=80',
        'text'   => 'Las garras del armadillo gigante pueden medir hasta 20 centímetros, superando proporcionalmente a las del oso polar. Cuando excavan, son verdaderas excavadoras ecológicas. Sin ellos, cientos de invertebrados y pequeños vertebrados no tendrían refugio térmico.',
    ],
    [
        'id'     => 'story-3',
        'author' => 'Relator Anónimo del Chaco',
        'date'   => '03 de julio, 2024',
        'image'  => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=600&q=80',
        'text'   => 'Cuentan los abuelos que el armadillo no cava para esconderse, sino para escuchar los secretos del corazón de la tierra. Cuando termina su labor, deja la puerta abierta para que el zorro, la serpiente y el ave encuentren paz en la tormenta.',
    ],
];

if (!file_exists(STORIES_FILE)) {
    echo json_encode($historiasIniciales, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$raw = file_get_contents(STORIES_FILE);
$data = json_decode($raw, true);

if (!is_array($data) || count($data) === 0) {
    echo json_encode($historiasIniciales, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
