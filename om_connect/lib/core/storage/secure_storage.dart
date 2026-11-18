import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

class SecureStorage {
  static const FlutterSecureStorage _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(
      encryptedSharedPreferences: true,
      resetOnError: true,
    ),
  );

  static const _tokenKey = 'auth_token';
  static const _userKey = 'user_data';

  static Future<void> saveToken(String token) async {
    try {
      await _storage.write(key: _tokenKey, value: token);
    } catch (_) {
      try {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(_tokenKey, token);
      } catch (_) {}
    }
  }

  static Future<String?> getToken() async {
    try {
      final token = await _storage.read(key: _tokenKey);
      if (token != null && token.isNotEmpty) return token;
    } catch (_) {}

    try {
      final prefs = await SharedPreferences.getInstance();
      return prefs.getString(_tokenKey);
    } catch (_) {
      return null;
    }
  }

  static Future<void> saveUserData(String userData) async {
    try {
      await _storage.write(key: _userKey, value: userData);
    } catch (_) {
      try {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(_userKey, userData);
      } catch (_) {}
    }
  }

  static Future<String?> getUserData() async {
    try {
      final data = await _storage.read(key: _userKey);
      if (data != null && data.isNotEmpty) return data;
    } catch (_) {}

    try {
      final prefs = await SharedPreferences.getInstance();
      return prefs.getString(_userKey);
    } catch (_) {
      return null;
    }
  }

  static Future<void> clearAll() async {
    try {
      await _storage.deleteAll();
    } catch (_) {}

    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_tokenKey);
      await prefs.remove(_userKey);
    } catch (_) {}
  }

  static Future<bool> hasToken() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }
}
