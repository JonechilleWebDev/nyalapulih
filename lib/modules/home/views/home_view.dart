// lib/app/modules/home/views/home_view.dart
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:nyalapulih/app/core/theme/app_colors.dart';
import 'package:nyalapulih/shared/widgets/bottom_navbar.dart';
import 'package:nyalapulih/modules/activity/views/activity_view.dart';
import 'package:nyalapulih/modules/explore/views/explore_view.dart';
import 'package:nyalapulih/modules/profile/views/profile_view.dart';

import '../controllers/home_controller.dart';

class HomeView extends GetView<HomeController> {
  const HomeView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgMain,
      body: SafeArea(
        child: Obx(
          () => IndexedStack(
            index: controller.selectedIndex.value,
            children: const [
              _HomeTab(),
              ExploreView(),
              ActivityView(),
              ProfileView(),
            ],
          ),
        ),
      ),
      bottomNavigationBar: Obx(
        () => AppBottomNavBar(
          currentIndex: controller.selectedIndex.value,
          onTap: controller.changeTab,
        ),
      ),
    );
  }
}

class _HomeTab extends StatelessWidget {
  const _HomeTab();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgMain,
      appBar: AppBar(title: const Text('Home')),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Selamat datang',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                color: AppColors.dark,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Mulai perjalanan pulihmu dari satu langkah kecil hari ini.',
              style: TextStyle(color: AppColors.mutedText),
            ),
            const SizedBox(height: 24),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.primary,
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Icon(
                    Icons.local_fire_department_rounded,
                    color: AppColors.accent,
                    size: 30,
                  ),
                  SizedBox(height: 12),
                  Text(
                    'Ruang untuk bertumbuh',
                    style: TextStyle(
                      color: AppColors.dark,
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  SizedBox(height: 6),
                  Text(
                    'Jelajahi aktivitas dan temukan hal yang mendukung prosesmu.',
                    style: TextStyle(color: AppColors.dark),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
