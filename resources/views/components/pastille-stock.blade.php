@props(['medicament', 'format' => 'quantite'])

{{--
    Pastille d'état du stock. La règle vit dans App\Enums\StatutStock ;
    cette vue ne fait que la rendre.

    format="quantite" (défaut) → 142 · 4 · faible · 0 · rupture
    format="libelle"           → En stock · Stock faible · Rupture
    format="nombre"            → 142, coloré selon l'état
--}}

@php $statut = $medicament->statut_stock; @endphp

<span {{ $attributes->class([$statut->classePastille(), 'num' => $format !== 'libelle']) }}>
    @switch($format)
        @case('libelle')
            {{ $statut->libelle() }}
        @break

        @case('nombre')
            {{ $medicament->stock }}
        @break

        @default
            {{ $statut->quantiteAnnotee($medicament->stock) }}
    @endswitch
</span>
