 @extends('layout')
 @section('content')
     <nav>
         @include('app.menu')
     </nav>
     <div class="row mt-4">
         <div class="col-md-12">
             <div class="card-body">
                 <div class="card-header  text-white text-center fs-30 p-40">
                     LISTE PRODUIT STOCK FAIBLE
                 </div>
                 <table class="table table-striped text-center">
                     <thead>
                         <tr>
                             <th>NOM PODUIT</th>
                             <th>STOCK</th>
                         </tr>
                     </thead>
                     <tbody>
                         @forelse($stockFaible as $m)
                             <tr>
                                 <td>{{ $m->nom }}</td>
                                 <span class="badge bg-info">
                                     <td>{{ $m->stock }}</td>
                                 </span>
                             </tr>
                         @empty
                             <tr>
                                 <td colspan="2">AUCUNE STOCK FAIBLE</td>
                             </tr>
                         @endforelse
                     </tbody>
                 </table>

             </div>
         </div>
     </div>
 @endsection
