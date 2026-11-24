
import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  static const String baseUrl =
      'http://localhost/api-test-main/api-test-main/public';

  // Convertir les réponses JSON
  static dynamic _decodeResponse(http.Response response) {
    final resultat = jsonDecode(response.body);

    if (response.statusCode >= 200 &&
        response.statusCode < 300 &&
        resultat['success'] == true) {
      return resultat;
    }

    throw Exception(
      resultat['message'] ?? 'Une erreur est survenue.',
    );
  }

  // INSCRIPTION
  static Future<dynamic> register(
    String email,
    String password,
  ) async {
    final response = await http.post(
      Uri.parse('$baseUrl/accounts/register'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );

    return _decodeResponse(response);
  }

  // CONNEXION
  static Future<dynamic> login(
    String email,
    String password,
  ) async {
    final response = await http.post(
      Uri.parse('$baseUrl/accounts/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );

    return _decodeResponse(response);
  }

  // AFFICHER TOUS LES MEMBRES
  static Future<List<dynamic>> getFamille() async {
    final response = await http.get(
      Uri.parse('$baseUrl/famille-membres'),
    );

    final resultat = _decodeResponse(response);
    return resultat['data'] ?? [];
  }

  // AJOUTER UN MEMBRE
  static Future<dynamic> ajouterMembre(
    Map<String, dynamic> donnees,
  ) async {
    final response = await http.post(
      Uri.parse('$baseUrl/famille-membres'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode(donnees),
    );

    return _decodeResponse(response);
  }

  // MODIFIER UN MEMBRE
  static Future<dynamic> modifierMembre(
    int id,
    Map<String, dynamic> donnees,
  ) async {
    final response = await http.put(
      Uri.parse('$baseUrl/famille-membres/$id'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode(donnees),
    );

    return _decodeResponse(response);
  }

  // SUPPRIMER UN MEMBRE
  static Future<dynamic> supprimerMembre(int id) async {
    final response = await http.delete(
      Uri.parse('$baseUrl/famille-membres/$id'),
    );

    return _decodeResponse(response);
  }
}
