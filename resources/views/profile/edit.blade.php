@extends('layouts.app')

@section('title', 'Configurações da conta · Projeto PAP')


@push('scripts')
    @vite('resources/js/pages/configuracoes.js')
@endpush

@section('content')
    <h1 class="font-heading text-3xl font-bold">Configurações da conta</h1>
@endsection
