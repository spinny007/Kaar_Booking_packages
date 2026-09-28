import 'package:flutter/material.dart';
import '../../core/api/api_client.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key, required this.api, required this.onSignedIn});
  final ApiClient api;
  final VoidCallback onSignedIn;
  @override State<LoginScreen> createState() => _LoginScreenState();
}
class _LoginScreenState extends State<LoginScreen> {
  final email = TextEditingController(); final otp = TextEditingController(); bool sent=false; bool busy=false; String? error;
  Future<void> submit() async { setState(() { busy=true; error=null; }); try { if(!sent){ await widget.api.requestOtp(email.text.trim()); setState(()=>sent=true); } else { await widget.api.verifyOtp(email.text.trim(),otp.text.trim(),'Kaar Flutter'); widget.onSignedIn(); } } catch(e){ setState(()=>error=e.toString()); } finally { if(mounted)setState(()=>busy=false); } }
  @override Widget build(BuildContext context)=>Scaffold(appBar:AppBar(title:const Text('Kaar Booking')),body:SafeArea(child:ListView(padding:const EdgeInsets.all(24),children:[Text(sent?'Enter verification code':'Sign in with email',style:Theme.of(context).textTheme.headlineSmall),const SizedBox(height:16),TextField(controller:email,enabled:!sent,keyboardType:TextInputType.emailAddress,autofillHints:const[AutofillHints.email],decoration:const InputDecoration(labelText:'Email')),if(sent)TextField(controller:otp,keyboardType:TextInputType.number,autofillHints:const[AutofillHints.oneTimeCode],maxLength:6,decoration:const InputDecoration(labelText:'6-digit code')),if(error!=null)Text(error!,style:TextStyle(color:Theme.of(context).colorScheme.error)),const SizedBox(height:16),FilledButton(onPressed:busy?null:submit,child:Text(busy?'Please wait…':sent?'Verify':'Send code'))])));
}
