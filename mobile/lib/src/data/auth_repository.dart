import '../../core/api_client.dart';
import '../domain/models/session_user.dart';

class AuthRepository {
  AuthRepository(this._apiClient);

  final ApiClient _apiClient;

  Future<(String, SessionUser)> login({
    required String email,
    required String password,
  }) async {
    final payload = await _apiClient.postJson('/auth/login', {
      'email': email,
      'password': password,
      'device_name': 'flutter-mobile',
    });

    final token = payload['token'] as String;
    final user = SessionUser.fromJson(payload['user'] as Map<String, dynamic>);
    _apiClient.setToken(token);

    return (token, user);
  }
}
