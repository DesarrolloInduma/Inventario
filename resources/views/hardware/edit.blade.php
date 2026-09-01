@extends('layouts.app')

@section('title', 'Editar equipo')
@section('page_title', 'Editar equipo: ' . $hardware->Hw_Serial)

@section('content')
@include('hardware._form')
@endsection
