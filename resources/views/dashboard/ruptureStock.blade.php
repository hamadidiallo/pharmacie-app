 @extends('layout')
 @section('content')
     <nav>
         @include('app.menu')
     </nav>
     <div class="card-body">
         <div class="card-header bg-dark text-white text-center fs-30">
             LISTE PRODUIT EN RUPTURE DE STOCK
         </div>
         <table class="table table-striped text-center">
             <thead>
                 <tr>
                     <th>NOM PODUIT</th>
                 </tr>
             </thead>
             <tbody>
                 @forelse($ruptureStock as $m)
                     <tr>
                         <td>{{ $m->nom }}</td>
                     </tr>
                 @empty
                     <tr>
                         <td colspan="1">AUCUNE RUPTURE DE STOCK</td>
                     </tr>
                 @endforelse
             </tbody>
         </table>

     </div>
 @endsection
