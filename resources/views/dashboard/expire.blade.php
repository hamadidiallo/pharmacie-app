 @extends('layout')
 @section('content')
     <nav>
         @include('app.menu')
     </nav>
     <div class="card-body">
         <div class="card-header bg-info text-white text-center">
             LISTE PRODUIT EXPIRATION PROCHE
         </div>
         <table class="table table-striped text-center">
             <thead>
                 <tr>
                     <th>NOM PODUIT</th>
                     <th>DATE EXPIRATION</th>
                 </tr>
             </thead>
             <tbody>
                 @forelse($expires as $m)
                     <tr>
                         <td> <span class="badge  fs-6">{{ $m->nom }}</span></td>

                         <td> <span class="badge bg-success fs-6">{{ $m->date_expiration }}</span></td>
                     </tr>
                 @empty
                     <tr>
                         <td colspan="2">AUCUN PRODUIT PROCHE D'EXPIRATION</td>
                     </tr>
                 @endforelse
             </tbody>
         </table>

     </div>
 @endsection
