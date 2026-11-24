import 'dart:convert';
import 'package:get_storage/get_storage.dart';

import '../models/user_model.dart';

class AuthProvider {
  final GetStorage storage = GetStorage();

  static const String userKey = 'users';
  static const String sessionKey = 'current_user';

  List<UserModel> getUsers() {
    final data = storage.read(userKey);

    if (data == null) return [];

    final List decoded = jsonDecode(jsonEncode(data));

    return decoded
        .map((item) => UserModel.fromJson(
              Map<String, dynamic>.from(item),
            ))
        .toList();
  }

  Future<bool> register(UserModel user) async {
    final users = getUsers();

    final exists = users.any(
      (item) => item.email.toLowerCase() ==
          user.email.toLowerCase(),
    );

    if (exists) return false;

    users.add(user);

    await storage.write(
      userKey,
      users.map((item) => item.toJson()).toList(),
    );

    return true;
  }

  UserModel? login(String email, String password) {
    final users = getUsers();

    try {
      final user = users.firstWhere(
        (item) =>
            item.email.toLowerCase() == email.toLowerCase() &&
            item.password == password,
      );

      return user;
    } catch (_) {
      return null;
    }
  }

  Future<void> saveSession(UserModel user) async {
    await storage.write(sessionKey, user.toJson());
  }

  UserModel? getSession() {
    final data = storage.read(sessionKey);

    if (data == null) return null;

    return UserModel.fromJson(
      Map<String, dynamic>.from(data),
    );
  }

  Future<void> logout() async {
    await storage.remove(sessionKey);
  }
}
