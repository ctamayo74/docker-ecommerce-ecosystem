<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Puebla la tabla products con un catálogo de ejemplo variado,
     * útil para probar los endpoints de listado, filtrado y compra.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Teclado mecánico RGB',
                'description' => 'Teclado mecánico con switches rojos, retroiluminación RGB personalizable y estructura de aluminio.',
                'sku' => 'TEC-MEC-001',
                'price' => 49.99,
                'stock' => 35,
                'image_url' => 'https://images.example.com/products/teclado-mecanico-rgb.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Mouse inalámbrico ergonómico',
                'description' => 'Mouse inalámbrico de 6 botones con sensor óptico de 3200 DPI y batería recargable.',
                'sku' => 'MOU-ERG-002',
                'price' => 24.50,
                'stock' => 60,
                'image_url' => 'https://images.example.com/products/mouse-ergonomico.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Monitor LED 24 pulgadas Full HD',
                'description' => 'Monitor LED de 24" resolución 1920x1080, panel IPS, entradas HDMI y VGA.',
                'sku' => 'MON-LED-003',
                'price' => 129.99,
                'stock' => 15,
                'image_url' => 'https://images.example.com/products/monitor-24.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Audífonos con cancelación de ruido',
                'description' => 'Audífonos over-ear inalámbricos con cancelación activa de ruido y hasta 30 horas de batería.',
                'sku' => 'AUD-ANC-004',
                'price' => 89.90,
                'stock' => 40,
                'image_url' => 'https://images.example.com/products/audifonos-anc.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Webcam Full HD 1080p',
                'description' => 'Webcam USB con autoenfoque, micrófono integrado y corrección de luz automática.',
                'sku' => 'WEB-CAM-005',
                'price' => 34.99,
                'stock' => 50,
                'image_url' => 'https://images.example.com/products/webcam-1080p.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Disco SSD NVMe 1TB',
                'description' => 'Unidad de estado sólido NVMe M.2 de 1TB, velocidades de lectura de hasta 3500 MB/s.',
                'sku' => 'SSD-NVM-006',
                'price' => 79.99,
                'stock' => 25,
                'image_url' => 'https://images.example.com/products/ssd-nvme-1tb.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Silla ergonómica de oficina',
                'description' => 'Silla ergonómica con soporte lumbar ajustable, reposabrazos 3D y base reforzada.',
                'sku' => 'SIL-ERG-007',
                'price' => 189.00,
                'stock' => 10,
                'image_url' => 'https://images.example.com/products/silla-ergonomica.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Hub USB-C 7 en 1',
                'description' => 'Adaptador USB-C con salida HDMI 4K, lector de tarjetas SD/microSD y 3 puertos USB 3.0.',
                'sku' => 'HUB-USC-008',
                'price' => 29.99,
                'stock' => 45,
                'image_url' => 'https://images.example.com/products/hub-usbc.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Micrófono de condensador USB',
                'description' => 'Micrófono USB ideal para streaming y podcasting, con soporte antivibración incluido.',
                'sku' => 'MIC-USB-009',
                'price' => 59.99,
                'stock' => 20,
                'image_url' => 'https://images.example.com/products/microfono-usb.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Mochila para laptop 15.6"',
                'description' => 'Mochila resistente al agua con compartimento acolchado para laptops de hasta 15.6 pulgadas.',
                'sku' => 'MOC-LAP-010',
                'price' => 39.99,
                'stock' => 0,
                'image_url' => 'https://images.example.com/products/mochila-laptop.jpg',
                // Producto sin stock para probar la validación de "stock insuficiente".
                'is_active' => true,
            ],
            [
                'name' => 'Router WiFi 6 doble banda',
                'description' => 'Router de última generación WiFi 6, velocidades de hasta 3000 Mbps y 4 antenas externas.',
                'sku' => 'ROU-WF6-011',
                'price' => 99.99,
                'stock' => 18,
                'image_url' => 'https://images.example.com/products/router-wifi6.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Producto descontinuado (inactivo)',
                'description' => 'Producto de ejemplo marcado como inactivo para probar el filtro del catálogo público.',
                'sku' => 'DES-INA-012',
                'price' => 15.00,
                'stock' => 5,
                'image_url' => null,
                // Producto inactivo: no debe aparecer en GET /api/products.
                'is_active' => false,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                $product
            );
        }
    }
}
