
import 'package:flutter/material.dart';
import 'services/api_service.dart';
import 'famille_form.dart';
import 'login_page.dart';

void main() {
  runApp(const MonCvApp());
}

class MonCvApp extends StatelessWidget {
  const MonCvApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Mon CV',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: Colors.indigo,
        ),
        useMaterial3: true,
      ),
      home: const LoginPage(),
    );
  }
}

class AccueilPage extends StatefulWidget {
  const AccueilPage({super.key});

  @override
  State<AccueilPage> createState() => _AccueilPageState();
}

class _AccueilPageState extends State<AccueilPage> {
  int pageActuelle = 0;

  final List<Widget> pages = const [
    CvPage(),
    FamillePage(),
    ContactPage(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'MON ESPACE PERSONNEL',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        backgroundColor: Colors.indigo,
        foregroundColor: Colors.white,
      ),
      body: pages[pageActuelle],
      bottomNavigationBar: NavigationBar(
        selectedIndex: pageActuelle,
        onDestinationSelected: (index) {
          setState(() {
            pageActuelle = index;
          });
        },
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.person),
            label: 'Mon CV',
          ),
          NavigationDestination(
            icon: Icon(Icons.family_restroom),
            label: 'Ma famille',
          ),
          NavigationDestination(
            icon: Icon(Icons.contact_mail),
            label: 'Contact',
          ),
        ],
      ),
    );
  }
}

class CvPage extends StatelessWidget {
  const CvPage({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: CircleAvatar(
  radius: 65,
  backgroundImage: const AssetImage(
    'assets/images/ma_photo.jpeg',
  ),
),
          ),
          const SizedBox(height: 15),
          const Center(
            child: Text(
              'KASOKI MUSAFIRI LYDIA',
              style: TextStyle(
                fontSize: 25,
                fontWeight: FontWeight.bold,
                color: Colors.indigo,
              ),
            ),
          ),
          const Center(
            child: Text('Étudiant en informatique'),
          ),
          const SizedBox(height: 25),
          const Text(
            'À PROPOS DE MOI',
            style: TextStyle(
              fontSize: 19,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Je suis un étudiant passionné par les technologies, '
            'le développement mobile et la création de solutions '
            'informatiques innovantes.',
          ),
          const SizedBox(height: 20),
          const Text(
            'FORMATION',
            style: TextStyle(
              fontSize: 19,
              fontWeight: FontWeight.bold,
            ),
          ),
          const ListTile(
            leading: Icon(Icons.school, color: Colors.indigo),
            title: Text('Licence en informatique'),
            subtitle: Text('Formation universitaire en cours'),
          ),
          const SizedBox(height: 10),
          const Text(
            'COMPÉTENCES',
            style: TextStyle(
              fontSize: 19,
              fontWeight: FontWeight.bold,
            ),
          ),
          const Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              Chip(label: Text('Flutter')),
              Chip(label: Text('Dart')),
              Chip(label: Text('PHP')),
              Chip(label: Text('MySQL')),
              Chip(label: Text('HTML / CSS')),
            ],
          ),
          const SizedBox(height: 20),
          const Text(
            'EXPÉRIENCES',
            style: TextStyle(
              fontSize: 19,
              fontWeight: FontWeight.bold,
            ),
          ),
          const ListTile(
            leading: Icon(Icons.phone_android, color: Colors.indigo),
            title: Text('Développement d’applications mobiles'),
            subtitle: Text('Projet académique Flutter'),
          ),
        ],
      ),
    );
  }
}



class FamillePage extends StatefulWidget {
  const FamillePage({super.key});

  @override
  State<FamillePage> createState() => _FamillePageState();
}

class _FamillePageState extends State<FamillePage> {
  late Future<List<dynamic>> membres;

  @override
  void initState() {
    super.initState();
    actualiser();
  }

  void actualiser() {
    membres = ApiService.getFamille();
  }

  Future<void> ouvrirFormulaire({Map<String, dynamic>? membre}) async {
    final resultat = await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => FamilleForm(membre: membre),
      ),
    );

    if (resultat == true && mounted) {
      setState(actualiser);
    }
  }

  Future<void> supprimerMembre(dynamic membre) async {
    final confirmation = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Confirmation'),
        content: const Text('Voulez-vous supprimer ce membre ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Supprimer'),
          ),
        ],
      ),
    );

    if (confirmation != true) return;

    try {
      await ApiService.supprimerMembre(
        int.parse(membre['id'].toString()),
      );

      if (!mounted) return;

      setState(actualiser);

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Membre supprimé avec succès')),
      );
    } catch (e) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Erreur : $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Ma famille'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => setState(actualiser),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => ouvrirFormulaire(),
        child: const Icon(Icons.add),
      ),
      body: FutureBuilder<List<dynamic>>(
        future: membres,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }

          if (snapshot.hasError) {
            return Center(
              child: Text('Erreur : ${snapshot.error}'),
            );
          }

          final liste = snapshot.data ?? [];

          if (liste.isEmpty) {
            return const Center(
              child: Text('Aucun membre enregistré. Appuyez sur + pour ajouter.'),
            );
          }

          return ListView.builder(
            itemCount: liste.length,
            itemBuilder: (context, index) {
              final membre = Map<String, dynamic>.from(liste[index]);

              return Card(
                margin: const EdgeInsets.all(10),
                child: ListTile(
                  leading: const CircleAvatar(
                    child: Icon(Icons.person),
                  ),
                  title: Text(
                    '${membre['nom'] ?? ''} ${membre['postnom'] ?? ''} ${membre['prenom'] ?? ''}',
                  ),
                  subtitle: Text(
                    'Parenté : ${membre['parente'] ?? ''}\n'
                    'Naissance : ${membre['date_naissance'] ?? ''}',
                  ),
                  isThreeLine: true,
                  trailing: PopupMenuButton<String>(
                    onSelected: (choix) {
                      if (choix == 'modifier') {
                        ouvrirFormulaire(membre: membre);
                      } else if (choix == 'supprimer') {
                        supprimerMembre(membre);
                      }
                    },
                    itemBuilder: (context) => const [
                      PopupMenuItem(
                        value: 'modifier',
                        child: Text('Modifier'),
                      ),
                      PopupMenuItem(
                        value: 'supprimer',
                        child: Text('Supprimer'),
                      ),
                    ],
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}



class ContactPage extends StatelessWidget {
  const ContactPage({super.key});

  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.contact_mail, size: 75, color: Colors.indigo),
          SizedBox(height: 15),
          Text(
            'Mes coordonnées',
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
          ),
          ListTile(
            leading: Icon(Icons.email),
            title: Text('david.baraka@example.com'),
          ),
          ListTile(
            leading: Icon(Icons.phone),
            title: Text('+243 990 000 000'),
          ),
          ListTile(
            leading: Icon(Icons.location_on),
            title: Text('Butembo, RDC'),
          ),
        ],
      ),
    );
  }
}
