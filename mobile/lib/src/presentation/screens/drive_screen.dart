import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/app_localizations.dart';
import '../../data/photo_backup_service.dart';
import '../../domain/models/drive_item.dart';
import '../controllers/auth_controller.dart';
import '../controllers/drive_controller.dart';

class DriveScreen extends StatefulWidget {
  const DriveScreen({
    super.key,
    required this.authController,
    required this.driveController,
    required this.photoBackupService,
  });

  final AuthController authController;
  final DriveController driveController;
  final PhotoBackupService photoBackupService;

  @override
  State<DriveScreen> createState() => _DriveScreenState();
}

class _DriveScreenState extends State<DriveScreen> {
  @override
  void initState() {
    super.initState();
    if (widget.driveController.items.isEmpty) {
      widget.driveController.load();
    }
  }

  Future<void> _shareItem(DriveItem item) async {
    final url = await widget.driveController.share(item);
    await Clipboard.setData(ClipboardData(text: url));

    if (!mounted) {
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(url)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final strings = AppLocalizations.of(context);

    return AnimatedBuilder(
      animation: widget.driveController,
      builder: (context, _) {
        final user = widget.authController.user;
        final usagePercent = user == null ? 0 : ((user.usedBytes / user.quotaBytes) * 100).round();

        return Scaffold(
          appBar: AppBar(
            title: Text(strings.drive),
            actions: [
              IconButton(
                tooltip: strings.refresh,
                onPressed: () => widget.driveController.load(
                  nextScope: widget.driveController.scope,
                  parentId: widget.driveController.currentParentId,
                ),
                icon: const Icon(Icons.refresh),
              ),
            ],
          ),
          body: ListView(
            padding: const EdgeInsets.all(20),
            children: [
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(28),
                  gradient: const LinearGradient(
                    colors: [Color(0xFF0F766E), Color(0xFF0F172A)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(strings.appTitle, style: Theme.of(context).textTheme.titleSmall?.copyWith(color: Colors.white70)),
                    const SizedBox(height: 8),
                    Text(user?.name ?? strings.drive, style: Theme.of(context).textTheme.headlineSmall?.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
                    const SizedBox(height: 12),
                    LinearProgressIndicator(
                      value: usagePercent / 100,
                      minHeight: 10,
                      borderRadius: BorderRadius.circular(999),
                    ),
                    const SizedBox(height: 8),
                    Text('$usagePercent% used', style: const TextStyle(color: Colors.white70)),
                  ],
                ),
              ),
              const SizedBox(height: 20),
              Wrap(
                spacing: 10,
                runSpacing: 10,
                children: [
                  ChoiceChip(
                    label: Text(strings.allFiles),
                    selected: widget.driveController.scope == 'root',
                    onSelected: (_) => widget.driveController.changeScope('root'),
                  ),
                  ChoiceChip(
                    label: Text(strings.recent),
                    selected: widget.driveController.scope == 'recent',
                    onSelected: (_) => widget.driveController.changeScope('recent'),
                  ),
                  ChoiceChip(
                    label: Text(strings.favorites),
                    selected: widget.driveController.scope == 'favorites',
                    onSelected: (_) => widget.driveController.changeScope('favorites'),
                  ),
                ],
              ),
              const SizedBox(height: 20),
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(28),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(strings.backupReady, style: Theme.of(context).textTheme.bodyMedium),
                    const SizedBox(height: 6),
                    Text(widget.photoBackupService.strategySummary),
                  ],
                ),
              ),
              if (widget.driveController.breadcrumb.length > 1)
                Padding(
                  padding: const EdgeInsets.only(top: 20),
                  child: OutlinedButton.icon(
                    onPressed: widget.driveController.back,
                    icon: const Icon(Icons.arrow_back),
                    label: Text(strings.back),
                  ),
                ),
              const SizedBox(height: 20),
              if (widget.driveController.loading)
                const Center(
                  child: Padding(
                    padding: EdgeInsets.all(24),
                    child: CircularProgressIndicator(),
                  ),
                )
              else if (widget.driveController.items.isEmpty)
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 40),
                  child: Center(child: Text(strings.empty)),
                )
              else
                ...widget.driveController.items.map((item) {
                  return Card(
                    elevation: 0,
                    margin: const EdgeInsets.only(bottom: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
                    child: ListTile(
                      contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
                      leading: CircleAvatar(
                        backgroundColor: item.isFolder ? const Color(0xFFFDE68A) : const Color(0xFFCCFBF1),
                        child: Icon(item.isFolder ? Icons.folder_rounded : Icons.description_rounded),
                      ),
                      title: Text(item.name),
                      subtitle: Text(item.isFolder ? strings.folder : '${strings.file} · ${item.mimeType ?? ''}'),
                      trailing: item.isFolder
                          ? const Icon(Icons.chevron_right_rounded)
                          : IconButton(
                              icon: const Icon(Icons.share_rounded),
                              onPressed: () => _shareItem(item),
                            ),
                      onTap: item.isFolder ? () => widget.driveController.openFolder(item) : null,
                    ),
                  );
                }),
            ],
          ),
        );
      },
    );
  }
}
