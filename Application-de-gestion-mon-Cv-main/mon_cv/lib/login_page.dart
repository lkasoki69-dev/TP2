
import 'package:flutter/material.dart';
import 'services/api_service.dart';
import 'main.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final emailController = TextEditingController();
  final passwordController = TextEditingController();

  bool inscription = false;
  bool chargement = false;
  bool masquerMotDePasse = true;

  Future<void> envoyer() async {
    final email = emailController.text.trim();
    final password = passwordController.text;

    if (email.isEmpty || password.isEmpty) {
      message('Veuillez remplir tous les champs.');
      return;
    }

    if (password.length < 6) {
      message('Le mot de passe doit contenir au moins 6 caractères.');
      return;
    }

    setState(() => chargement = true);

    try {
      if (inscription) {
        await ApiService.register(email, password);
        if (!mounted) return;
        message('Compte créé ! Vous pouvez maintenant vous connecter.');
        setState(() => inscription = false);
      } else {
        await ApiService.login(email, password);

        if (!mounted) return;

        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (_) => const AccueilPage(),
          ),
        );
      }
    } catch (e) {
      if (!mounted) return;
      message('Erreur : $e');
    } finally {
      if (mounted) {
        setState(() => chargement = false);
      }
    }
  }

  void message(String texte) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(texte)),
    );
  }

  @override
  void dispose() {
    emailController.dispose();
    passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(25),
          child: Column(
            children: [
              const Icon(
                Icons.account_circle,
                size: 90,
                color: Colors.indigo,
              ),
              const SizedBox(height: 15),
              Text(
                inscription ? 'Créer un compte' : 'Connexion',
                style: const TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                  color: Colors.indigo,
                ),
              ),
              const SizedBox(height: 30),
              TextField(
                controller: emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: const InputDecoration(
                  labelText: 'Adresse e-mail',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.email),
                ),
              ),
              const SizedBox(height: 15),
              TextField(
                controller: passwordController,
                obscureText: masquerMotDePasse,
                decoration: InputDecoration(
                  labelText: 'Mot de passe',
                  border: const OutlineInputBorder(),
                  prefixIcon: const Icon(Icons.lock),
                  suffixIcon: IconButton(
                    icon: Icon(
                      masquerMotDePasse
                          ? Icons.visibility
                          : Icons.visibility_off,
                    ),
                    onPressed: () {
                      setState(() {
                        masquerMotDePasse = !masquerMotDePasse;
                      });
                    },
                  ),
                ),
              ),
              const SizedBox(height: 25),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: chargement ? null : envoyer,
                  child: chargement
                      ? const CircularProgressIndicator()
                      : Text(
                          inscription ? 'Créer mon compte' : 'Se connecter',
                        ),
                ),
              ),
              const SizedBox(height: 15),
              TextButton(
                onPressed: chargement
                    ? null
                    : () {
                        setState(() {
                          inscription = !inscription;
                        });
                      },
                child: Text(
                  inscription
                      ? 'J’ai déjà un compte : me connecter'
                      : 'Je n’ai pas de compte : m’inscrire',
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
