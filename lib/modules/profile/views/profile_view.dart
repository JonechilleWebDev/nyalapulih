import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/profile_controller.dart';
import 'edit_profile_view.dart';
import 'change_password_view.dart';

const Color bgMain = Color(0xFFF8F7F4);
const Color primaryColor = Color(0xFFDFC7A7);
const Color darkColor = Color(0xFF432818);
const Color accentColor = Color(0xFFBC6C25);
const Color mutedColor = Color(0xFF806D5C);

class ProfileView extends GetView<ProfileController> {
  const ProfileView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: bgMain,
      body: SafeArea(
        child: Obx(() {
          if (controller.isLoading.value &&
              controller.user.value == null) {
            return const Center(
              child: CircularProgressIndicator(
                color: accentColor,
              ),
            );
          }

          final user = controller.user.value;

          return RefreshIndicator(
            color: accentColor,
            onRefresh: controller.loadProfile,
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              child: Column(
                children: [
                  _buildHeader(context, user?.name ?? 'Pengguna'),
                  const SizedBox(height: 12),

                  Text(
                    user?.name ?? '-',
                    style: const TextStyle(
                      fontSize: 21,
                      fontWeight: FontWeight.bold,
                      color: darkColor,
                    ),
                  ),

                  const SizedBox(height: 5),

                  Text(
                    user?.email ?? '-',
                    style: const TextStyle(
                      fontSize: 12,
                      color: mutedColor,
                    ),
                  ),

                  const SizedBox(height: 5),

                  if ((user?.organization ?? '').isNotEmpty)
                    Text(
                      user!.organization!,
                      style: const TextStyle(
                        fontSize: 12,
                        color: accentColor,
                      ),
                    ),

                  const SizedBox(height: 5),

                  Text(
                    _formatRole(user?.role ?? ''),
                    style: const TextStyle(
                      fontSize: 11,
                      color: mutedColor,
                    ),
                  ),

                  const SizedBox(height: 24),

                  _buildMenuCard([
                    _menuItem(
                      icon: Icons.manage_accounts_outlined,
                      title: 'Edit informasi profil',
                      onTap: () {
                        Get.to(
                          () => const EditProfileView(),
                        );
                      },
                    ),
                    _menuItem(
                      icon: Icons.notifications_none,
                      title: 'Notifikasi',
                      trailing: 'ON',
                      onTap: () {},
                    ),
                    _menuItem(
                      icon: Icons.translate,
                      title: 'Bahasa',
                      trailing: 'Indonesia',
                      onTap: () {},
                    ),
                  ]),

                  const SizedBox(height: 14),

                  _buildMenuCard([
                    _menuItem(
                      icon: Icons.security_outlined,
                      title: 'Keamanan akun',
                      onTap: () {
                        Get.to(
                          () => const ChangePasswordView(),
                        );
                      },
                    ),
                    _menuItem(
                      icon: Icons.palette_outlined,
                      title: 'Tema',
                      trailing: 'Light mode',
                      onTap: () {},
                    ),
                  ]),

                  const SizedBox(height: 14),

                  _buildMenuCard([
                    _menuItem(
                      icon: Icons.support_agent,
                      title: 'Bantuan & Dukungan',
                      onTap: () {},
                    ),
                    _menuItem(
                      icon: Icons.contact_support_outlined,
                      title: 'Hubungi kami',
                      onTap: () {},
                    ),
                    _menuItem(
                      icon: Icons.lock_outline,
                      title: 'Kebijakan privasi',
                      onTap: () {},
                    ),
                  ]),

                  const SizedBox(height: 14),

                  _buildMenuCard([
                    _menuItem(
                      icon: Icons.logout,
                      title: 'Keluar akun',
                      titleColor: Colors.redAccent,
                      onTap: () => _confirmLogout(),
                    ),
                  ]),

                  const SizedBox(height: 30),
                ],
              ),
            ),
          );
        }),
      ),
    );
  }

  Widget _buildHeader(BuildContext context, String name) {
    return SizedBox(
      height: 185,
      child: Stack(
        clipBehavior: Clip.none,
        children: [
          ClipPath(
            clipper: ProfileHeaderClipper(),
            child: Container(
              height: 165,
              width: double.infinity,
              color: primaryColor.withValues(alpha: 0.45),
            ),
          ),

          Positioned(
            top: 22,
            left: 20,
            right: 20,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                IconButton(
                  onPressed: () {},
                  icon: const Icon(
                    Icons.notifications_none,
                    color: darkColor,
                  ),
                ),
                Row(
                  children: [
                    IconButton(
                      onPressed: controller.loadProfile,
                      icon: const Icon(
                        Icons.history,
                        color: darkColor,
                      ),
                    ),
                    IconButton(
                      onPressed: () {},
                      icon: const Icon(
                        Icons.more_vert,
                        color: darkColor,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          Positioned(
            bottom: 0,
            left: 0,
            right: 0,
            child: Center(
              child: Stack(
                clipBehavior: Clip.none,
                children: [
                  Container(
                    width: 100,
                    height: 100,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: primaryColor,
                      border: Border.all(
                        color: bgMain,
                        width: 4,
                      ),
                    ),
                    child: const Icon(
                      Icons.person,
                      size: 58,
                      color: darkColor,
                    ),
                  ),
                  Positioned(
                    right: -2,
                    bottom: 2,
                    child: Container(
                      padding: const EdgeInsets.all(7),
                      decoration: const BoxDecoration(
                        shape: BoxShape.circle,
                        color: bgMain,
                      ),
                      child: const Icon(
                        Icons.edit,
                        size: 16,
                        color: darkColor,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMenuCard(List<Widget> children) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 20),
      padding: const EdgeInsets.symmetric(
        horizontal: 13,
        vertical: 8,
      ),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(13),
        border: Border.all(
          color: darkColor.withValues(alpha: 0.08),
        ),
        boxShadow: [
          BoxShadow(
            color: darkColor.withValues(alpha: 0.04),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(children: children),
    );
  }

  Widget _menuItem({
    required IconData icon,
    required String title,
    String? trailing,
    Color? titleColor,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 11),
        child: Row(
          children: [
            Icon(icon, size: 21, color: darkColor),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                title,
                style: TextStyle(
                  fontSize: 13,
                  color: titleColor ?? darkColor,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
            if (trailing != null)
              Text(
                trailing,
                style: const TextStyle(
                  color: accentColor,
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                ),
              ),
          ],
        ),
      ),
    );
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

  void _confirmLogout() {
    Get.dialog(
      AlertDialog(
        backgroundColor: bgMain,
        title: const Text('Keluar akun?'),
        content: const Text(
          'Apakah kamu yakin ingin keluar dari akun NyalaPulih?',
        ),
        actions: [
          TextButton(
            onPressed: Get.back,
            child: const Text('Batal'),
          ),
          TextButton(
            onPressed: () async {
              Get.back();
              try {
                await controller.repository.logout();
                Get.offAllNamed('/login');
              } catch (e) {
                Get.snackbar('Gagal', e.toString());
              }
            },
            child: const Text(
              'Keluar',
              style: TextStyle(color: Colors.redAccent),
            ),
          ),
        ],
      ),
    );
  }
}

class ProfileHeaderClipper extends CustomClipper<Path> {
  @override
  Path getClip(Size size) {
    final path = Path();

    path.lineTo(0, size.height - 30);

    path.quadraticBezierTo(
      size.width / 2,
      size.height + 25,
      size.width,
      size.height - 30,
    );

    path.lineTo(size.width, 0);
    path.close();

    return path;
  }

  @override
  bool shouldReclip(covariant CustomClipper<Path> oldClipper) {
    return false;
  }
}
