@extends('layouts.app')

@php($tituloModulo = ['computadores' => 'computador', 'impresoras' => 'impresora', 'camaras' => 'cámara'][$modulo])
@php($articuloModulo = $modulo === 'impresoras' || $modulo === 'camaras' ? 'nueva' : 'nuevo')

@section('title', 'Nuevo ' . $tituloModulo)
@section('page_title', 'Registrar ' . $articuloModulo . ' ' . $tituloModulo)

@section('content')
@include('hardware._form')
@endsection
