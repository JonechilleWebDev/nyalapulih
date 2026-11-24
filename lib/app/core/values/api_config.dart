class ApiConfig {
  ApiConfig._();

  // Untuk Flutter Web atau aplikasi desktop
  // yang berjalan pada komputer server.
  static const String baseUrl = 'http://127.0.0.1:8000';

  // Endpoint autentikasi
  static const String register =
      '$baseUrl/auth/register.php';

  static const String login =
      '$baseUrl/auth/login.php';

  static const String logout =
      '$baseUrl/auth/logout.php';

  static const String profile =
      '$baseUrl/user/profile.php';
}
