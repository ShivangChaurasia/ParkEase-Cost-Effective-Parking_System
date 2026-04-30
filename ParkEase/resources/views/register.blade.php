@extends('layouts.app')
@section('title', 'Register')
@section('content')
<div class="container d-flex justify-content-center align-items-center mt-5" style="min-height: 60vh;">
    <div id="sign-up"></div>
</div>
@endsection

@push('scripts')
<script>
    function renderClerkComponent() {
        if (Clerk.user) {
            window.location.href = '/dashboard';
        } else {
            const signUpDiv = document.getElementById('sign-up');
            Clerk.mountSignUp(signUpDiv);
        }
    }
</script>
@endpush
