import 'package:dio/dio.dart';
import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/storage/secure_storage.dart';
import '../data/models/user_model.dart';

class AuthRepository {
  Dio get _dio => ApiClient.instance;

  Future<AuthResponse> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    required String role,
    String? mobile,
    String deviceName = 'mobile',
  }) async {
    try {
      final response = await _dio.post(ApiEndpoints.register, data: {
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': passwordConfirmation,
        'mobile': mobile,
        'role': role,
        'device_name': deviceName,
      });

      final authResponse =
          AuthResponse.fromJson(response.data as Map<String, dynamic>);

      await SecureStorage.saveToken(authResponse.token);
      await SecureStorage.saveUserData(authResponse.user.toJsonString());

      return authResponse;
    } on DioException catch (e) {
      throw ApiException.fromDioError(e);
    }
  }

  Future<AuthResponse> login({
    required String email,
    required String password,
    String deviceName = 'mobile',
  }) async {
    try {
      final response = await _dio.post(ApiEndpoints.login, data: {
        'email': email,
        'password': password,
        'device_name': deviceName,
      });

      final authResponse =
          AuthResponse.fromJson(response.data as Map<String, dynamic>);

      await SecureStorage.saveToken(authResponse.token);
      await SecureStorage.saveUserData(authResponse.user.toJsonString());

      return authResponse;
    } on DioException catch (e) {
      throw ApiException.fromDioError(e);
    }
  }

  Future<void> logout() async {
    try {
      await _dio.post(ApiEndpoints.logout);
    } on DioException catch (_) {
      // Even if the API call fails, clear local data
    } finally {
      await SecureStorage.clearAll();
      ApiClient.reset();
    }
  }

  Future<User> getUser() async {
    try {
      final response = await _dio.get(ApiEndpoints.user);
      final data = response.data as Map<String, dynamic>;
      final userData = data['data'] as Map<String, dynamic>? ?? data;
      final user = User.fromJson(userData);
      await SecureStorage.saveUserData(user.toJsonString());
      return user;
    } on DioException catch (e) {
      throw ApiException.fromDioError(e);
    }
  }

  Future<User?> getCachedUser() async {
    try {
      final userData = await SecureStorage.getUserData();
      if (userData != null && userData.isNotEmpty) {
        return User.fromJsonString(userData);
      }
    } catch (_) {}
    return null;
  }
}
