@extends('layout')
    <section class="container">
        <br>
        <form action="{{route('medicament.update',$medicament)}}" method="post">
            @method('put')
            @csrf
            <x-component.input type='text' name='nom' value='{{$medicament->nom}}' label='Nom Medicament :'/>
            <x-component.input type='decimal' name='prix' value='{{$medicament->prix}}' label='Prix Unitaire : '/>
            <x-component.input type='number' name='stock' value='{{$medicament->stock}}' label='Quantité : '/>
            <x-component.input type='date' name='date_expiration' value="{{ $medicament->date_expiration->format('Y-m-d')}}" label='Date Expiration : '/>
            <x-component.input type='text' name='description' value='{{$medicament->description}}' label='Description :'/>
            <hr>
            <button class="btn btn-outline-warning">MODIFIER</button>
        </form>
    </section>

