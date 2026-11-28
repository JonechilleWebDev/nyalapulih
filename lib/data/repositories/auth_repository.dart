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

  Future<UserModel> updateProfile({
    required String name,
    required String email,
    String? organization,
  }) {
    return provider.updateProfile(
      name: name,
      email: email,
      organization: organization,
    );
  }
  
  Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) {
    return provider.changePassword(
      currentPassword: currentPassword,
      newPassword: newPassword,
      confirmPassword: confirmPassword,
    );
  }

  Future<void> logout() {
    return provider.logout();
  }

  bool isLoggedIn() {
    return provider.isLoggedIn();
  }
}
