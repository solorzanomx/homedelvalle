<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categoryId = DB::table('help_categories')->where('slug', 'operaciones')->value('id');

        if (!$categoryId) {
            return;
        }

        DB::table('help_articles')->updateOrInsert(
            ['slug' => 'comisionistas-referidos'],
            [
                'help_category_id' => $categoryId,
                'title' => 'Comisionistas y referidos',
                'slug' => 'comisionistas-referidos',
                'sort_order' => 4,
                'view_count' => 0,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'content' => <<<'MD'
# Comisionistas y referidos

El programa de comisionistas permite que personas externas (porteros, vecinos, contactos, clientes pasados) nos refieran propietarios o clientes a cambio de un porcentaje de la comision que cobra la agencia.

## Esquema de comisiones

| Tipo de referido | Porcentaje | Cuando aplica |
|-----------------|-----------|---------------|
| **Trajo propietario** | 5% de la comision de la agencia | Nos refiere un dueno de casa para firmar exclusiva |
| **Trajo cliente listo** | 10% de la comision de la agencia | Nos trae un comprador o inquilino listo para cerrar |

**Importante:** La comision se paga unicamente cuando la operacion se cierra exitosamente.

---

## Ejemplo real: Como funciona paso a paso

Imaginemos este escenario:

1. **Juanito Perez** es el portero de un edificio. Le comentamos que damos el 5% de nuestra comision a quien nos consiga duenos queriendo vender.
2. Juanito nos llama: *"Tengo a Pedro Torres del 4to piso. Tiene un departamento que quiere vender y quiere una cita."*
3. Registramos a Juanito como **comisionista** tipo "Portero" en el sistema.
4. Registramos el **referido**: "Pedro Torres, Tel: 55-1234-5678, Depto 4B en Edificio Roma Norte".
5. Contactamos a Pedro, visitamos el departamento, y firmamos la **exclusiva**.
6. Creamos la propiedad y la operacion en el sistema, y las **vinculamos** al referido de Juanito.
7. Publicamos la propiedad y trabajamos la venta.
8. La propiedad se vende — la operacion llega a etapa **Cierre**.
9. El sistema calcula automaticamente la comision de Juanito: **comision de la agencia x 5%**.
10. Se le paga a Juanito y se registra como **Pagado**.

---

## Paso a paso en el sistema

### 1. Registrar al comisionista

1. Ve a **Comisionistas** en el menu lateral (seccion Equipo)
2. Haz clic en **"+ Nuevo"**
3. Selecciona el tipo de comisionista:
   - **Portero** — Portero de edificio que conoce a los vecinos
   - **Vecino** — Vecino de la zona con contactos
   - **Broker Hipotecario** — Profesional de hipotecas con clientes
   - **Cliente Pasado** — Un cliente anterior que nos refiere
   - **Comisionista** — Comisionista profesional
   - **Otro** — Cualquier otro contacto
4. Ingresa su nombre, telefono y email
5. Guarda

### 2. Registrar un referido

1. Abre la ficha del comisionista
2. Baja a la seccion **"Registrar Referido"**
3. Selecciona el tipo de referido:
   - **Trajo propietario (5%)** — si nos refiere al dueno de un inmueble
   - **Trajo cliente listo (10%)** — si nos trae un comprador o inquilino
4. Escribe el **nombre** de la persona referida (obligatorio)
5. Agrega su **telefono** si lo tienes
6. Escribe el **contexto**: direccion del inmueble, situacion, que quiere hacer
7. Si la propiedad ya existe en el sistema, seleccionala del dropdown
8. La comision porcentual se asigna automaticamente (5% o 10%), pero puedes ajustarla si hay un acuerdo diferente
9. Haz clic en **"Registrar Referido"**

### 3. Dar seguimiento al referido

El referido pasa por 4 etapas:

| Estado | Significado | Que hacer |
|--------|-------------|-----------|
| **Registrado** | Se recibio la referencia | Contactar a la persona referida, agendar cita |
| **En proceso** | Estamos trabajando el caso | Crear propiedad/operacion en el sistema y vincularlas |
| **Por pagar** | La operacion cerro, se adeuda la comision | Verificar monto calculado y proceder al pago |
| **Pagado** | Se pago al comisionista | Queda registrada la fecha y monto de pago |

### 4. Vincular propiedad, operacion y cliente

Conforme avanza el proceso, necesitas vincular el referido con los registros del sistema:

1. En la ficha del comisionista, ve a la pestana **"Referidos"**
2. En el referido que quieras vincular, haz clic en **"Vincular"**
3. Selecciona la **propiedad** (una vez creada en el sistema)
4. Selecciona la **operacion** (una vez creada en el pipeline)
5. Selecciona el **cliente/propietario** (una vez dado de alta)
6. Guarda

Es muy importante vincular la **operacion** porque el calculo automatico de la comision depende de ella.

### 5. Calculo y pago de la comision

Cuando la operacion vinculada llega a la etapa de **Cierre**:

1. El sistema calcula automaticamente: `comision de la agencia x porcentaje del referido`
2. El referido cambia automaticamente a estado **"Por pagar"** con el monto calculado
3. El monto aparece en la ficha del comisionista
4. Revisa que el monto sea correcto
5. Al confirmar el pago, haz clic en **"Marcar pagado"**
6. Se registra la fecha de pago y se suma al total ganado del comisionista

---

## Donde ver la informacion

- **Listado de comisionistas**: Ve el total de referidos, monto ganado y estado de cada uno
- **Ficha del comisionista**: Pestana "Informacion" con estadisticas, pestana "Referidos" con historial completo
- **Chips de vinculacion**: En cada referido puedes ver si ya esta vinculado a propiedad, operacion y cliente

---

## Tips practicos

- **Registra el referido de inmediato** cuando recibes la llamada, aunque no tengas todos los datos. Solo necesitas el nombre.
- **Usa el campo de contexto** para anotar la direccion del inmueble y cualquier detalle relevante.
- **Vincula la operacion** tan pronto la crees en el pipeline — esto habilita el calculo automatico al cierre.
- **El porcentaje se puede ajustar** manualmente si hay un acuerdo diferente al estandar.
- **Revisa los "Por pagar"** regularmente: la pestana de referidos muestra un indicador naranja cuando hay comisiones pendientes.
- Si el comisionista tiene WhatsApp, puedes contactarlo directo desde su perfil con el boton verde.
MD
            ]
        );
    }

    public function down(): void
    {
        DB::table('help_articles')->where('slug', 'comisionistas-referidos')->delete();
    }
};
