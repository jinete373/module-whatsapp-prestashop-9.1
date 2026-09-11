Módulo: WhatsApp - Mejor Precio
Versión: 1.0.1
Compatible: PrestaShop 9.x

Instalación:
1. En el Back Office ve a Módulos > Gestor de módulos.
2. Selecciona "Instalar un módulo" y sube whatsappmejorprecio.zip.
3. Instálalo.
4. En la configuración del módulo puedes revisar/cambiar el número de WhatsApp.
5. Limpia la caché de PrestaShop si no aparece inmediatamente.

El módulo utiliza el hook displayProductAdditionalInfo, que PrestaShop 9 documenta como un punto para añadir información adicional en la ficha de producto.

Corrección versión 1.0.1:
- Se eliminó Tools::displayPrice(), que no está disponible como método público en PrestaShop 9.1.
- El precio ahora se formatea mediante Context::getCurrentLocale()->formatPrice(), usando el código ISO de la moneda.

