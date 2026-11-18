import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AppConfig {
  static const String appName = 'OM Connect';
  static String? _customBaseUrl;

  static const String hostWifiIp = '192.168.1.174';
  static const String defaultPort = '8000';

  static List<String> get candidateUrls {
    return [
      if (_customBaseUrl != null) _customBaseUrl!,
      'https://saileshkandel.com.np/api/v1',
      'http://192.168.1.174:8000/api/v1',
      'http://192.168.18.202:8000/api/v1',
      'http://10.0.2.2:8000/api/v1',
      'http://127.0.0.1:8000/api/v1',
      'https://om.lims.com.np/api/v1',
    ];
  }

  static String get baseUrl {
    if (_customBaseUrl != null && _customBaseUrl!.isNotEmpty) {
      return _customBaseUrl!;
    }
    const envUrl = String.fromEnvironment('API_URL', defaultValue: '');
    if (envUrl.isNotEmpty) return envUrl;

    if (kIsWeb) {
      return 'https://saileshkandel.com.np/api/v1';
    }

    return 'https://saileshkandel.com.np/api/v1';
  }

  static void setCustomBaseUrl(String url) {
    _customBaseUrl = url;
  }

  static Future<void> loadSavedBaseUrl() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final saved = prefs.getString('api_base_url');
      if (saved != null && saved.isNotEmpty) {
        _customBaseUrl = saved;
      }
    } catch (_) {}
  }

  static Future<void> saveBaseUrl(String url) async {
    _customBaseUrl = url;
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('api_base_url', url);
    } catch (_) {}
  }

  /// Automatically probe candidate server URLs and return the first responding one
  static Future<String?> autoDetectServer() async {
    final dio = Dio(BaseOptions(
      connectTimeout: const Duration(milliseconds: 2000),
      receiveTimeout: const Duration(milliseconds: 2000),
    ));

    for (final url in candidateUrls) {
      try {
        final response = await dio.get('$url/products');
        if (response.statusCode == 200) {
          await saveBaseUrl(url);
          return url;
        }
      } catch (_) {}
    }
    return null;
  }

  static const int connectTimeout = 15000;
  static const int receiveTimeout = 15000;
  static const int maxUploadSize = 5 * 1024 * 1024; // 5MB
}
