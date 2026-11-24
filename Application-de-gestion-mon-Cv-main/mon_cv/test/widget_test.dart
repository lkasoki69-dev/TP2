
import 'package:flutter_test/flutter_test.dart';
import 'package:mon_cv/main.dart';

void main() {
  testWidgets('Mon application CV s affiche', (WidgetTester tester) async {
    await tester.pumpWidget(const MonCvApp());

    expect(find.text('MON ESPACE PERSONNEL'), findsOneWidget);
  });
}

