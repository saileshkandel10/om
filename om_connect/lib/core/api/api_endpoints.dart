class ApiEndpoints {
  // Auth
  static const String register = '/auth/register';
  static const String login = '/auth/login';
  static const String logout = '/auth/logout';
  static const String user = '/auth/user';

  // Profile
  static const String profile = '/profile';
  static const String profileAvatar = '/profile/avatar';

  // Products
  static const String products = '/products';
  static const String productCategories = '/products/categories';
  static const String illnessCategories = '/products/illness-categories';
  static String productDetail(int id) => '/products/$id';

  // Bills
  static const String bills = '/bills';
  static String billDetail(int id) => '/bills/$id';

  // Rewards
  static const String rewardWallet = '/rewards/wallet';
  static const String rewardTransactions = '/rewards/transactions';
  static const String rewardRedeem = '/rewards/redeem';
  static const String rewardRedemptions = '/rewards/redemptions';

  // Notifications
  static const String notifications = '/notifications';
  static const String notificationsUnreadCount = '/notifications/unread-count';
  static const String notificationsReadAll = '/notifications/read-all';
  static String notificationRead(int id) => '/notifications/$id/read';

  // Notices
  static const String notices = '/notices';
  static String noticeDetail(int id) => '/notices/$id';

  // Campaigns
  static const String campaigns = '/campaigns';
  static String campaignDetail(int id) => '/campaigns/$id';

  // Advertisements
  static const String advertisements = '/advertisements';
  static String advertisementImpression(int id) => '/advertisements/$id/impression';
  static String advertisementClick(int id) => '/advertisements/$id/click';

  // Shareholder
  static const String shareholderDashboard = '/shareholder/dashboard';
  static const String shareholderVerifyBoid = '/shareholder/verify-boid';
  static const String shareholderAccount = '/shareholder/account';

  // Pharmacy
  static const String pharmacyDashboard = '/pharmacy/dashboard';
  static const String pharmacyProfile = '/pharmacy/profile';
  static const String pharmacySourcingRecords = '/pharmacy/sourcing-records';
  static const String pharmacyQuizzes = '/pharmacy/quizzes';
  static const String pharmacyQuizSettings = '/pharmacy/quizzes/settings';
  static const String pharmacyQuizCategories = '/pharmacy/quizzes/categories';
  static const String pharmacyQuizMyAttempts = '/pharmacy/quizzes/my-attempts';
  static String pharmacyQuizDetail(int id) => '/pharmacy/quizzes/$id';
  static String pharmacyQuizStart(int id) => '/pharmacy/quizzes/$id/start';
  static String pharmacyQuizSubmit(int attemptId) => '/pharmacy/quizzes/attempts/$attemptId/submit';
  static String pharmacyQuizAttemptDetail(int attemptId) => '/pharmacy/quizzes/attempts/$attemptId';

  // Doctor
  static const String doctorDashboard = '/doctor/dashboard';
  static const String doctorPrescriptions = '/doctor/prescriptions';
  static String doctorPrescriptionDetail(int id) => '/doctor/prescriptions/$id';
}
