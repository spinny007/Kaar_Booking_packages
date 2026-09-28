import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'core/api/api_client.dart';
import 'core/auth/token_store.dart';
import 'features/auth/login_screen.dart';
import 'features/home/home_screen.dart';

void main() { WidgetsFlutterBinding.ensureInitialized(); runApp(const KaarApp()); }
class KaarApp extends StatefulWidget { const KaarApp({super.key}); @override State<KaarApp> createState()=>_KaarAppState(); }
class _KaarAppState extends State<KaarApp>{late final TokenStore tokens;late final ApiClient api;bool? signedIn;@override void initState(){super.initState();tokens=const TokenStore(FlutterSecureStorage(aOptions:AndroidOptions(encryptedSharedPreferences:true)));api=ApiClient(baseUri:Uri.parse(const String.fromEnvironment('KAAR_API_URL',defaultValue:'https://example.invalid/api/index.php/v1/kaar/')),tokens:tokens);tokens.read().then((value){if(mounted)setState(()=>signedIn=value!=null);});}@override Widget build(BuildContext context)=>MaterialApp(title:'Kaar Booking',theme:ThemeData(colorScheme:ColorScheme.fromSeed(seedColor:const Color(0xff1268e8)),useMaterial3:true),home:signedIn==null?const Scaffold(body:Center(child:CircularProgressIndicator())):signedIn!?HomeScreen(api:api,onSignedOut:()=>setState(()=>signedIn=false)):LoginScreen(api:api,onSignedIn:()=>setState(()=>signedIn=true)));}
