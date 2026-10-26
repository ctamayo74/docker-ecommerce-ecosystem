<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Este proyecto es una API pura. La única ruta web es un redireccionamiento
| a la documentación Swagger para conveniencia al abrir la URL raíz.
|
*/

Route::get('/', function () {
    return redirect('/api/documentation');
});
