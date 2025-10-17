@extends('layouts.auth')
@section('title', 'Login')
@section('content')
    <div class="min-h-screen bg-gradient-to-br from-background to-muted flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-card rounded-2xl shadow-xl p-8 border border-border">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-primary rounded-2xl mb-4">
                    <span class="icon-dumbbell w-8 h-8 text-primary-foreground"></span>
                </div>
                <h1 class="text-2xl font-bold text-card-foreground mb-2">Really Sports Center</h1>
                <p class="text-muted-foreground">Sistem Manajemen Gym</p>
            </div>
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="bg-red-500/10 border border-red-500 text-red-600 text-sm p-3 rounded-lg">
                        {{ $errors->first() }}
                    </div>
                @endif
                <div>
                    @include('components.ui.input', [
                        'label' => 'Alamat Email',
                        'name' => 'email',
                        'type' => 'email',
                        'placeholder' => 'Masukkan Email',
                        'required' => true,
                    ])
                </div>

                <div class="relative">
                    @include('components.ui.input', [
                        'label' => 'Password',
                        'name' => 'password',
                        'type' => 'password',
                        'placeholder' => 'Masukkan password Anda',
                        'required' => true,
                    ])
                    <button type="button" class="absolute right-3 top-8 text-muted-foreground hover:text-foreground">
                        <span class="icon-eye w-5 h-5"></span>
                    </button>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" value="1" class="rounded border-border text-primary focus:ring-ring" />
                        <span class="ml-2 text-sm text-muted-foreground">Ingat saya</span>
                    </label>
                    <a href="#" class="text-sm text-primary hover:text-primary/80">Lupa password?</a>
                </div>

                @include('components.ui.button', [
                    'type' => 'submit',
                    'class' => 'w-full',
                    'slot' => 'Masuk',
                ])
            </form>
            <div class="mt-6 text-center">
                <p class="text-xs text-muted-foreground/60">© 2024 Really Sports Center. Hak cipta dilindungi.</p>
            </div>
        </div>
    </div>
@endsection
