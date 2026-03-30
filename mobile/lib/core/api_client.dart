import 'dart:convert';
import 'dart:io';

class ApiClient {
  ApiClient({
    this.baseUrl = const String.fromEnvironment(
      'API_BASE_URL',
      defaultValue: 'http://10.0.2.2:8000/api/v1',
    ),
  });

  final String baseUrl;
  String? _token;

  void setToken(String? token) {
    _token = token;
  }

  Future<Map<String, dynamic>> getJson(String path) async {
    final request = await _openUrl('GET', path);
    final response = await request.close();
    final payload = await utf8.decodeStream(response);
    return jsonDecode(payload) as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> postJson(String path, Map<String, dynamic> body) async {
    final request = await _openUrl('POST', path);
    request.headers.contentType = ContentType.json;
    request.write(jsonEncode(body));
    final response = await request.close();
    final payload = await utf8.decodeStream(response);
    return jsonDecode(payload) as Map<String, dynamic>;
  }

  Future<HttpClientRequest> _openUrl(String method, String path) async {
    final client = HttpClient();
    final request = await client.openUrl(method, Uri.parse('$baseUrl$path'));
    request.headers.set(HttpHeaders.acceptHeader, 'application/json');

    if (_token != null && _token!.isNotEmpty) {
      request.headers.set(HttpHeaders.authorizationHeader, 'Bearer $_token');
    }

    return request;
  }
}
