import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../features/auth/domain/auth_provider.dart';
import '../../features/auth/presentation/screens/login_screen.dart';
import '../../features/auth/presentation/screens/register_screen.dart';
import '../../features/auth/presentation/screens/splash_screen.dart';
import '../../features/home/home_screen.dart';
import '../../features/bills/presentation/screens/bill_upload_screen.dart';
import '../../features/bills/presentation/screens/bill_list_screen.dart';
import '../../features/rewards/presentation/screens/reward_wallet_screen.dart';
import '../../features/shareholder/presentation/screens/boid_verification_screen.dart';
import '../../features/shareholder/presentation/screens/shareholder_dashboard_screen.dart';
import '../../features/pharmacy/presentation/screens/pharmacy_dashboard_screen.dart';
import '../../features/pharmacy/presentation/screens/pharmacist_quiz_screen.dart';
import '../../features/doctor/presentation/screens/doctor_dashboard_screen.dart';
import '../../features/doctor/presentation/screens/create_prescription_screen.dart';
import '../../features/products/presentation/screens/product_catalog_screen.dart';
import '../../features/notifications/presentation/screens/notification_list_screen.dart';
import '../../features/profile/presentation/screens/profile_screen.dart';
import '../../features/notices/presentation/screens/notice_list_screen.dart';

final routerProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authProvider);

  return GoRouter(
    initialLocation: '/',
    redirect: (context, state) {
      final loc = state.matchedLocation;
      final isAuthRoute = loc == '/login' || loc == '/register';
      final isSplash = loc == '/';

      if (authState.status == AuthStatus.initial) {
        return isSplash ? null : '/';
      }

      if (authState.status == AuthStatus.loading) {
        // Allow ongoing loading states without interrupting current view
        return null;
      }

      if (authState.isAuthenticated) {
        if (isAuthRoute || isSplash) return '/home';
        return null;
      }

      // Unauthenticated or Error
      if (!isAuthRoute) {
        return '/login';
      }

      return null;
    },
    routes: [
      GoRoute(path: '/', builder: (_, __) => const SplashScreen()),
      GoRoute(path: '/login', builder: (_, __) => const LoginScreen()),
      GoRoute(path: '/register', builder: (_, __) => const RegisterScreen()),
      GoRoute(path: '/home', builder: (_, __) => const HomeScreen()),
      GoRoute(
          path: '/bills/upload',
          builder: (_, __) => const BillUploadScreen()),
      GoRoute(
          path: '/bills',
          builder: (_, __) => const BillListScreen()),
      GoRoute(
          path: '/rewards',
          builder: (_, __) => const RewardWalletScreen()),
      GoRoute(
          path: '/shareholder/dashboard',
          builder: (_, __) => const ShareholderDashboardScreen()),
      GoRoute(
          path: '/shareholder/verify-boid',
          builder: (_, __) => const BoidVerificationScreen()),
      GoRoute(
          path: '/pharmacy/dashboard',
          builder: (_, __) => const PharmacyDashboardScreen()),
      GoRoute(
          path: '/pharmacy/quiz',
          builder: (_, __) => const PharmacistQuizScreen()),
      GoRoute(
          path: '/doctor/dashboard',
          builder: (_, __) => const DoctorDashboardScreen()),
      GoRoute(
          path: '/doctor/prescriptions/create',
          builder: (_, __) => const CreatePrescriptionScreen()),
      GoRoute(
          path: '/products',
          builder: (_, __) => const ProductCatalogScreen()),
      GoRoute(
          path: '/notifications',
          builder: (_, __) => const NotificationListScreen()),
      GoRoute(
          path: '/profile',
          builder: (_, __) => const ProfileScreen()),
      GoRoute(
          path: '/notices',
          builder: (_, __) => const NoticeListScreen()),
    ],
  );
});
