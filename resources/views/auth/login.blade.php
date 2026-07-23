<!DOCTYPE html>
<html lang="ar" dir="rtl" class="light-style customizer-hide">
@include('layouts.Frontend.Partials.head', ['pageTitle' => 'تسجيل الدخول | كتبي'])
<body>
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <div class="card">
                    <div class="card-body">
                        <div class="app-brand justify-content-center mb-4">
                            <a href="{{ route('login') }}" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo"><i class="bx bx-book-open text-primary fs-1"></i></span>
                                <span class="app-brand-text demo text-body fw-bolder">كتبي</span>
                            </a>
                        </div>

                        <h4 class="mb-2">مرحباً بك</h4>
                        <p class="mb-4">سجل الدخول للوصول إلى أرشيفك الشخصي.</p>

                        <form method="POST" action="{{ route('login.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">البريد الإلكتروني</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">كلمة المرور</label>
                                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3 form-check">
                                <input id="remember" type="checkbox" name="remember" value="1" class="form-check-input">
                                <label for="remember" class="form-check-label">تذكرني</label>
                            </div>
                            <button type="submit" class="btn btn-primary d-grid w-100">تسجيل الدخول</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.Frontend.Partials.scripts')
</body>
</html>
