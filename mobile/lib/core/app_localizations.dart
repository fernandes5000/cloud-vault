import 'package:flutter/widgets.dart';

class AppLocalizations {
  AppLocalizations(this.locale);

  final Locale locale;

  static const supportedLocales = [
    Locale('en'),
    Locale('pt', 'BR'),
    Locale('es'),
  ];

  static const _strings = {
    'en': {
      'appTitle': 'CloudVault',
      'subtitle': 'Personal cloud with mobile-first backup and clean file access.',
      'login': 'Sign in',
      'logout': 'Sign out',
      'email': 'Email',
      'password': 'Password',
      'drive': 'Drive',
      'allFiles': 'All files',
      'recent': 'Recent',
      'favorites': 'Favorites',
      'empty': 'Nothing here yet.',
      'refresh': 'Refresh',
      'share': 'Share',
      'connect': 'Connect to your API',
      'backupReady': 'Background photo backup strategy is scaffolded for V1.',
      'folder': 'Folder',
      'file': 'File',
      'back': 'Back',
    },
    'pt_BR': {
      'appTitle': 'CloudVault',
      'subtitle': 'Cloud pessoal com backup mobile e acesso limpo aos arquivos.',
      'login': 'Entrar',
      'logout': 'Sair',
      'email': 'Email',
      'password': 'Senha',
      'drive': 'Arquivos',
      'allFiles': 'Todos os arquivos',
      'recent': 'Recentes',
      'favorites': 'Favoritos',
      'empty': 'Nada por aqui ainda.',
      'refresh': 'Atualizar',
      'share': 'Compartilhar',
      'connect': 'Conecte na sua API',
      'backupReady': 'A estrategia de backup em background ja esta estruturada para o V1.',
      'folder': 'Pasta',
      'file': 'Arquivo',
      'back': 'Voltar',
    },
    'es': {
      'appTitle': 'CloudVault',
      'subtitle': 'Nube personal con backup movil y acceso limpio a los archivos.',
      'login': 'Iniciar sesion',
      'logout': 'Cerrar sesion',
      'email': 'Correo',
      'password': 'Contrasena',
      'drive': 'Archivos',
      'allFiles': 'Todos los archivos',
      'recent': 'Recientes',
      'favorites': 'Favoritos',
      'empty': 'Todavia no hay nada aqui.',
      'refresh': 'Actualizar',
      'share': 'Compartir',
      'connect': 'Conecta con tu API',
      'backupReady': 'La estrategia de backup en segundo plano ya esta preparada para el V1.',
      'folder': 'Carpeta',
      'file': 'Archivo',
      'back': 'Volver',
    },
  };

  static AppLocalizations of(BuildContext context) {
    final localizations = Localizations.of<AppLocalizations>(context, AppLocalizations);
    assert(localizations != null, 'AppLocalizations not found in context');
    return localizations!;
  }

  static const delegate = _AppLocalizationsDelegate();

  String get appTitle => _value('appTitle');
  String get subtitle => _value('subtitle');
  String get login => _value('login');
  String get logout => _value('logout');
  String get email => _value('email');
  String get password => _value('password');
  String get drive => _value('drive');
  String get allFiles => _value('allFiles');
  String get recent => _value('recent');
  String get favorites => _value('favorites');
  String get empty => _value('empty');
  String get refresh => _value('refresh');
  String get share => _value('share');
  String get connect => _value('connect');
  String get backupReady => _value('backupReady');
  String get folder => _value('folder');
  String get file => _value('file');
  String get back => _value('back');

  String _value(String key) {
    final localeKey = locale.countryCode != null && locale.countryCode!.isNotEmpty
        ? '${locale.languageCode}_${locale.countryCode}'
        : locale.languageCode;

    return _strings[localeKey]?[key] ?? _strings[locale.languageCode]?[key] ?? _strings['en']![key]!;
  }
}

class _AppLocalizationsDelegate extends LocalizationsDelegate<AppLocalizations> {
  const _AppLocalizationsDelegate();

  @override
  bool isSupported(Locale locale) {
    return AppLocalizations.supportedLocales.any((supported) {
      return supported.languageCode == locale.languageCode;
    });
  }

  @override
  Future<AppLocalizations> load(Locale locale) async {
    return AppLocalizations(locale);
  }

  @override
  bool shouldReload(covariant LocalizationsDelegate<AppLocalizations> old) => false;
}
