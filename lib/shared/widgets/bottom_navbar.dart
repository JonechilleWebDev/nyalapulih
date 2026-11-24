// lib/app/shared/widgets/app_bottom_nav_bar.dart
import 'package:flutter/material.dart';
import 'package:nyalapulih/app/core/theme/app_colors.dart';

class AppBottomNavBar extends StatefulWidget {
  const AppBottomNavBar({
    super.key,
    required this.currentIndex,
    required this.onTap,
  });

  final int currentIndex;
  final ValueChanged<int> onTap;

  @override
  State<AppBottomNavBar> createState() => _AppBottomNavBarState();
}

class _AppBottomNavBarState extends State<AppBottomNavBar> {
  int? _hoveredIndex;

  static const _items = [
    _NavigationItem(Icons.home_outlined, Icons.home_rounded, 'Home'),
    _NavigationItem(Icons.explore_outlined, Icons.explore_rounded, 'Jelajahi'),
    _NavigationItem(
      Icons.assignment_outlined,
      Icons.assignment_rounded,
      'Aktivitas',
    ),
    _NavigationItem(
      Icons.person_outline_rounded,
      Icons.person_rounded,
      'Profil',
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.cardSurface,
        border: Border(top: BorderSide(color: AppColors.border, width: 1)),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
      child: Row(
        children: List.generate(_items.length, (index) {
          final item = _items[index];
          final isSelected = widget.currentIndex == index;
          final isHovered = _hoveredIndex == index;
          final color = isSelected ? AppColors.accent : AppColors.mutedText;

          return Expanded(
            child: MouseRegion(
              onEnter: (_) => setState(() => _hoveredIndex = index),
              onExit: (_) => setState(() => _hoveredIndex = null),
              child: Semantics(
                button: true,
                selected: isSelected,
                label: item.label,
                child: InkWell(
                  borderRadius: BorderRadius.circular(14),
                  onTap: () => widget.onTap(index),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 160),
                    margin: const EdgeInsets.symmetric(horizontal: 3),
                    padding: const EdgeInsets.symmetric(vertical: 7),
                    decoration: BoxDecoration(
                      color: isSelected
                          ? AppColors.primary.withValues(alpha: 0.42)
                          : isHovered
                          ? AppColors.primary.withValues(alpha: 0.2)
                          : Colors.transparent,
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          isSelected ? item.activeIcon : item.icon,
                          color: color,
                        ),
                        const SizedBox(height: 3),
                        Text(
                          item.label,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            color: color,
                            fontSize: 11,
                            fontWeight: isSelected
                                ? FontWeight.w700
                                : FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          );
        }),
      ),
    );
  }
}

class _NavigationItem {
  const _NavigationItem(this.icon, this.activeIcon, this.label);

  final IconData icon;
  final IconData activeIcon;
  final String label;
}
