import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../app/core/theme/app_colors.dart';
import '../../profile/bindings/profile_binding.dart';
import '../../profile/views/profile_view.dart';
import '../controllers/home_controller.dart';

class HomeView extends GetView<HomeController> {
  const HomeView({super.key});

  static final List<Widget> _pages = [
    const _EmptyPage(),
    const _EmptyPage(),
    const _EmptyPage(),
    const ProfileTab(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgMain,

      body: SafeArea(
        child: Obx(
          () => IndexedStack(
            index: controller.selectedIndex.value,
            children: _pages,
          ),
        ),
      ),

      bottomNavigationBar: Obx(
        () => Container(
          decoration: BoxDecoration(
            color: AppColors.cardSurface,
            border: Border(
              top: BorderSide(
                color: AppColors.border,
                width: 1,
              ),
            ),
          ),
          child: BottomNavigationBar(
            currentIndex: controller.selectedIndex.value,
            onTap: controller.changeTab,
            type: BottomNavigationBarType.fixed,
            backgroundColor: AppColors.cardSurface,
            elevation: 0,
            selectedItemColor: AppColors.accent,
            unselectedItemColor: AppColors.mutedText,
            selectedFontSize: 12,
            unselectedFontSize: 12,
            selectedLabelStyle: const TextStyle(
              fontWeight: FontWeight.w700,
            ),
            unselectedLabelStyle: const TextStyle(
              fontWeight: FontWeight.w500,
            ),
            items: const [
              BottomNavigationBarItem(
                icon: Icon(Icons.home_outlined),
                activeIcon: Icon(Icons.home_rounded),
                label: 'Home',
              ),
              BottomNavigationBarItem(
                icon: Icon(Icons.explore_outlined),
                activeIcon: Icon(Icons.explore_rounded),
                label: 'Jelajahi',
              ),
              BottomNavigationBarItem(
                icon: Icon(Icons.assignment_outlined),
                activeIcon: Icon(Icons.assignment_rounded),
                label: 'Aktivitas',
              ),
              BottomNavigationBarItem(
                icon: Icon(Icons.person_outline_rounded),
                activeIcon: Icon(Icons.person_rounded),
                label: 'Profil',
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class ProfileTab extends StatefulWidget {
  const ProfileTab({super.key});

  @override
  State<ProfileTab> createState() => _ProfileTabState();
}

class _ProfileTabState extends State<ProfileTab> {
  @override
  void initState() {
    super.initState();
    ProfileBinding().dependencies();
  }

  @override
  Widget build(BuildContext context) {
    return const ProfileView();
  }
}

class _EmptyPage extends StatelessWidget {
  const _EmptyPage();

  @override
  Widget build(BuildContext context) {
    return const SizedBox.expand();
  }
}
