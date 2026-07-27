@extends('layout')
    <nav>
        @include('app.menu')
    </nav>
    <section class="container">
        <br>
        <h1 class="text-center mt-7">Ajout Medicament</h1>
        <form action="{{route('medicament.store')}}" method="post">
            @csrf
            <x-component.input type='text' name='nom' value='{{old("nom")}}' label='Nom Medicament :'/>
            <x-component.input type='decimal' name='prix' value='{{old("prix")}}' label='Prix Unitaire : '/>
            <x-component.input type='number' name='stock' value='{{old("stock")}}' label='Quantité : '/>
            <x-component.input type='date' name='date_expiration' value='{{old("date_expiration")}}' label='Date Expiration : '/>
            <x-component.input type='text' name='description' value='{{old("description")}}' label='Description :'/>
            <hr>
            <button class="btn btn-outline-info">AJOUTER</button>
        </form>
    </section>

