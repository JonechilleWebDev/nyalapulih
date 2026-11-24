import 'package:flutter_test/flutter_test.dart';
import 'package:get/get.dart';
import 'package:nyalapulih/modules/home/bindings/home_binding.dart';
import 'package:nyalapulih/modules/home/views/home_view.dart';

void main() {
  testWidgets('navigation shows and switches between all tabs', (tester) async {
    await tester.pumpWidget(
      GetMaterialApp(initialBinding: HomeBinding(), home: const HomeView()),
    );

    expect(find.text('Home'), findsNWidgets(2));
    expect(find.text('Profil'), findsOneWidget);

    await tester.tap(find.text('Jelajahi').last);
    await tester.pumpAndSettle();
    expect(
      find.text('Temukan hal baru yang mendukung perjalanan pulihmu.'),
      findsOneWidget,
    );

    await tester.tap(find.text('Aktivitas').last);
    await tester.pumpAndSettle();
    expect(find.text('Aktivitasmu akan muncul di sini.'), findsOneWidget);

    await tester.tap(find.text('Profil').last);
    await tester.pumpAndSettle();
    expect(
      find.text('Kelola informasi dan pengaturan akun Anda.'),
      findsOneWidget,
    );

    await tester.tap(find.text('Home').last);
    await tester.pumpAndSettle();
    expect(
      find.text('Mulai perjalanan pulihmu dari satu langkah kecil hari ini.'),
      findsOneWidget,
    );
  });
}
