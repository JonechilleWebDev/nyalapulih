import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';

import 'app/core/theme/app_theme.dart';
import 'app/routes/app_pages.dart';
import 'app/routes/app_routes.dart';
import 'modules/auth/bindings/auth_binding.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  await GetStorage.init();

  runApp(const NyalaPulihApp());
}

class NyalaPulihApp extends StatelessWidget {
  const NyalaPulihApp({super.key});

  @override
  Widget build(BuildContext context) {
    return GetMaterialApp(
      title: 'NyalaPulih',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      initialBinding: AuthBinding(),
      initialRoute: AppRoutes.home,
      getPages: AppPages.routes,
    );
  }
}
