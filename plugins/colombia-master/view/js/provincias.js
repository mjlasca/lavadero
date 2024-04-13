/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2015-2016  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

var provincia_list = [
   {value: 'Amazonas'},
   {value: 'Antioquia'},
   {value: 'Arauca'},
   {value: 'Atlantico'},
   {value: 'Bogotá'},
   {value: 'Bolívar'},
   {value: 'Boyacá'},
   {value: 'Caldas'},
   {value: 'Caquetá'},
   {value: 'Casanare'},
   {value: 'Cauca'},
   {value: 'Cesar'},
   {value: 'Córdoba'},
   {value: 'Cundinamarca'},
   {value: 'Chocó'},
   {value: 'Guainía'},
   {value: 'Guaviare'},
   {value: 'Huila'},
   {value: 'La Guajira'},
   {value: 'Magdalena'},
   {value: 'Meta'},
   {value: 'Nariño'},
   {value: 'Norte de Santander'},
   {value: 'Putumayo'},
   {value: 'Quindío'},
   {value: 'Risaralda'},
   {value: 'San Andrés y Providencia'},
   {value: 'Santander'},
   {value: 'Sucre'},
   {value: 'Tolima'},
   {value: 'Valle del Cauca'},
   {value: 'Vaupés'},
   {value: 'Vichada'},
];

$(document).ready(function() {
   $("#ac_provincia, #ac_provincia2").autocomplete({
      lookup: provincia_list,
   });
});
