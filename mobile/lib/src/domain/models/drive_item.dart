class DriveItem {
  const DriveItem({
    required this.id,
    required this.name,
    required this.type,
    required this.mimeType,
    required this.isFavorite,
  });

  final String id;
  final String name;
  final String type;
  final String? mimeType;
  final bool isFavorite;

  bool get isFolder => type == 'folder';

  factory DriveItem.fromJson(Map<String, dynamic> json) {
    return DriveItem(
      id: json['id'] as String,
      name: json['name'] as String,
      type: json['type'] as String,
      mimeType: json['mimeType'] as String?,
      isFavorite: json['isFavorite'] as bool? ?? false,
    );
  }
}
