@extends('layouts.app')

@section('title', 'Pacientes · Projeto PAP')
@section('cargos', 'secretario,admin')

@push('scripts')
    @vite('resources/js/pages/pacientes.js')
@endpush

@section('content')
    <h1 class="font-heading text-3xl font-bold">Pacientes</h1>
@endsection
