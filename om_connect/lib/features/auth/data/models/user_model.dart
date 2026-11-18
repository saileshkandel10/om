import 'dart:convert';

/// User model matching the Laravel UserProfileResource
class User {
  final int id;
  final String name;
  final String email;
  final String? avatar;
  final String? mobile;
  final String? status;
  final List<String> roles;
  final RewardWallet? rewardWallet;
  final ShareholderAccount? shareholderAccount;

  User({
    required this.id,
    required this.name,
    required this.email,
    this.avatar,
    this.mobile,
    this.status,
    this.roles = const [],
    this.rewardWallet,
    this.shareholderAccount,
  });

  bool get isShareholder => roles.contains('shareholder');
  bool get isPharmacy => roles.contains('pharmacy');
  bool get isDoctor => roles.contains('doctor');
  bool get isAdmin => roles.contains('admin');

  String get primaryRole {
    if (isShareholder) return 'shareholder';
    if (isPharmacy) return 'pharmacy';
    if (isDoctor) return 'doctor';
    if (isAdmin) return 'admin';
    return 'user';
  }

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as int,
      name: json['name'] as String,
      email: json['email'] as String,
      avatar: json['avatar'] as String?,
      mobile: json['mobile'] as String?,
      status: json['status'] as String?,
      roles: (json['roles'] as List<dynamic>?)
              ?.map((e) => e.toString())
              .toList() ??
          [],
      rewardWallet: json['reward_wallet'] != null
          ? RewardWallet.fromJson(json['reward_wallet'] as Map<String, dynamic>)
          : null,
      shareholderAccount: json['shareholder_account'] != null
          ? ShareholderAccount.fromJson(
              json['shareholder_account'] as Map<String, dynamic>)
          : null,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'email': email,
        'avatar': avatar,
        'mobile': mobile,
        'status': status,
        'roles': roles,
      };

  String toJsonString() => jsonEncode(toJson());

  factory User.fromJsonString(String jsonString) =>
      User.fromJson(jsonDecode(jsonString) as Map<String, dynamic>);
}

class RewardWallet {
  final String totalPoints;
  final String totalBonus;
  final String totalRoyalty;
  final String totalValue;

  RewardWallet({
    required this.totalPoints,
    required this.totalBonus,
    required this.totalRoyalty,
    required this.totalValue,
  });

  factory RewardWallet.fromJson(Map<String, dynamic> json) {
    return RewardWallet(
      totalPoints: json['total_points']?.toString() ?? '0.00',
      totalBonus: json['total_bonus']?.toString() ?? '0.00',
      totalRoyalty: json['total_royalty']?.toString() ?? '0.00',
      totalValue: json['total_value']?.toString() ?? '0.00',
    );
  }
}

class ShareholderAccount {
  final int id;
  final String? boid;
  final String? sharesOwned;
  final String? verificationStatus;
  final String? verifiedAt;

  ShareholderAccount({
    required this.id,
    this.boid,
    this.sharesOwned,
    this.verificationStatus,
    this.verifiedAt,
  });

  bool get isVerified => verificationStatus == 'verified';

  factory ShareholderAccount.fromJson(Map<String, dynamic> json) {
    return ShareholderAccount(
      id: json['id'] as int,
      boid: json['boid'] as String?,
      sharesOwned: json['shares_owned']?.toString(),
      verificationStatus: json['verification_status'] as String?,
      verifiedAt: json['verified_at'] as String?,
    );
  }
}

class AuthResponse {
  final User user;
  final String token;
  final String? message;

  AuthResponse({required this.user, required this.token, this.message});

  factory AuthResponse.fromJson(Map<String, dynamic> json) {
    // Handle both 'data' wrapped and unwrapped user responses
    final userData = json['user'] is Map<String, dynamic>
        ? json['user'] as Map<String, dynamic>
        : (json['user'] as Map<String, dynamic>?)?['data'] as Map<String, dynamic>? ?? json['user'] as Map<String, dynamic>;

    return AuthResponse(
      user: User.fromJson(userData),
      token: json['token'] as String,
      message: json['message'] as String?,
    );
  }
}
