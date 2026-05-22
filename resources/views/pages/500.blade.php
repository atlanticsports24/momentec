@extends('layouts.minimal')

@section('title', 'Server Error')
@section('meta_description', 'Something went wrong. Please try again later.')

@section('content')
<div class="mx-auto max-w-[1280px] px-4 py-20 text-center sm:px-6 lg:px-8">
    <p class="text-8xl font-bold text-red-200">500</p>
    <h1 class="mt-4 text-3xl font-bold text-primary">Something went wrong</h1>
    <p class="mx-auto mt-4 max-w-md text-gray-600">
        We&rsquo;re working to fix the issue. Please try again in a few moments.
    </p>
    @if(config('site.contact_email') || config('site.contact_phone'))
        <div class="mx-auto mt-8 max-w-sm rounded-xl border border-gray-200 bg-gray-50 p-6 text-sm text-gray-600">
            <p class="font-semibold text-primary">Need help?</p>
            @if(config('site.contact_email'))
                <p class="mt-2">
                    Email:
                    <a href="mailto:{{ config('site.contact_email') }}" class="font-medium text-accent hover:underline">
                        {{ config('site.contact_email') }}
                    </a>
                </p>
            @endif
            @if(config('site.contact_phone'))
                <p class="mt-1">
                    Phone:
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', config('site.contact_phone')) }}" class="font-medium text-accent hover:underline">
                        {{ config('site.contact_phone') }}
                    </a>
                </p>
            @endif
        </div>
    @endif
    <a href="{{ route('home') }}" class="mt-8 inline-flex rounded-lg bg-accent px-6 py-3 font-semibold text-white hover:bg-accent-dark">
        Back to Home
    </a>
</div>
@endsection
