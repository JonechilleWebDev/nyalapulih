import '../models/user_model.dart';
import '../providers/auth_provider.dart';

class AuthRepository {
  final AuthProvider provider;

  AuthRepository(this.provider);

  Future<bool> register(UserModel user) {
    return provider.register(user);
  }

  Future<UserModel?> login(
    String email,
    String password,
  ) async {
    final user = provider.login(email, password);

    if (user != null) {
      await provider.saveSession(user);
    }

    return user;
  }

  UserModel? getSession() {
    return provider.getSession();
  }

  Future<void> logout() {
    return provider.logout();
  }
}
