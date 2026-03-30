import '../../core/api_client.dart';
import '../domain/models/drive_item.dart';

class DriveRepository {
  DriveRepository(this._apiClient);

  final ApiClient _apiClient;

  Future<List<DriveItem>> listItems({
    String scope = 'root',
    String? parentId,
  }) async {
    final query = StringBuffer('/drive?scope=$scope');

    if (parentId != null) {
      query.write('&parent_id=$parentId');
    }

    final payload = await _apiClient.getJson(query.toString());
    final items = payload['data'] as List<dynamic>? ?? const [];

    return items.map((item) => DriveItem.fromJson(item as Map<String, dynamic>)).toList();
  }

  Future<String> createPublicShare(String driveItemId) async {
    final payload = await _apiClient.postJson('/shares', {
      'drive_item_id': driveItemId,
      'visibility': 'public',
      'permission': 'download',
    });

    final share = payload['data'] as Map<String, dynamic>;
    return share['publicUrl'] as String;
  }
}
