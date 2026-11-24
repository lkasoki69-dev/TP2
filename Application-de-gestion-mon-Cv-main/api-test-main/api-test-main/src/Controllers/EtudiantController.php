<?php

namespace App\Controllers;

use App\Models\Etudiant;

class EtudiantController extends Controller
{
    private Etudiant $model;

    public function __construct()
    {
        $this->model = new Etudiant();
    }

    public function index(): void
    {
        $etudiants = $this->model->findAll();
        $this->json(['success' => true, 'data' => $etudiants]);
    }

    public function show(int $id): void
    {
        $etudiant = $this->model->findById($id);
        if (!$etudiant) {
            $this->json(['success' => false, 'message' => 'Étudiant introuvable'], 404);
            return;
        }
        $this->json(['success' => true, 'data' => $etudiant]);
    }

    public function store(): void
    {
        $data = $this->getJsonBody();
        $errors = $this->validate($data);
        if ($errors) {
            $this->json(['success' => false, 'message' => 'Données invalides', 'errors' => $errors], 422);
            return;
        }

        $id = $this->model->create($data);
        $etudiant = $this->model->findById($id);
        $this->json(['success' => true, 'message' => 'Étudiant créé', 'data' => $etudiant], 201);
    }

    public function update(int $id): void
    {
        if (!$this->model->findById($id)) {
            $this->json(['success' => false, 'message' => 'Étudiant introuvable'], 404);
            return;
        }

        $data = $this->getJsonBody();
        $errors = $this->validate($data);
        if ($errors) {
            $this->json(['success' => false, 'message' => 'Données invalides', 'errors' => $errors], 422);
            return;
        }

        $this->model->update($id, $data);
        $etudiant = $this->model->findById($id);
        $this->json(['success' => true, 'message' => 'Étudiant mis à jour', 'data' => $etudiant]);
    }

    public function destroy(int $id): void
    {
        if (!$this->model->findById($id)) {
            $this->json(['success' => false, 'message' => 'Étudiant introuvable'], 404);
            return;
        }

        $this->model->delete($id);
        $this->json(['success' => true, 'message' => 'Étudiant supprimé']);
    }

    private function validate(array $data): array
    {
        $errors = [];
        $fields = [
            'nom' => 50,
            'postnom' => 50,
            'prenom' => 50,
            'adresse' => 100,
            'telephone' => 15,
        ];

        foreach ($fields as $field => $maxLength) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                $errors[$field] = "Le champ {$field} est obligatoire";
            } elseif (strlen((string) $data[$field]) > $maxLength) {
                $errors[$field] = "Le champ {$field} ne doit pas dépasser {$maxLength} caractères";
            }
        }

        return $errors;
    }
}
