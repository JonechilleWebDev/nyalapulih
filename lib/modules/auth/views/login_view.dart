import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../app/core/theme/app_colors.dart';
import '../../../app/routes/app_routes.dart';
import '../../../shared/widgets/auth_text_field.dart';
import '../controllers/auth_controller.dart';

class LoginView extends GetView<AuthController> {
  const LoginView({super.key});

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
                  const SizedBox(height: 20),

                  // Logo
                  Center(
                    child: Container(
                      width: 76,
                      height: 76,
                      decoration: BoxDecoration(
                        color: AppColors.primary,
                        borderRadius: BorderRadius.circular(24),
                      ),
                      child: const Icon(
                        Icons.spa_rounded,
                        color: AppColors.dark,
                        size: 42,
                      ),
                    ),
                  ),

                  const SizedBox(height: 24),

                  const Text(
                    'NyalaPulih',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 30,
                      fontWeight: FontWeight.bold,
                      color: AppColors.dark,
                    ),
                  ),

                  const SizedBox(height: 8),

                  const Text(
                    'Selamat datang kembali!\nMulai perjalanan pulihmu bersama kami.',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 14,
                      height: 1.6,
                      color: AppColors.mutedText,
                    ),
                  ),

                  const SizedBox(height: 32),

                  // Login Card
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
                      key: controller.loginFormKey,
                      child: Column(
                        crossAxisAlignment:
                            CrossAxisAlignment.stretch,
                        children: [
                          const Text(
                            'Masuk ke Akun',
                            style: TextStyle(
                              fontSize: 21,
                              fontWeight: FontWeight.bold,
                              color: AppColors.dark,
                            ),
                          ),

                          const SizedBox(height: 24),

                          AuthTextField(
                            label: 'Email',
                            hint: 'Masukkan email kamu',
                            icon: Icons.email_outlined,
                            controller:
                                controller.emailController,
                            keyboardType:
                                TextInputType.emailAddress,
                            validator: controller.validateEmail,
                          ),

                          const SizedBox(height: 20),

                          Obx(
                            () => AuthTextField(
                              label: 'Password',
                              hint: 'Masukkan password',
                              icon: Icons.lock_outline,
                              controller:
                                  controller.passwordController,
                              obscureText: controller
                                  .isLoginPasswordHidden.value,
                              validator:
                                  controller.validatePassword,
                              suffixIcon: IconButton(
                                onPressed:
                                    controller.toggleLoginPassword,
                                icon: Icon(
                                  controller.isLoginPasswordHidden.value
                                      ? Icons.visibility_off_outlined
                                      : Icons.visibility_outlined,
                                  color: AppColors.mutedText,
                                ),
                              ),
                            ),
                          ),

                          const SizedBox(height: 12),

                          Align(
                            alignment: Alignment.centerRight,
                            child: TextButton(
                              onPressed: () {
                                Get.snackbar(
                                  'Informasi',
                                  'Fitur lupa password belum tersedia.',
                                );
                              },
                              child: const Text(
                                'Lupa Password?',
                                style: TextStyle(
                                  color: AppColors.accent,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                          ),

                          const SizedBox(height: 16),

                          
                            SizedBox(
                            height: 54,
                            child: Obx(
                                () => ElevatedButton(
                                onPressed: controller.isLoading.value
                                    ? null
                                    : controller.login,
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
                                        'Masuk',
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

                  const SizedBox(height: 24),

                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Text(
                        'Belum punya akun?',
                        style: TextStyle(
                          color: AppColors.mutedText,
                        ),
                      ),
                      TextButton(
                        onPressed: () {
                          Get.toNamed(AppRoutes.register);
                        },
                        child: const Text(
                          'Daftar Sekarang',
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
