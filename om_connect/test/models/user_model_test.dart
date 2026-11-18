import 'package:flutter_test/flutter_test.dart';
import 'package:om_connect/features/auth/data/models/user_model.dart';

void main() {
  group('User Model Tests', () {
    test('User.fromJson parses shareholder correctly', () {
      final json = {
        'id': 1,
        'name': 'Test Shareholder',
        'email': 'shareholder@example.com',
        'roles': ['shareholder'],
        'reward_wallet': {
          'total_points': '150.00',
          'total_bonus': '20.00',
          'total_royalty': '50.00',
          'total_value': '220.00',
        },
        'shareholder_account': {
          'id': 1,
          'boid': '1301000000001234',
          'verification_status': 'verified',
        },
      };

      final user = User.fromJson(json);

      expect(user.id, 1);
      expect(user.name, 'Test Shareholder');
      expect(user.isShareholder, true);
      expect(user.primaryRole, 'shareholder');
      expect(user.rewardWallet?.totalPoints, '150.00');
      expect(user.shareholderAccount?.isVerified, true);
    });

    test('User.fromJson parses pharmacy and doctor roles', () {
      final pharmacyJson = {
        'id': 2,
        'name': 'Test Pharmacy',
        'email': 'pharmacy@example.com',
        'roles': ['pharmacy'],
      };

      final doctorJson = {
        'id': 3,
        'name': 'Test Doctor',
        'email': 'doctor@example.com',
        'roles': ['doctor'],
      };

      final pharmacyUser = User.fromJson(pharmacyJson);
      final doctorUser = User.fromJson(doctorJson);

      expect(pharmacyUser.isPharmacy, true);
      expect(pharmacyUser.primaryRole, 'pharmacy');

      expect(doctorUser.isDoctor, true);
      expect(doctorUser.primaryRole, 'doctor');
    });

    test('AuthResponse parses correctly', () {
      final json = {
        'message': 'Success',
        'token': 'sample-bearer-token',
        'user': {
          'id': 1,
          'name': 'John Doe',
          'email': 'john@example.com',
          'roles': ['shareholder'],
        },
      };

      final authResponse = AuthResponse.fromJson(json);
      expect(authResponse.token, 'sample-bearer-token');
      expect(authResponse.user.name, 'John Doe');
      expect(authResponse.user.email, 'john@example.com');
    });
  });
}
