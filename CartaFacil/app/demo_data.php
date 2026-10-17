<?php
// Datos del local de demostración (inventado). Lo usan instalar.php y la demo estática.
return [
    'restaurant' => [
        'email' => 'demo@cartafacil.es',
        'password' => 'demo1234',
        'name' => 'Chiringuito La Ola',
        'slug' => 'demo',
        'tagline' => 'Pescaíto, arroces y buena sombra frente al mar',
        'phone' => '950 000 000',
        'address' => 'Paseo Marítimo s/n',
        'color' => '#0e7490',
        'footer_note' => 'Precios con IVA incluido. Servicio de pan y alioli: 1,50 € por persona.',
    ],
    'categories' => [
        'Para picar' => [
            ['Ensaladilla rusa', 'Con atún, langostinos y picos de pan', 9.50, 5.50, 'huevos,crustaceos,pescado,gluten'],
            ['Tomate aliñado', 'Tomate de la tierra, ventresca y cebolleta', 8.00, null, 'pescado'],
            ['Pimientos de padrón', 'Con sal en escamas', 7.00, null, ''],
            ['Patatas bravas', 'Salsa brava de la casa y alioli', 6.50, 4.00, 'huevos'],
        ],
        'Pescaíto frito' => [
            ['Boquerones fritos', 'Con limón', 11.00, 6.50, 'pescado,gluten'],
            ['Calamares a la andaluza', 'Calamar fresco del día', 14.00, 8.00, 'moluscos,gluten'],
            ['Cazón en adobo', 'Marinado en vinagre, comino y pimentón', 12.00, 7.00, 'pescado,gluten,sulfitos'],
            ['Fritura variada', 'Boquerón, calamar, cazón y gamba', 18.00, null, 'pescado,moluscos,crustaceos,gluten'],
        ],
        'Arroces (mín. 2 personas)' => [
            ['Arroz a banda', 'Precio por persona. Con alioli', 15.00, null, 'pescado,crustaceos,moluscos,huevos'],
            ['Arroz negro', 'Precio por persona. Con sepia y alioli', 16.00, null, 'moluscos,huevos'],
            ['Paella de verduras', 'Precio por persona', 13.00, null, 'apio'],
        ],
        'Postres' => [
            ['Tarta de queso', 'Al horno, con mermelada de frutos rojos', 5.50, null, 'lacteos,huevos,gluten'],
            ['Helado artesano', 'Vainilla, chocolate o limón', 4.00, null, 'lacteos,frutos'],
        ],
        'Bebidas' => [
            ['Caña', null, 2.20, null, 'gluten'],
            ['Tinto de verano', null, 3.50, null, 'sulfitos'],
            ['Refresco', 'Cola, naranja o limón', 2.80, null, ''],
            ['Agua mineral', '50 cl', 2.00, null, ''],
        ],
    ],
    'sold_out' => ['Cazón en adobo'],
];
