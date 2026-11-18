import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../../../core/config/app_config.dart';
import '../data/models/user_model.dart';
import 'auth_repository.dart';

// Repository provider
final authRepositoryProvider = Provider((ref) => AuthRepository());

// Auth state
enum AuthStatus { initial, loading, authenticated, unauthenticated, error }

class AuthState {
  final AuthStatus status;
  final User? user;
  final String? error;

  const AuthState({
    this.status = AuthStatus.initial,
    this.user,
    this.error,
  });

  AuthState copyWith({AuthStatus? status, User? user, String? error}) {
    return AuthState(
      status: status ?? this.status,
      user: user ?? this.user,
      error: error,
    );
  }

  bool get isAuthenticated => status == AuthStatus.authenticated;
  bool get isLoading => status == AuthStatus.loading;
}

class AuthNotifier extends StateNotifier<AuthState> {
  final AuthRepository _repository;

  AuthNotifier(this._repository) : super(const AuthState());

  /// Check if user has a valid cached session
  Future<void> checkAuth() async {
    state = state.copyWith(status: AuthStatus.loading);
    try {
      final cached = await _repository.getCachedUser();
      if (cached != null) {
        state = AuthState(status: AuthStatus.authenticated, user: cached);
        // Refresh profile in background
        _repository.getUser().then((fresh) {
          state = state.copyWith(user: fresh);
        }).catchError((_) {});
      } else {
        state = const AuthState(status: AuthStatus.unauthenticated);
      }
    } catch (_) {
      state = const AuthState(status: AuthStatus.unauthenticated);
    }
  }

  Future<void> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    required String role,
    String? mobile,
  }) async {
    state = state.copyWith(status: AuthStatus.loading);
    try {
      final response = await _repository.register(
        name: name,
        email: email,
        password: password,
        passwordConfirmation: passwordConfirmation,
        role: role,
        mobile: mobile,
      );
      state = AuthState(status: AuthStatus.authenticated, user: response.user);
    } catch (e) {
      state = AuthState(status: AuthStatus.error, error: e.toString());
    }
  }

  Future<void> login({
    required String email,
    required String password,
  }) async {
    state = state.copyWith(status: AuthStatus.loading);
    try {
      final response = await _repository.login(
        email: email,
        password: password,
      );
      state = AuthState(status: AuthStatus.authenticated, user: response.user);
    } catch (e) {
      // If initial connection failed, attempt automatic candidate server fallback once
      final isNetworkError = e.toString().toLowerCase().contains('connection') ||
          e.toString().toLowerCase().contains('timed out');
      if (isNetworkError) {
        final workingUrl = await AppConfig.autoDetectServer();
        if (workingUrl != null) {
          ApiClient.setBaseUrl(workingUrl);
          try {
            final retryResponse = await _repository.login(
              email: email,
              password: password,
            );
            state = AuthState(status: AuthStatus.authenticated, user: retryResponse.user);
            return;
          } catch (retryError) {
            state = AuthState(status: AuthStatus.error, error: retryError.toString());
            return;
          }
        }
      }

      state = AuthState(status: AuthStatus.error, error: e.toString());
    }
  }

  Future<void> logout() async {
    await _repository.logout();
    state = const AuthState(status: AuthStatus.unauthenticated);
  }

  Future<void> refreshUser() async {
    try {
      final user = await _repository.getUser();
      state = state.copyWith(user: user);
    } catch (_) {}
  }
}

// Global auth state provider
final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier(ref.read(authRepositoryProvider));
});
