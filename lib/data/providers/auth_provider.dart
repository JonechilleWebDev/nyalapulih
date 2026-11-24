import 'dart:convert';

import 'package:get_storage/get_storage.dart';
import 'package:http/http.dart' as http;

import '../../app/core/values/api_config.dart';
import '../models/user_model.dart';

class AuthProvider {
  final http.Client client;
  final GetStorage storage = GetStorage();

  AuthProvider({http.Client? client})
      : client = client ?? http.Client();

  static const String tokenKey = 'auth_token';

  // Membaca respons JSON dari server
  Map<String, dynamic> _handleResponse(
    http.Response response,
  ) {
    final Map<String, dynamic> data =
        jsonDecode(response.body);

    if (response.statusCode >= 200 &&
        response.statusCode < 300 &&
        data['success'] == true) {
      return data;
    }

    throw Exception(
      data['message'] ?? 'Terjadi kesalahan pada server',
    );
  }

  // REGISTER
  Future<void> register({
    required String name,
    required String email,
    required String password,
  }) async {
    final response = await client.post(
      Uri.parse(ApiConfig.register),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'name': name,
        'email': email,
        'password': password,
      }),
    );

    _handleResponse(response);
  }

  // LOGIN
  Future<UserModel> login({
    required String email,
    required String password,
  }) async {
    final response = await client.post(
      Uri.parse(ApiConfig.login),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );

    final data = _handleResponse(response);

    final String token = data['token'];

    final UserModel user = UserModel.fromJson(
      Map<String, dynamic>.from(data['user']),
    );

    // Simpan token autentikasi
    await storage.write(tokenKey, token);

    return user;
  }

  // PROFILE
  Future<UserModel> getProfile() async {
    final token = storage.read(tokenKey);

    if (token == null) {
      throw Exception('Token autentikasi tidak ditemukan');
    }

    final response = await client.get(
      Uri.parse(ApiConfig.profile),
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );

    final data = _handleResponse(response);

    return UserModel.fromJson(
      Map<String, dynamic>.from(data['user']),
    );
  }

  // LOGOUT
  Future<void> logout() async {
    final token = storage.read(tokenKey);

    if (token != null) {
      final response = await client.post(
        Uri.parse(ApiConfig.logout),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      _handleResponse(response);
    }

    await storage.remove(tokenKey);
  }

  // Cek apakah token tersedia
  bool isLoggedIn() {
    return storage.read(tokenKey) != null;
  }
}
