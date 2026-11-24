import 'package:flutter/material.dart';
import 'package:nyalapulih/app/core/theme/app_colors.dart';

class ExploreView extends StatelessWidget {
  const ExploreView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgMain,
      appBar: AppBar(title: const Text('Jelajahi')),
      body: const Padding(
        padding: EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Jelajahi',
              style: TextStyle(
                color: AppColors.dark,
                fontSize: 24,
                fontWeight: FontWeight.w700,
              ),
            ),
            SizedBox(height: 8),
            Text(
              'Temukan hal baru yang mendukung perjalanan pulihmu.',
              style: TextStyle(color: AppColors.mutedText),
            ),
            SizedBox(height: 24),
            _ExploreCard(
              icon: Icons.self_improvement_rounded,
              title: 'Ruang untuk dirimu',
              description: 'Luangkan waktu untuk mengenal kebutuhanmu.',
            ),
            SizedBox(height: 12),
            _ExploreCard(
              icon: Icons.lightbulb_outline_rounded,
              title: 'Temukan inspirasi',
              description: 'Satu langkah kecil dapat membuka kemungkinan baru.',
            ),
          ],
        ),
      ),
    );
  }
}

class _ExploreCard extends StatelessWidget {
  const _ExploreCard({
    required this.icon,
    required this.title,
    required this.description,
  });

  final IconData icon;
  final String title;
  final String description;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppColors.primary,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          Icon(icon, color: AppColors.accent, size: 28),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    color: AppColors.dark,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  description,
                  style: const TextStyle(color: AppColors.dark),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
