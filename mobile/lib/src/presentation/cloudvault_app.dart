import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';

import '../../core/api_client.dart';
import '../../core/app_localizations.dart';
import '../data/auth_repository.dart';
import '../data/drive_repository.dart';
import '../data/photo_backup_service.dart';
import 'controllers/auth_controller.dart';
import 'controllers/drive_controller.dart';
import 'screens/drive_screen.dart';
import 'screens/login_screen.dart';

class CloudVaultApp extends StatefulWidget {
  const CloudVaultApp({super.key});

  @override
  State<CloudVaultApp> createState() => _CloudVaultAppState();
}

class _CloudVaultAppState extends State<CloudVaultApp> {
  late final ApiClient apiClient;
  late final AuthController authController;
  late final DriveController driveController;
  late final PhotoBackupService photoBackupService;

  @override
  void initState() {
    super.initState();
    apiClient = ApiClient();
    authController = AuthController(AuthRepository(apiClient));
    driveController = DriveController(DriveRepository(apiClient));
    photoBackupService = const PhotoBackupService();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: authController,
      builder: (context, _) {
        final locale = _resolveLocale(authController.user?.preferredLocale);

        return MaterialApp(
          title: 'CloudVault',
          debugShowCheckedModeBanner: false,
          locale: locale,
          supportedLocales: AppLocalizations.supportedLocales,
          localizationsDelegates: const [
            AppLocalizations.delegate,
            GlobalMaterialLocalizations.delegate,
            GlobalWidgetsLocalizations.delegate,
            GlobalCupertinoLocalizations.delegate,
          ],
          theme: ThemeData(
            useMaterial3: true,
            colorScheme: ColorScheme.fromSeed(
              seedColor: const Color(0xFF0F766E),
              brightness: Brightness.light,
            ),
            scaffoldBackgroundColor: const Color(0xFFF5F7F4),
          ),
          home: authController.isAuthenticated
              ? DriveScreen(
                  authController: authController,
                  driveController: driveController,
                  photoBackupService: photoBackupService,
                )
              : LoginScreen(
                  authController: authController,
                  driveController: driveController,
                ),
        );
      },
    );
  }

  Locale _resolveLocale(String? preferredLocale) {
    return switch (preferredLocale) {
      'pt_BR' => const Locale('pt', 'BR'),
      'es' => const Locale('es'),
      _ => const Locale('en'),
    };
  }
}
