import 'package:flutter/foundation.dart';

import '../../data/auth_repository.dart';
import '../../domain/models/session_user.dart';

class AuthController extends ChangeNotifier {
  AuthController(this._authRepository);

  final AuthRepository _authRepository;

  SessionUser? user;
  String? token;
  bool loading = false;

  Future<void> login({
    required String email,
    required String password,
  }) async {
    loading = true;
    notifyListeners();

    try {
      final (nextToken, nextUser) = await _authRepository.login(
        email: email,
        password: password,
      );
      token = nextToken;
      user = nextUser;
    } finally {
      loading = false;
      notifyListeners();
    }
  }

  bool get isAuthenticated => token != null && user != null;
}
