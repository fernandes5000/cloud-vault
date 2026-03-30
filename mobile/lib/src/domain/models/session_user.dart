class SessionUser {
  const SessionUser({
    required this.id,
    required this.name,
    required this.email,
    required this.preferredLocale,
    required this.usedBytes,
    required this.quotaBytes,
  });

  final int id;
  final String name;
  final String email;
  final String preferredLocale;
  final int usedBytes;
  final int quotaBytes;

  factory SessionUser.fromJson(Map<String, dynamic> json) {
    final storage = json['storage'] as Map<String, dynamic>? ?? const {};

    return SessionUser(
      id: json['id'] as int,
      name: json['name'] as String,
      email: json['email'] as String,
      preferredLocale: json['preferredLocale'] as String? ?? 'en',
      usedBytes: storage['usedBytes'] as int? ?? 0,
      quotaBytes: storage['quotaBytes'] as int? ?? 1,
    );
  }
}
