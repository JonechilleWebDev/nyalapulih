import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../app/core/theme/app_colors.dart';
import '../../../app/routes/app_routes.dart';
import '../../../shared/widgets/auth_text_field.dart';
import '../controllers/auth_controller.dart';

class RegisterView extends GetView<AuthController> {
  const RegisterView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(
                maxWidth: 430,
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const SizedBox(height: 16),

                  // Logo
                  Center(
                    child: Container(
                      width: 70,
                      height: 70,
                      decoration: BoxDecoration(
                        color: AppColors.primary,
                        borderRadius: BorderRadius.circular(22),
                      ),
                      child: const Icon(
                        Icons.spa_rounded,
                        color: AppColors.dark,
                        size: 40,
                      ),
                    ),
                  ),

                  const SizedBox(height: 20),

                  const Text(
                    'Buat Akun Baru',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 27,
                      fontWeight: FontWeight.bold,
                      color: AppColors.dark,
                    ),
                  ),

                  const SizedBox(height: 8),

                  const Text(
                    'Bersama NyalaPulih, mulai langkah kecil\nmenuju kehidupan yang lebih baik.',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 14,
                      height: 1.6,
                      color: AppColors.mutedText,
                    ),
                  ),

                  const SizedBox(height: 28),

                  // Register Card
                  Container(
                    padding: const EdgeInsets.all(24),
                    decoration: BoxDecoration(
                      color: AppColors.cardSurface,
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(
                        color: AppColors.border,
                      ),
                    ),
                    child: Form(
                      key: controller.registerFormKey,
                      child: Column(
                        crossAxisAlignment:
                            CrossAxisAlignment.stretch,
                        children: [
                          const Text(
                            'Registrasi',
                            style: TextStyle(
                              fontSize: 21,
                              fontWeight: FontWeight.bold,
                              color: AppColors.dark,
                            ),
                          ),

                          const SizedBox(height: 24),

                          AuthTextField(
                            label: 'Nama Lengkap',
                            hint: 'Masukkan nama lengkap',
                            icon: Icons.person_outline,
                            controller: controller.nameController,
                            validator: controller.validateName,
                          ),

                          const SizedBox(height: 18),

                          AuthTextField(
                            label: 'Email',
                            hint: 'Masukkan email kamu',
                            icon: Icons.email_outlined,
                            controller: controller.emailController,
                            keyboardType:
                                TextInputType.emailAddress,
                            validator: controller.validateEmail,
                          ),

                          const SizedBox(height: 18),

                          Obx(
                            () => AuthTextField(
                              label: 'Password',
                              hint: 'Minimal 8 karakter',
                              icon: Icons.lock_outline,
                              controller:
                                  controller.passwordController,
                              obscureText: controller
                                  .isRegisterPasswordHidden.value,
                              validator:
                                  controller.validatePassword,
                              suffixIcon: IconButton(
                                onPressed:
                                    controller.toggleRegisterPassword,
                                icon: Icon(
                                  controller.isRegisterPasswordHidden.value
                                      ? Icons.visibility_off_outlined
                                      : Icons.visibility_outlined,
                                  color: AppColors.mutedText,
                                ),
                              ),
                            ),
                          ),

                          const SizedBox(height: 18),

                          Obx(
                            () => AuthTextField(
                              label: 'Konfirmasi Password',
                              hint: 'Ulangi password kamu',
                              icon: Icons.lock_reset_outlined,
                              controller: controller
                                  .confirmPasswordController,
                              obscureText: controller
                                  .isConfirmPasswordHidden.value,
                              validator:
                                  controller.validateConfirmPassword,
                              suffixIcon: IconButton(
                                onPressed: controller
                                    .toggleConfirmPassword,
                                icon: Icon(
                                  controller.isConfirmPasswordHidden.value
                                      ? Icons.visibility_off_outlined
                                      : Icons.visibility_outlined,
                                  color: AppColors.mutedText,
                                ),
                              ),
                            ),
                          ),

                          const SizedBox(height: 28),

                          
                            SizedBox(
                            height: 54,
                            child: Obx(
                                () => ElevatedButton(
                                onPressed: controller.isLoading.value
                                    ? null
                                    : controller.register,
                                style: ElevatedButton.styleFrom(
                                    backgroundColor: AppColors.accent,
                                    foregroundColor: AppColors.cardSurface,
                                    disabledBackgroundColor: AppColors.primary,
                                    elevation: 0,
                                    shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(14),
                                    ),
                                ),
                                child: controller.isLoading.value
                                    ? const SizedBox(
                                        width: 22,
                                        height: 22,
                                        child: CircularProgressIndicator(
                                            strokeWidth: 2.5,
                                            color: AppColors.dark,
                                        ),
                                        )
                                    : const Text(
                                        'Daftar Sekarang',
                                        style: TextStyle(
                                            fontSize: 16,
                                            fontWeight: FontWeight.bold,
                                        ),
                                        ),
                                ),
                            ),
                            ),
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(height: 20),

                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Text(
                        'Sudah punya akun?',
                        style: TextStyle(
                          color: AppColors.mutedText,
                        ),
                      ),
                      TextButton(
                        onPressed: () {
                          Get.offNamed(AppRoutes.login);
                        },
                        child: const Text(
                          'Masuk',
                          style: TextStyle(
                            color: AppColors.accent,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
