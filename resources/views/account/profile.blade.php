@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
        
        <div class="w-full md:w-64 shrink-0">
            @include('account.partials.sidebar', ['active' => 'profile'])
        </div>

        <div class="flex-grow bg-dark-card border border-border-gold rounded-2xl shadow-sm p-6 md:p-8">
            <div class="mb-6">
                <h1 class="text-3xl font-extrabold text-text-light tracking-tight">Profile Details</h1>
                <p class="text-text-muted mt-1">Manage your contact information and account security password.</p>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-xl text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl text-sm font-semibold">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('account.profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Personal Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-text-light mb-4 pb-2 border-b border-border-gold/30">Personal Info</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Full Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                        <div>
                            <label for="phone" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Phone Number</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label for="email" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Email Address *</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                    </div>
                </div>

                <!-- Password Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-text-light mb-2 pb-2 border-b border-border-gold/30">Change Password</h3>
                    <p class="text-text-muted text-xs mb-4">Leave fields blank if you do not wish to update your password.</p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <label for="current_password" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Current Password</label>
                            <input type="password" name="current_password" id="current_password" class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                        <div>
                            <label for="new_password" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">New Password</label>
                            <input type="password" name="new_password" id="new_password" class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                        <div>
                            <label for="new_password_confirmation" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-border-gold/30">
                    <button type="submit" class="btn btn-gold">
                        Update Account
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
