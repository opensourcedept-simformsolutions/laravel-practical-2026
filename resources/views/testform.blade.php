@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="container">  
<x-form.form :action="route('login')" method="POST" class="form-contained">
        <x-form.form-section title="Login">
            <x-form.fieldset legend="Account Access">
                <x-form.field name="email" label="Email" required>
                    <x-form.input name="email" id="email" type="email" :error="$errors->has('email')"
                        placeholder="name@example.com" />
                        {{ $errors->first('email') }}
                    </x-form.error name="email" />
                </x-form.field>


                <x-form.field name="password" label="Password" required>
                    <x-form.input name="password" id="password" type="password" :error="$errors->has('password')"
                        placeholder="Enter your password" />
                </x-form.field>

                <x-form.checkbox name="remember" id="remember" :checked="old('remember')">
                    Remember me
                </x-form.checkbox>

                <div class="d-flex justify-content-end">
                    <x-form.submit-button>
                        Log In
                    </x-form.submit-button>
                </div>
            </x-form.fieldset>
        </x-form.form-section>
    </x-form.form>

@endsection
