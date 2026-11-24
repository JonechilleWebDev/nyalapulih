// lib/app/modules/profile/views/profile_view.dart
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:nyalapulih/app/core/theme/app_colors.dart';

import '../controllers/profile_controller.dart';

class ProfileView extends GetView<ProfileController> {
  const ProfileView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgMain,
      appBar: AppBar(
        backgroundColor: AppColors.cardSurface,
        elevation: 0,
        title: const Text('Profil'),
      ),
      body: const SafeArea(
        child: Padding(
          padding: EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              CircleAvatar(
                radius: 34,
                backgroundColor: AppColors.primary,
                child: Icon(
                  Icons.person_rounded,
                  color: AppColors.dark,
                  size: 36,
                ),
              ),
              SizedBox(height: 16),
              Text(
                'Profil Anda',
                style: TextStyle(
                  color: AppColors.dark,
                  fontSize: 22,
                  fontWeight: FontWeight.w700,
                ),
              ),
              SizedBox(height: 8),
              Text(
                'Kelola informasi dan pengaturan akun Anda.',
                style: TextStyle(color: AppColors.mutedText),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
