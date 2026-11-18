import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:om_connect/main.dart';

void main() {
  testWidgets('App smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const ProviderScope(child: OmConnectApp()));
    await tester.pump(const Duration(milliseconds: 100));
    expect(find.byType(OmConnectApp), findsOneWidget);
  });
}
