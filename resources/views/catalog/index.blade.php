@extends('layouts.app')

@section('content')
<div class="px-6 py-6">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold">Katalog Alat</h1>

        <input
            type="text"
            placeholder="Cari kamera, drone, lighting..."
            class="w-80 px-4 py-2 rounded-xl border focus:ring-2 focus:ring-blue-500 outline-none"
        >
    </div>
