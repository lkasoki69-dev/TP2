<?php

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Models/Etudiant.php';
require_once __DIR__ . '/../src/Models/FamilleMembre.php';
require_once __DIR__ . '/../src/Models/Account.php';
require_once __DIR__ . '/../src/Controllers/Controller.php';
require_once __DIR__ . '/../src/Controllers/EtudiantController.php';
require_once __DIR__ . '/../src/Controllers/FamilleMembreController.php';
require_once __DIR__ . '/../src/Controllers/AccountController.php';

use App\Controllers\AccountController;
use App\Controllers\EtudiantController;
use App\Controllers\FamilleMembreController;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$path = $uri;

if ($scriptDir !== '/' && str_starts_with($path, $scriptDir)) {
    $path = substr($path, strlen($scriptDir));
}

$path = '/' . trim($path, '/');
if ($path === '/') {
    $path = '';
}

// ---------- Étudiants ----------
if (preg_match('#^/etudiants$#', $path)) {
    $controller = new EtudiantController();
    match ($method) {
        'GET' => $controller->index(),
        'POST' => $controller->store(),
        default => methodNotAllowed(),
    };
    exit;
}

if (preg_match('#^/etudiants/(\d+)$#', $path, $matches)) {
    $controller = new EtudiantController();
    $id = (int) $matches[1];
    match ($method) {
        'GET' => $controller->show($id),
        'PUT' => $controller->update($id),
        'DELETE' => $controller->destroy($id),
        default => methodNotAllowed(),
    };
    exit;
}

// ---------- Membres de famille ----------
if (preg_match('#^/famille-membres$#', $path)) {
    $controller = new FamilleMembreController();
    match ($method) {
        'GET' => $controller->index(),
        'POST' => $controller->store(),
        default => methodNotAllowed(),
    };
    exit;
}

if (preg_match('#^/famille-membres/(\d+)$#', $path, $matches)) {
    $controller = new FamilleMembreController();
    $id = (int) $matches[1];
    match ($method) {
        'GET' => $controller->show($id),
        // POST sert à la mise à jour en form-data (PHP ne lit pas les fichiers envoyés en PUT)
        'PUT', 'POST' => $controller->update($id),
        'DELETE' => $controller->destroy($id),
        default => methodNotAllowed(),
    };
    exit;
}

// ---------- Comptes ----------
if (preg_match('#^/accounts/register$#', $path)) {
    $method === 'POST' ? (new AccountController())->register() : methodNotAllowed();
    exit;
}

if (preg_match('#^/accounts/login$#', $path)) {
    $method === 'POST' ? (new AccountController())->login() : methodNotAllowed();
    exit;
}

http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Route introuvable']);

function methodNotAllowed(): void
{
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
