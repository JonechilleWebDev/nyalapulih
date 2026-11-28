import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../data/models/user_model.dart';
import '../../../data/providers/auth_provider.dart';
import '../../../data/repositories/auth_repository.dart';

class ProfileController extends GetxController {
  final AuthRepository repository =
      AuthRepository(AuthProvider());

  final Rxn<UserModel> user = Rxn<UserModel>();

  final isLoading = false.obs;
  final isSaving = false.obs;

  // Edit profile controllers
  final nameController = TextEditingController();
  final emailController = TextEditingController();
  final organizationController = TextEditingController();
  final roleController = TextEditingController();

  // Change password controllers
  final currentPasswordController = TextEditingController();
  final newPasswordController = TextEditingController();
  final confirmPasswordController = TextEditingController();

  final showCurrentPassword = false.obs;
  final showNewPassword = false.obs;
  final showConfirmPassword = false.obs;

  @override
  void onInit() {
    super.onInit();
    loadProfile();
  }

  String _formatRole(String role) {
    switch (role.toLowerCase()) {
        case 'relawan':
        case 'sukarelawan':
        case 'volunteer':
        return 'Relawan';
        case 'admin':
        return 'Administrator';
        default:
        return role;
    }
  }

  Future<void> loadProfile() async {
    try {
      isLoading.value = true;

      final result = await repository.getProfile();
      user.value = result;

      nameController.text = result.name;
      emailController.text = result.email;
      organizationController.text = result.organization ?? '';
      roleController.text = _formatRole(result.role);
    } catch (e) {
      Get.snackbar(
        'Gagal',
        e.toString().replaceFirst('Exception: ', ''),
        snackPosition: SnackPosition.BOTTOM,
      );
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> updateProfile() async {
    final name = nameController.text.trim();
    final email = emailController.text.trim();
    final organization = organizationController.text.trim();

    if (name.isEmpty || email.isEmpty) {
      Get.snackbar(
        'Validasi',
        'Nama dan email wajib diisi',
        snackPosition: SnackPosition.BOTTOM,
      );
      return;
    }

    if (!GetUtils.isEmail(email)) {
      Get.snackbar(
        'Validasi',
        'Format email tidak valid',
        snackPosition: SnackPosition.BOTTOM,
      );
      return;
    }

    try {
      isSaving.value = true;

      final result = await repository.updateProfile(
        name: name,
        email: email,
        organization: organization,
      );

      user.value = result;

      Get.back();

      Get.snackbar(
        'Berhasil',
        'Profil berhasil diperbarui',
        snackPosition: SnackPosition.BOTTOM,
      );
    } catch (e) {
      Get.snackbar(
        'Gagal',
        e.toString().replaceFirst('Exception: ', ''),
        snackPosition: SnackPosition.BOTTOM,
      );
    } finally {
      isSaving.value = false;
    }
  }

  Future<void> changePassword() async {
    final currentPassword =
        currentPasswordController.text;
    final newPassword = newPasswordController.text;
    final confirmPassword =
        confirmPasswordController.text;

    if (currentPassword.isEmpty ||
        newPassword.isEmpty ||
        confirmPassword.isEmpty) {
      Get.snackbar(
        'Validasi',
        'Semua password wajib diisi',
        snackPosition: SnackPosition.BOTTOM,
      );
      return;
    }

    if (newPassword.length < 8) {
      Get.snackbar(
        'Validasi',
        'Password baru minimal 8 karakter',
        snackPosition: SnackPosition.BOTTOM,
      );
      return;
    }

    if (newPassword != confirmPassword) {
      Get.snackbar(
        'Validasi',
        'Konfirmasi password tidak cocok',
        snackPosition: SnackPosition.BOTTOM,
      );
      return;
    }

    try {
      isSaving.value = true;

      await repository.changePassword(
        currentPassword: currentPassword,
        newPassword: newPassword,
        confirmPassword: confirmPassword,
      );

      Get.snackbar(
        'Berhasil',
        'Password berhasil diubah. Silakan login kembali.',
        snackPosition: SnackPosition.BOTTOM,
      );

      // Token sudah dicabut oleh backend dan dihapus provider.
      // Ganti '/login' jika nama route login di proyekmu berbeda.
      Get.offAllNamed('/login');
    } catch (e) {
      Get.snackbar(
        'Gagal',
        e.toString().replaceFirst('Exception: ', ''),
        snackPosition: SnackPosition.BOTTOM,
      );
    } finally {
      isSaving.value = false;
    }
  }

  @override
  void onClose() {
    nameController.dispose();
    emailController.dispose();
    organizationController.dispose();
    roleController.dispose();

    currentPasswordController.dispose();
    newPasswordController.dispose();
    confirmPasswordController.dispose();

    super.onClose();
  }
}
