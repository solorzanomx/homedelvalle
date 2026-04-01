<h1>Editar propiedad</h1>

<form method="POST" action="/properties/{{ $property->id }}">
    @csrf
    @method('PUT')

    <input type="text" name="title" value="{{ $property->title }}"><br>
    <input type="text" name="price" value="{{ $property->price }}"><br>

    <button type="submit">Actualizar</button>
</form>
