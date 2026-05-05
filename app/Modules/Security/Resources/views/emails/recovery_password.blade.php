@extends('layouts.email')

@section('content')

{{__('Dear')}}: <b>{{$user['full_name']}}</b>, 
<br/>
<br/>
{{__('We have received a request to reset your account password. If you did not make this request, you can ignore this email.')}}
<br/>
<br/>
{{__('To reset your password, please click the following link')}}: <a href="{{route("recovery", $code)}}">{{__('Reset my password.')}}</a>
<br/>
<br/>
{{__('This link will be available for 5 minutes. If you do not use it within this time, you will need to request a new password reset.')}}
<br/>
<br/>
<br/>
<br/>



CNAC





@endsection