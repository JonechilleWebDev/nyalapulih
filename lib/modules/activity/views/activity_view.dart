import 'package:flutter/material.dart';
import 'package:nyalapulih/app/core/theme/app_colors.dart';

class ActivityView extends StatelessWidget {
  const ActivityView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgMain,
      appBar: AppBar(title: const Text('Aktivitas')),
      body: const Center(
        child: Padding(
          padding: EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.event_note_rounded, color: AppColors.accent, size: 48),
              SizedBox(height: 16),
              Text(
                'Aktivitas',
                style: TextStyle(
                  color: AppColors.dark,
                  fontSize: 22,
                  fontWeight: FontWeight.w700,
                ),
              ),
              SizedBox(height: 8),
              Text(
                'Aktivitasmu akan muncul di sini.',
                textAlign: TextAlign.center,
                style: TextStyle(color: AppColors.mutedText),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
