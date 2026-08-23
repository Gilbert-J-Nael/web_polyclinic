<div class="auth-wrapper d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="card shadow border-0 rounded-4" style="width:100%; max-width:420px;">

        <div class="card-body p-4">

            <div class="text-center mb-4">
                <h2 class="fw-bold mb-1">Register</h2>

                <p class="text-muted mb-0">
                    Create your account
                </p>
            </div>

            {{-- Alert Success --}}
            @if(session('succ_msg'))
                <div class="alert alert-success">
                    {{ session('succ_msg') }}
                </div>
            @endif

            {{-- Alert Error --}}
            @if(session('resp_msg'))
                <div class="alert alert-danger">
                    @if(is_object(session('resp_msg')))
                        {{ session('resp_msg')->errors()->first() }}
                    @else
                        {{ session('resp_msg') }}
                    @endif
                </div>
            @endif

            <form action="{{ url('/register/store') }}" method="POST">
                @csrf

                {{-- Email --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email-input"
                        class="form-control rounded-3"
                        placeholder="Enter your email"
                        required
                    >
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password-input"
                        class="form-control rounded-3"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                {{-- Button --}}
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary rounded-3 py-2">
                        Register
                    </button>
                </div>

                <div class="text-center">
                    <small class="text-muted">
                        Already have an account?
                        <a href="{{ url('/login') }}" class="text-decoration-none">
                            Login
                        </a>
                    </small>
                </div>

            </form>

        </div>
    </div>
</div>