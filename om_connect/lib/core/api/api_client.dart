import 'package:dio/dio.dart';
import '../config/app_config.dart';
import '../storage/secure_storage.dart';

class ApiClient {
  static Dio? _dio;

  static Dio get instance {
    _dio ??= _createDio();
    return _dio!;
  }

  static Dio _createDio() {
    final dio = Dio(BaseOptions(
      baseUrl: AppConfig.baseUrl,
      connectTimeout: Duration(milliseconds: AppConfig.connectTimeout),
      receiveTimeout: Duration(milliseconds: AppConfig.receiveTimeout),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ));

    dio.interceptors.addAll([
      AuthInterceptor(),
      LogInterceptor(
        requestBody: true,
        responseBody: true,
        error: true,
      ),
    ]);

    return dio;
  }

  static void setBaseUrl(String url) {
    AppConfig.setCustomBaseUrl(url);
    if (_dio != null) {
      _dio!.options.baseUrl = url;
    }
  }

  /// Reset the Dio instance (e.g., after logout)
  static void reset() {
    _dio = null;
  }
}

class AuthInterceptor extends Interceptor {
  @override
  Future<void> onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    final token = await SecureStorage.getToken();
    if (token != null && token.isNotEmpty) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    handler.next(options);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    if (err.response?.statusCode == 401) {
      // Token expired or invalid — clear storage
      SecureStorage.clearAll();
    }
    handler.next(err);
  }
}

/// Helper class for standardized API error handling
class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final Map<String, dynamic>? errors;

  ApiException({required this.message, this.statusCode, this.errors});

  factory ApiException.fromDioError(DioException error) {
    final response = error.response;

    if (response != null) {
      final data = response.data;
      String message = 'Something went wrong';
      Map<String, dynamic>? errors;

      if (data is Map<String, dynamic>) {
        message = data['message'] as String? ?? message;
        errors = data['errors'] as Map<String, dynamic>?;
      }

      return ApiException(
        message: message,
        statusCode: response.statusCode,
        errors: errors,
      );
    }

    switch (error.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
        return ApiException(message: 'Connection timed out. Please try again.');
      case DioExceptionType.connectionError:
        return ApiException(message: 'No internet connection.');
      default:
        return ApiException(message: 'Something went wrong. Please try again.');
    }
  }

  @override
  String toString() => message;
}
