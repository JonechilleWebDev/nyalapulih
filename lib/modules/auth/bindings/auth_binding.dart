import 'package:get/get.dart';

import '../../../data/providers/auth_provider.dart';
import '../../../data/repositories/auth_repository.dart';
import '../controllers/auth_controller.dart';

class AuthBinding extends Bindings {
  @override
  void dependencies() {
    Get.lazyPut<AuthProvider>(
      () => AuthProvider(),
      fenix: true,
    );

    Get.lazyPut<AuthRepository>(
      () => AuthRepository(Get.find<AuthProvider>()),
      fenix: true,
    );

    Get.lazyPut<AuthController>(
      () => AuthController(
        Get.find<AuthRepository>(),
      ),
      fenix: true,
    );
  }
}
