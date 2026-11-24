<?php

namespace App\Controllers;

use App\Models\FamilleMembre;
use DateTime;

class FamilleMembreController extends Controller
{
    private const UPLOAD_DIR = 'uploads/famille';
    private const MAX_IMAGE_SIZE = 2 * 1024 * 1024;
    private const IMAGE_EXTENSIONS = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_GIF => 'gif',
        IMAGETYPE_WEBP => 'webp',
    ];

    private FamilleMembre $model;

    public function __construct()
    {
        $this->model = new FamilleMembre();
    }

    public function index(): void
    {
        $membres = array_map([$this, 'present'], $this->model->findAll());
        $this->json(['success' => true, 'data' => $membres]);
    }

    public function show(int $id): void
    {
        $membre = $this->model->findById($id);
        if (!$membre) {
            $this->json(['success' => false, 'message' => 'Membre introuvable'], 404);
            return;
        }
        $this->json(['success' => true, 'data' => $this->present($membre)]);
    }

    /** POST /famille-membres — form-data (avec image) ou JSON (sans image). */
    public function store(): void
    {
        $data = $this->getInput();
        $errors = $this->validate($data);
        $upload = $this->checkImage($errors);
        if ($errors) {
            $this->json(['success' => false, 'message' => 'Données invalides', 'errors' => $errors], 422);
            return;
        }

        $data['image'] = $upload ? $this->saveImage($upload) : null;
        $id = $this->model->create($data);
        $this->json(['success' => true, 'message' => 'Membre créé', 'data' => $this->present($this->model->findById($id))], 201);
    }

    /** PUT /famille-membres/{id} (JSON) ou POST /famille-membres/{id} (form-data, pour changer l'image). */
    public function update(int $id): void
    {
        $membre = $this->model->findById($id);
        if (!$membre) {
            $this->json(['success' => false, 'message' => 'Membre introuvable'], 404);
            return;
        }

        $data = $this->getInput();
        $errors = $this->validate($data);
        $upload = $this->checkImage($errors);
        if ($errors) {
            $this->json(['success' => false, 'message' => 'Données invalides', 'errors' => $errors], 422);
            return;
        }

        $data['image'] = $membre['image'];
        if ($upload) {
            $data['image'] = $this->saveImage($upload);
            $this->deleteImage($membre['image']);
        }

        $this->model->update($id, $data);
        $this->json(['success' => true, 'message' => 'Membre mis à jour', 'data' => $this->present($this->model->findById($id))]);
    }

    public function destroy(int $id): void
    {
        $membre = $this->model->findById($id);
        if (!$membre) {
            $this->json(['success' => false, 'message' => 'Membre introuvable'], 404);
            return;
        }

        $this->model->delete($id);
        $this->deleteImage($membre['image']);
        $this->json(['success' => true, 'message' => 'Membre supprimé']);
    }

    private function getInput(): array
    {
        return $_POST ?: $this->getJsonBody();
    }

    private function validate(array $data): array
    {
        $errors = [];
        $fields = [
            'nom' => 50,
            'postnom' => 50,
            'prenom' => 50,
            'lieu_naissance' => 100,
            'date_naissance' => 10,
            'parente' => 50,
        ];

        foreach ($fields as $field => $maxLength) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                $errors[$field] = "Le champ {$field} est obligatoire";
            } elseif (strlen((string) $data[$field]) > $maxLength) {
                $errors[$field] = "Le champ {$field} ne doit pas dépasser {$maxLength} caractères";
            }
        }

        if (!isset($errors['date_naissance'])) {
            $date = DateTime::createFromFormat('!Y-m-d', (string) $data['date_naissance']);
            if (!$date || $date->format('Y-m-d') !== $data['date_naissance']) {
                $errors['date_naissance'] = 'La date doit être au format AAAA-MM-JJ';
            } elseif ($date > new DateTime('today')) {
                $errors['date_naissance'] = 'La date de naissance ne peut pas être dans le futur';
            }
        }

        return $errors;
    }

    /** Retourne le fichier envoyé (ou null s'il n'y en a pas) et ajoute une erreur s'il est invalide. */
    private function checkImage(array &$errors): ?array
    {
        $file = $_FILES['image'] ?? null;
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['image'] = "Échec de l'envoi de l'image";
            return null;
        }
        if ($file['size'] > self::MAX_IMAGE_SIZE) {
            $errors['image'] = "L'image ne doit pas dépasser 2 Mo";
            return null;
        }

        $info = @getimagesize($file['tmp_name']);
        if (!$info || !isset(self::IMAGE_EXTENSIONS[$info[2]])) {
            $errors['image'] = "L'image doit être au format jpg, png, gif ou webp";
            return null;
        }

        $file['extension'] = self::IMAGE_EXTENSIONS[$info[2]];
        return $file;
    }

    private function saveImage(array $file): string
    {
        $dir = __DIR__ . '/../../public/' . self::UPLOAD_DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $name = bin2hex(random_bytes(16)) . '.' . $file['extension'];
        move_uploaded_file($file['tmp_name'], $dir . '/' . $name);

        return self::UPLOAD_DIR . '/' . $name;
    }

    private function deleteImage(?string $path): void
    {
        if (!$path) {
            return;
        }
        $file = __DIR__ . '/../../public/' . $path;
        if (is_file($file)) {
            unlink($file);
        }
    }

    /** Ajoute l'URL complète de l'image à la réponse. */
    private function present(array $membre): array
    {
        $membre['image_url'] = null;
        if ($membre['image']) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
            $membre['image_url'] = "{$scheme}://{$_SERVER['HTTP_HOST']}{$base}/{$membre['image']}";
        }
        return $membre;
    }
}
