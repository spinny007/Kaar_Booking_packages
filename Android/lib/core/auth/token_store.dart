import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class TokenPair {
  const TokenPair({required this.accessToken, required this.refreshToken, required this.expiresAt});
  final String accessToken;
  final String refreshToken;
  final DateTime expiresAt;
}

class TokenStore {
  const TokenStore(this._storage);
  final FlutterSecureStorage _storage;
  static const _access = 'kaar.access';
  static const _refresh = 'kaar.refresh';
  static const _expiry = 'kaar.expiry';

  Future<TokenPair?> read() async {
    final values = await Future.wait([_storage.read(key: _access), _storage.read(key: _refresh), _storage.read(key: _expiry)]);
    final expiry = DateTime.tryParse(values[2] ?? '');
    if (values[0] == null || values[1] == null || expiry == null) return null;
    return TokenPair(accessToken: values[0]!, refreshToken: values[1]!, expiresAt: expiry);
  }

  Future<void> write(TokenPair pair) async {
    await _storage.write(key: _access, value: pair.accessToken);
    await _storage.write(key: _refresh, value: pair.refreshToken);
    await _storage.write(key: _expiry, value: pair.expiresAt.toUtc().toIso8601String());
  }

  Future<void> clear() async {
    await Future.wait([_storage.delete(key: _access), _storage.delete(key: _refresh), _storage.delete(key: _expiry)]);
  }
}
