import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../data/models/user_model.dart';
import '../../../data/repositories/auth_repository.dart';
import '../../../app/routes/app_routes.dart';

class AuthController extends GetxController {
  final AuthRepository repository;

  AuthController(this.repository);

  final emailController = TextEditingController();
  final passwordController = TextEditingController();
  final nameController = TextEditingController();
  final confirmPasswordController = TextEditingController();

  final loginFormKey = GlobalKey<FormState>();
  final registerFormKey = GlobalKey<FormState>();

  final isLoginPasswordHidden = true.obs;
  final isRegisterPasswordHidden = true.obs;
  final isConfirmPasswordHidden = true.obs;

  final isLoading = false.obs;

  void toggleLoginPassword() =>
      isLoginPasswordHidden.toggle();

  void toggleRegisterPassword() =>
      isRegisterPasswordHidden.toggle();

  void toggleConfirmPassword() =>
      isConfirmPasswordHidden.toggle();

  String? validateName(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Nama tidak boleh kosong';
    }
    return null;
  }

  String? validateEmail(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Email tidak boleh kosong';
    }

    if (!GetUtils.isEmail(value.trim())) {
      return 'Format email tidak valid';
    }

    return null;
  }

  String? validatePassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'Password tidak boleh kosong';
    }

    if (value.length < 8) {
      return 'Password minimal 8 karakter';
    }

    return null;
  }

  String? validateConfirmPassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'Konfirmasi password tidak boleh kosong';
    }

    if (value != passwordController.text) {
      return 'Password tidak cocok';
    }

    return null;
  }

  Future<void> register() async {
    if (!registerFormKey.currentState!.validate()) return;

    isLoading.value = true;

    try {
      final user = UserModel(
        id: DateTime.now().microsecondsSinceEpoch.toString(),
        name: nameController.text.trim(),
        email: emailController.text.trim(),
        password: passwordController.text,
      );

      final success = await repository.register(user);

      if (!success) {
        Get.snackbar('Gagal', 'Email sudah terdaftar');
        return;
      }

      Get.snackbar(
        'Berhasil',
        'Akun berhasil dibuat. Silakan login.',
      );

      nameController.clear();
      emailController.clear();
      passwordController.clear();
      confirmPasswordController.clear();

      Get.offNamed(AppRoutes.login);
    } catch (e) {
      Get.snackbar('Error', 'Terjadi kesalahan: $e');
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> login() async {
    if (!loginFormKey.currentState!.validate()) return;

    isLoading.value = true;

    try {
      final user = await repository.login(
        emailController.text.trim(),
        passwordController.text,
      );

      if (user == null) {
        Get.snackbar(
          'Login Gagal',
          'Email atau password salah.',
        );
        return;
      }

      Get.snackbar(
        'Berhasil',
        'Selamat datang, ${user.name}!',
      );

      Get.offAllNamed(AppRoutes.home);
    } catch (e) {
      Get.snackbar('Error', 'Terjadi kesalahan: $e');
    } finally {
      isLoading.value = false;
    }
  }

  @override
  void onClose() {
    emailController.dispose();
    passwordController.dispose();
    nameController.dispose();
    confirmPasswordController.dispose();
    super.onClose();
  }
}
