<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('template.head')
    </head>
    <body class="login bg-primary">
        <div class="wrapper wrapper-login">
            <div class="container container-login animated fadeIn">
                <h3 class="text-center">Sign In</h3>
                <div class="login-form">
                    <div class="form-sub">
                        <div class="form-floating form-floating-custom mb-3">
                            <input
                                id="username"
                                name="username"
                                type="text"
                                class="form-control"
                                placeholder="username"
                                required
                                />
                            <label for="username">Username</label>
                        </div>
                        <div class="form-floating form-floating-custom mb-3">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-control"
                                placeholder="password"
                                required
                                />
                            <label for="password">Password</label>
                            
                            <div class="show-password">
                                <i class="icon-eye"></i>
                            </div>
                            <div class="set-password">
                                <i class="icon-lock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="d-flex form-sub">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="rememberme" />
                                <label class="form-check-label" for="rememberme">Remember Me</label>
                            </div>

                            <a href="#" class="link float-end">Forget Password ?</a>
                        </div>
                    </div>
                    <div class="form-action mb-3">
                        <a href="#" class="btn btn-primary w-100 btn-login">Sign In</a>
                    </div>
                    <div class="login-account">
                        <span class="msg">Don't have an account yet ?</span>
                        <a href="#" id="show-signup" class="link">Sign Up</a>
                    </div>
                </div>
            </div>

            <div class="container container-signup animated fadeIn">
                <h3 class="text-center">Sign Up</h3>
                <div class="login-form">
                    <div class="form-sub">
                        <div class="form-floating form-floating-custom mb-3">
                            <input
                                id="fullname"
                                name="fullname"
                                type="text"
                                class="form-control"
                                placeholder="fullname"
                                required
                                />
                            <label for="fullname">Fullname</label>
                        </div>
                        <div class="form-floating form-floating-custom mb-3">
                            <input
                                id="email"
                                name="email"
                                type="email"
                                class="form-control"
                                placeholder="email"
                                required
                                />
                            <label for="email">Email</label>
                        </div>
                        <div class="form-floating form-floating-custom mb-3">
                            <input
                                id="passwordsignin"
                                name="passwordsignin"
                                type="password"
                                class="form-control"
                                placeholder="passwordsignin"
                                required
                                />
                            <label for="passwordsignin">Password</label>
                            <div class="show-password">
                                <i class="icon-eye"></i>
                            </div>
                        </div>
                        <div class="form-floating form-floating-custom mb-3">
                            <input
                                id="confirmpassword"
                                name="confirmpassword"
                                type="password"
                                class="form-control"
                                placeholder="confirmpassword"
                                required
                                />
                            <label for="confirmpassword">Confirm Password</label>
                            <div class="show-password">
                                <i class="icon-eye"></i>
                            </div>
                        </div>
                    </div>
                    <div class="row form-sub m-0">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="agree" id="agree" />
                            <label class="form-check-label" for="agree"
                                   >I Agree the terms and conditions.</label
                            >
                        </div>
                    </div>
                    <div class="form-action">
                        <a href="#" id="show-signin" class="btn btn-danger btn-link btn-login me-3">Cancel</a>
                        <a href="#" class="btn btn-primary btn-login">Sign Up</a>
                    </div>
                </div>
            </div>
        </div>

        @include('template.script-login')
    </body>
</html>
