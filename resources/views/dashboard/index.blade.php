@extends('layouts.app')

@section('title', 'Início · Projeto PAP')


@push('scripts')
    @vite('resources/js/pages/inicio.js')
@endpush

@section('content')
    <h1 class="font-heading text-3xl font-bold">Início</h1>
@endsection
