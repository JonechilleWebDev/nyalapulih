import '../models/user_model.dart';
import '../providers/auth_provider.dart';

class AuthRepository {
  final AuthProvider provider;

  AuthRepository(this.provider);

  Future<void> register({
    required String name,
    required String email,
    required String password,
  }) {
    return provider.register(
      name: name,
      email: email,
      password: password,
    );
  }

  Future<UserModel> login({
    required String email,
    required String password,
  }) {
    return provider.login(
      email: email,
      password: password,
    );
  }

  Future<UserModel> getProfile() {
    return provider.getProfile();
  }

  Future<void> logout() {
    return provider.logout();
  }

  bool isLoggedIn() {
    return provider.isLoggedIn();
  }
}
