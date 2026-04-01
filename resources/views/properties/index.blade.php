<h1>Propiedades</h1>

<a href="/properties/create">Crear propiedad</a>

@foreach($properties as $property)
    <div>
        <h2>{{ $property->title }}</h2>
        <p>${{ $property->price }}</p>
        <a href="/properties/{{ $property->id }}/edit">Editar</a>
    </div>
@endforeach
