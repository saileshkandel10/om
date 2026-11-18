import 'package:flutter_test/flutter_test.dart';
import 'package:om_connect/core/api/api_endpoints.dart';

void main() {
  group('ApiEndpoints Tests', () {
    test('dynamic endpoints generate correct URL paths', () {
      expect(ApiEndpoints.productDetail(42), '/products/42');
      expect(ApiEndpoints.billDetail(7), '/bills/7');
      expect(ApiEndpoints.notificationRead(99), '/notifications/99/read');
      expect(ApiEndpoints.noticeDetail(12), '/notices/12');
      expect(ApiEndpoints.campaignDetail(5), '/campaigns/5');
      expect(ApiEndpoints.doctorPrescriptionDetail(3), '/doctor/prescriptions/3');
    });

    test('static endpoints have valid relative routes', () {
      expect(ApiEndpoints.login, '/auth/login');
      expect(ApiEndpoints.register, '/auth/register');
      expect(ApiEndpoints.products, '/products');
      expect(ApiEndpoints.bills, '/bills');
      expect(ApiEndpoints.rewardWallet, '/rewards/wallet');
      expect(ApiEndpoints.shareholderVerifyBoid, '/shareholder/verify-boid');
      expect(ApiEndpoints.pharmacyDashboard, '/pharmacy/dashboard');
      expect(ApiEndpoints.doctorDashboard, '/doctor/dashboard');
    });
  });
}
