<?php

namespace App\Controllers;

use App\Models\Account;

class AccountController extends Controller
{
    private Account $model;

    public function __construct()
    {
        $this->model = new Account();
    }

    public function register(): void
    {
        $data = $this->getJsonBody();
        $errors = $this->validate($data);
        if ($errors) {
            $this->json(['success' => false, 'message' => 'Données invalides', 'errors' => $errors], 422);
            return;
        }

        $email = strtolower(trim($data['email']));
        if ($this->model->findByEmail($email)) {
            $this->json(['success' => false, 'message' => 'Cet email est déjà utilisé'], 409);
            return;
        }

        $id = $this->model->create($email, (string) $data['password']);
        $this->json(['success' => true, 'message' => 'Compte créé', 'data' => $this->model->findById($id)], 201);
    }

    public function login(): void
    {
        $data = $this->getJsonBody();
        $errors = $this->validate($data, false);
        if ($errors) {
            $this->json(['success' => false, 'message' => 'Données invalides', 'errors' => $errors], 422);
            return;
        }

        $account = $this->model->findByEmail(strtolower(trim($data['email'])));
        if (!$account || !password_verify((string) $data['password'], $account['password'])) {
            $this->json(['success' => false, 'message' => 'Email ou mot de passe incorrect'], 401);
            return;
        }

        unset($account['password']);
        $this->json(['success' => true, 'message' => 'Connexion réussie', 'data' => $account]);
    }

    private function validate(array $data, bool $checkLength = true): array
    {
        $errors = [];

        $email = trim((string) ($data['email'] ?? ''));
        if ($email === '') {
            $errors['email'] = 'Le champ email est obligatoire';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
            $errors['email'] = "L'email n'est pas valide";
        }

        $password = (string) ($data['password'] ?? '');
        if ($password === '') {
            $errors['password'] = 'Le champ password est obligatoire';
        } elseif ($checkLength && strlen($password) < 6) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères';
        }

        return $errors;
    }
}
