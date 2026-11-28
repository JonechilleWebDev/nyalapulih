import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/profile_controller.dart';
import 'profile_view.dart';

class ChangePasswordView extends GetView<ProfileController> {
  const ChangePasswordView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: bgMain,
      appBar: AppBar(
        backgroundColor: bgMain,
        foregroundColor: darkColor,
        elevation: 0,
        title: const Text(
          'Ubah Password',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(22),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 90,
                height: 90,
                decoration: BoxDecoration(
                  color: primaryColor.withValues(alpha: 0.45),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.lock_outline,
                  size: 44,
                  color: darkColor,
                ),
              ),
            ),

            const SizedBox(height: 24),

            const Text(
              'Perbarui Password',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: darkColor,
              ),
            ),

            const SizedBox(height: 8),

            const Text(
              'Gunakan password yang kuat dan jangan bagikan kepada siapa pun.',
              style: TextStyle(
                color: mutedColor,
                fontSize: 13,
                height: 1.5,
              ),
            ),

            const SizedBox(height: 28),

            Obx(() => _passwordField(
              label: 'Password saat ini',
              hint: 'Masukkan password saat ini',
              controller: controller.currentPasswordController,
              visible: controller.showCurrentPassword.value,
              onToggle: () {
                controller.showCurrentPassword.toggle();
              },
            )),

            const SizedBox(height: 20),

            Obx(() => _passwordField(
              label: 'Password baru',
              hint: 'Masukkan password baru',
              controller: controller.newPasswordController,
              visible: controller.showNewPassword.value,
              onToggle: () {
                controller.showNewPassword.toggle();
              },
            )),

            const SizedBox(height: 20),

            Obx(() => _passwordField(
              label: 'Konfirmasi password baru',
              hint: 'Ulangi password baru',
              controller: controller.confirmPasswordController,
              visible: controller.showConfirmPassword.value,
              onToggle: () {
                controller.showConfirmPassword.toggle();
              },
            )),

            const SizedBox(height: 12),

            const Text(
              '• Minimal 8 karakter\n'
              '• Password baru harus berbeda dari password lama\n'
              '• Konfirmasi harus sama dengan password baru',
              style: TextStyle(
                fontSize: 12,
                color: mutedColor,
                height: 1.7,
              ),
            ),

            const SizedBox(height: 30),

            Obx(() => SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton(
                onPressed: controller.isSaving.value
                    ? null
                    : controller.changePassword,
                style: ElevatedButton.styleFrom(
                  backgroundColor: accentColor,
                  foregroundColor: Colors.white,
                  disabledBackgroundColor:
                      accentColor.withValues(alpha: 0.5),
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: controller.isSaving.value
                    ? const SizedBox(
                        width: 22,
                        height: 22,
                        child: CircularProgressIndicator(
                          color: Colors.white,
                          strokeWidth: 2,
                        ),
                      )
                    : const Text(
                        'Simpan Password Baru',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                        ),
                      ),
              ),
            )),
          ],
        ),
      ),
    );
  }

  Widget _passwordField({
    required String label,
    required String hint,
    required TextEditingController controller,
    required bool visible,
    required VoidCallback onToggle,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            color: darkColor,
            fontWeight: FontWeight.w600,
            fontSize: 13,
          ),
        ),
        const SizedBox(height: 8),
        TextField(
          controller: controller,
          obscureText: !visible,
          autocorrect: false,
          enableSuggestions: false,
          style: const TextStyle(
            color: darkColor,
            fontSize: 14,
          ),
          decoration: InputDecoration(
            hintText: hint,
            prefixIcon: const Icon(
              Icons.lock_outline,
              color: mutedColor,
              size: 20,
            ),
            suffixIcon: IconButton(
              onPressed: onToggle,
              icon: Icon(
                visible
                    ? Icons.visibility_outlined
                    : Icons.visibility_off_outlined,
                color: mutedColor,
              ),
            ),
            filled: true,
            fillColor: Colors.white,
            contentPadding: const EdgeInsets.symmetric(
              horizontal: 15,
              vertical: 15,
            ),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(
                color: darkColor.withValues(alpha: 0.12),
              ),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(
                color: darkColor.withValues(alpha: 0.12),
              ),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(
                color: accentColor,
              ),
            ),
          ),
        ),
      ],
    );
  }
}
