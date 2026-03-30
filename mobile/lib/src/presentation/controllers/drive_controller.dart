import 'package:flutter/foundation.dart';

import '../../data/drive_repository.dart';
import '../../domain/models/drive_item.dart';

class DriveController extends ChangeNotifier {
  DriveController(this._driveRepository);

  final DriveRepository _driveRepository;

  List<DriveItem> items = const [];
  bool loading = false;
  String scope = 'root';
  String? currentParentId;
  final List<({String? id, String name})> breadcrumb = [(id: null, name: 'Home')];

  Future<void> load({
    String nextScope = 'root',
    String? parentId,
  }) async {
    loading = true;
    scope = nextScope;
    currentParentId = nextScope == 'root' ? parentId : null;
    notifyListeners();

    try {
      items = await _driveRepository.listItems(
        scope: nextScope,
        parentId: nextScope == 'root' ? parentId : null,
      );
    } finally {
      loading = false;
      notifyListeners();
    }
  }

  Future<void> openFolder(DriveItem item) async {
    breadcrumb.add((id: item.id, name: item.name));
    await load(parentId: item.id);
  }

  Future<void> back() async {
    if (breadcrumb.length <= 1) {
      await load(parentId: null);
      return;
    }

    breadcrumb.removeLast();
    final previous = breadcrumb.last;
    await load(parentId: previous.id);
  }

  Future<void> changeScope(String nextScope) async {
    if (nextScope == 'root') {
      breadcrumb
        ..clear()
        ..add((id: null, name: 'Home'));
    }

    await load(nextScope: nextScope, parentId: nextScope == 'root' ? currentParentId : null);
  }

  Future<String> share(DriveItem item) {
    return _driveRepository.createPublicShare(item.id);
  }
}
