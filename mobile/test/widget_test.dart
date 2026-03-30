import 'package:flutter_test/flutter_test.dart';

import 'package:cloudvault_mobile/src/presentation/cloudvault_app.dart';

void main() {
  testWidgets('CloudVault login screen renders', (WidgetTester tester) async {
    await tester.pumpWidget(const CloudVaultApp());
    await tester.pumpAndSettle();

    expect(find.text('CloudVault'), findsOneWidget);
  });
}
