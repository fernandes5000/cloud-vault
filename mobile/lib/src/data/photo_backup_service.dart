class PhotoBackupService {
  const PhotoBackupService();

  String get strategySummary =>
      'Queue photos locally, upload in chunks on Wi-Fi or charger, and retry with backoff when connectivity returns.';
}
