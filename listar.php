<?php
/**
 * listar.php
 * -----------------------------------------------------------------------------
 * Devuelve todas las historias del muro en formato JSON para que index.html
 * las muestre. Si aún no hay historias guardadas, devuelve una lista vacía
 * (el muro mostrará el mensaje de "Aún no hay historias publicadas").
 * -----------------------------------------------------------------------------
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store'); // siempre la versión más reciente

const STORIES_FILE = __DIR__ . '/data/stories.json';

// Si el archivo todavía no existe, devolvemos una lista vacía.
if (!file_exists(STORIES_FILE)) {
    echo json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$raw = file_get_contents(STORIES_FILE);
$data = json_decode($raw, true);

// Si el contenido no es válido, devolvemos una lista vacía.
if (!is_array($data)) {
    echo json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
