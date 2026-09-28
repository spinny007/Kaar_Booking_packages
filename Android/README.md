# Kaar Booking Flutter app

One role-aware app hosts customer and driver features. Access and rotating refresh tokens are kept only in platform secure storage. Build with the API endpoint supplied at compile time:

```text
flutter run --dart-define=KAAR_API_URL=https://example.com/api/index.php/v1/kaar/
```

Do not commit Firebase service files, signing keys, endpoints, or credentials. Firebase packages are present for notification integration, but production initialization requires per-environment Firebase configuration and the backend push provider.
