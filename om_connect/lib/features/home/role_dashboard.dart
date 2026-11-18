import 'package:flutter/material.dart';
import '../auth/data/models/user_model.dart';
import '../shareholder/presentation/screens/shareholder_dashboard_screen.dart';
import '../pharmacy/presentation/screens/pharmacy_dashboard_screen.dart';
import '../doctor/presentation/screens/doctor_dashboard_screen.dart';
import 'user_dashboard_screen.dart';

/// Routes to the correct dashboard based on the user's primary role
class RoleDashboard extends StatelessWidget {
  final User? user;

  const RoleDashboard({super.key, this.user});

  @override
  Widget build(BuildContext context) {
    if (user == null) {
      return const Center(child: CircularProgressIndicator());
    }

    switch (user!.primaryRole) {
      case 'shareholder':
        return const ShareholderDashboardScreen();
      case 'pharmacy':
        return const PharmacyDashboardScreen();
      case 'doctor':
        return const DoctorDashboardScreen();
      case 'customer':
      case 'user':
      default:
        return const UserDashboardScreen();
    }
  }
}
