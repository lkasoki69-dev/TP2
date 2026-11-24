
import 'package:flutter/material.dart';
import 'services/api_service.dart';

class FamilleForm extends StatefulWidget {
  final Map<String, dynamic>? membre;

  const FamilleForm({super.key, this.membre});

  @override
  State<FamilleForm> createState() => _FamilleFormState();
}

class _FamilleFormState extends State<FamilleForm> {
  final _formKey = GlobalKey<FormState>();

  final nom = TextEditingController();
  final postnom = TextEditingController();
  final prenom = TextEditingController();
  final lieuNaissance = TextEditingController();
  final dateNaissance = TextEditingController();
  final parente = TextEditingController();

  bool chargement = false;

  @override
  void initState() {
    super.initState();

    final m = widget.membre;

    if (m != null) {
      nom.text = m['nom']?.toString() ?? '';
      postnom.text = m['postnom']?.toString() ?? '';
      prenom.text = m['prenom']?.toString() ?? '';
      lieuNaissance.text = m['lieu_naissance']?.toString() ?? '';
      dateNaissance.text = m['date_naissance']?.toString() ?? '';
      parente.text = m['parente']?.toString() ?? '';
    }
  }

  Future<void> enregistrer() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => chargement = true);

    final donnees = {
      'nom': nom.text.trim(),
      'postnom': postnom.text.trim(),
      'prenom': prenom.text.trim(),
      'lieu_naissance': lieuNaissance.text.trim(),
      'date_naissance': dateNaissance.text.trim(),
      'parente': parente.text.trim(),
    };

    try {
      if (widget.membre == null) {
        await ApiService.ajouterMembre(donnees);
      } else {
        final id = int.parse(widget.membre!['id'].toString());
        await ApiService.modifierMembre(id, donnees);
      }

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Enregistrement réussi !')),
      );

      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Erreur : $e')),
      );
    } finally {
      if (mounted) setState(() => chargement = false);
    }
  }

  @override
  void dispose() {
    nom.dispose();
    postnom.dispose();
    prenom.dispose();
    lieuNaissance.dispose();
    dateNaissance.dispose();
    parente.dispose();
    super.dispose();
  }

  Widget champ(
    String titre,
    TextEditingController controller,
  ) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 15),
      child: TextFormField(
        controller: controller,
        decoration: InputDecoration(
          labelText: titre,
          border: const OutlineInputBorder(),
        ),
        validator: (value) {
          if (value == null || value.trim().isEmpty) {
            return 'Ce champ est obligatoire';
          }
          return null;
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final modification = widget.membre != null;

    return Scaffold(
      appBar: AppBar(
        title: Text(
          modification ? 'Modifier un membre' : 'Ajouter un membre',
        ),
        backgroundColor: Colors.indigo,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              champ('Nom', nom),
              champ('Postnom', postnom),
              champ('Prénom', prenom),
              champ('Lieu de naissance', lieuNaissance),
              champ('Date de naissance (AAAA-MM-JJ)', dateNaissance),
              champ('Parenté', parente),
              const SizedBox(height: 10),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: chargement ? null : enregistrer,
                  child: chargement
                      ? const CircularProgressIndicator()
                      : Text(
                          modification ? 'Modifier' : 'Enregistrer',
                        ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
