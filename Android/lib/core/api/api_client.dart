import 'dart:convert';
import 'package:http/http.dart' as http;
import '../auth/token_store.dart';

class ApiException implements Exception {
  const ApiException(this.statusCode, this.message);
  final int statusCode;
  final String message;
  @override String toString() => message;
}

class ApiClient {
  ApiClient({required this.baseUri, required TokenStore tokens, http.Client? httpClient}) : _tokens = tokens, _http = httpClient ?? http.Client();
  final Uri baseUri;
  final TokenStore _tokens;
  final http.Client _http;
  Future<void>? _refreshing;

  Uri _uri(String path) => baseUri.resolve(path.replaceFirst(RegExp(r'^/'), ''));

  Future<Map<String, dynamic>> post(String path, Map<String, dynamic> body, {bool authenticated = true}) async {
    var response = await _send('POST', path, body, authenticated: authenticated);
    if (authenticated && response.statusCode == 401) {
      await (_refreshing ??= _refresh().whenComplete(() => _refreshing = null));
      response = await _send('POST', path, body, authenticated: true);
    }
    return _decode(response);
  }

  Future<Map<String, dynamic>> get(String path, {bool authenticated = true}) async {
    var response = await _send('GET', path, null, authenticated: authenticated);
    if (authenticated && response.statusCode == 401) {
      await (_refreshing ??= _refresh().whenComplete(() => _refreshing = null));
      response = await _send('GET', path, null, authenticated: true);
    }
    return _decode(response);
  }

  Future<http.Response> _send(String method, String path, Map<String, dynamic>? body, {required bool authenticated}) async {
    final headers = <String, String>{'Accept': 'application/json', 'Content-Type': 'application/json'};
    if (authenticated) {
      final pair = await _tokens.read();
      if (pair == null) throw const ApiException(401, 'Sign in required.');
      headers['Authorization'] = 'Bearer ${pair.accessToken}';
    }
    return _http.send(http.Request(method, _uri(path))..headers.addAll(headers)..body = body == null ? '' : jsonEncode(body)).then(http.Response.fromStream);
  }

  Map<String, dynamic> _decode(http.Response response) {
    final decoded = response.body.isEmpty ? <String, dynamic>{} : jsonDecode(response.body) as Map<String, dynamic>;
    if (response.statusCode < 200 || response.statusCode >= 300) throw ApiException(response.statusCode, (decoded['message'] ?? decoded['error'] ?? 'Request failed') as String);
    return decoded;
  }

  Future<void> requestOtp(String email) => post('auth/challenge', {'email': email}, authenticated: false).then((_) {});
  Future<void> verifyOtp(String email, String otp, String deviceName) async {
    final response = await post('auth/verify', {'email': email, 'otp': otp, 'deviceName': deviceName}, authenticated: false);
    final data = response['data'] as Map<String, dynamic>;
    await _tokens.write(TokenPair(accessToken: data['accessToken'] as String, refreshToken: data['refreshToken'] as String, expiresAt: DateTime.parse(data['accessExpiresAt'] as String)));
  }
  Future<void> _refresh() async {
    final pair = await _tokens.read(); if (pair == null) throw const ApiException(401, 'Sign in required.');
    try { final response = await post('auth/refresh', {'refreshToken': pair.refreshToken, 'deviceName': 'Kaar Flutter'}, authenticated: false); final data = response['data'] as Map<String, dynamic>; await _tokens.write(TokenPair(accessToken: data['accessToken'] as String, refreshToken: data['refreshToken'] as String, expiresAt: DateTime.parse(data['accessExpiresAt'] as String))); } catch (_) { await _tokens.clear(); rethrow; }
  }
  Future<void> logout() async { try { await post('auth/logout', {}); } finally { await _tokens.clear(); } }
}
