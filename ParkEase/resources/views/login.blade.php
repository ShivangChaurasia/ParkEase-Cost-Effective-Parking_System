@extends('layouts.app')
@section('title', 'Login')
@section('content')
<div class="container d-flex justify-content-center align-items-center mt-5" style="min-height: 60vh;">
    <div id="sign-in"></div>
</div>
@endsection

@push('scripts')
<script>
    function renderClerkComponent() {
        if (Clerk.user) {
            window.location.href = '/dashboard';
        } else {
            const signInDiv = document.getElementById('sign-in');
            Clerk.mountSignIn(signInDiv);
        }
    }
</script>
@endpush
