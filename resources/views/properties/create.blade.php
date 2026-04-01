<h1>Nueva propiedad</h1>

<form method="POST" action="/properties">
    @csrf

    <input type="text" name="title" placeholder="Título"><br>
    <input type="text" name="price" placeholder="Precio"><br>
    <input type="text" name="city" placeholder="Ciudad"><br>
    <input type="text" name="colony" placeholder="Colonia"><br>

    <button type="submit">Guardar</button>
</form>
