<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class WhatsAppMejorPrecio extends Module
{
    public function __construct()
    {
        $this->name = 'whatsappmejorprecio';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Personalizado';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('WhatsApp - Mejor Precio');
        $this->description = $this->l('Muestra un botón de WhatsApp en todas las fichas de producto para solicitar mejor precio o cotización.');
        $this->ps_versions_compliancy = array(
            'min' => '9.0.0',
            'max' => _PS_VERSION_,
        );
    }

    public function install()
    {
        return parent::install()
            && Configuration::updateValue('WMP_PHONE', '52229123467')
            && $this->registerHook('displayProductAdditionalInfo');
    }

    public function uninstall()
    {
        return Configuration::deleteByName('WMP_PHONE')
            && parent::uninstall();
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitWhatsAppMejorPrecio')) {
            $phone = preg_replace('/\D+/', '', (string) Tools::getValue('WMP_PHONE'));

            if (Tools::strlen($phone) < 10) {
                $output .= $this->displayError($this->l('El número de WhatsApp no parece válido.'));
            } else {
                Configuration::updateValue('WMP_PHONE', $phone);
                $output .= $this->displayConfirmation($this->l('Configuración guardada.'));
            }
        }

        $phone = Configuration::get('WMP_PHONE');

        return $output . '
        <div class="panel">
            <h3><i class="icon-whatsapp"></i> ' . $this->l('WhatsApp - Mejor Precio') . '</h3>
            <p>' . $this->l('Este módulo muestra un botón verde en las fichas de producto para que el cliente solicite un mejor precio o una cotización por WhatsApp.') . '</p>
            <form method="post">
                <div class="form-group">
                    <label class="control-label col-lg-3">' . $this->l('WhatsApp con lada') . '</label>
                    <div class="col-lg-6">
                        <input type="text" name="WMP_PHONE" value="' . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . '" class="form-control">
                        <p class="help-block">' . $this->l('Ejemplo: 52229123467. No incluyas +, espacios, guiones ni paréntesis.') . '</p>
                    </div>
                </div>
                <div class="panel-footer">
                    <button type="submit" name="submitWhatsAppMejorPrecio" class="btn btn-default pull-right">
                        <i class="process-icon-save"></i> ' . $this->l('Guardar') . '
                    </button>
                </div>
            </form>
        </div>';
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        $phone = preg_replace('/\D+/', '', (string) Configuration::get('WMP_PHONE'));

        if (!$phone || empty($params['product'])) {
            return '';
        }

        $product = $params['product'];

        $idProduct = 0;
        if (!empty($product['id_product'])) {
            $idProduct = (int) $product['id_product'];
        } elseif (!empty($product['id'])) {
            $idProduct = (int) $product['id'];
        }

        $name = !empty($product['name']) ? (string) $product['name'] : $this->l('este producto');
        $reference = !empty($product['reference']) ? (string) $product['reference'] : '';

        $url = !empty($product['url']) ? (string) $product['url'] : '';
        if (!$url && $idProduct) {
            $url = $this->context->link->getProductLink($idProduct);
        }

        $price = '';
        if (isset($product['price_amount']) && $product['price_amount'] !== '') {
            $price = $this->context->getCurrentLocale()->formatPrice(
                (float) $product['price_amount'],
                $this->context->currency->iso_code
            );
        } elseif ($idProduct) {
            $rawPrice = (float) Product::getPriceStatic($idProduct, true);
            $price = $this->context->getCurrentLocale()->formatPrice(
                $rawPrice,
                $this->context->currency->iso_code
            );
        }

        $message = 'Hola, me interesa el producto: ' . $name;
        if ($reference !== '') {
            $message .= ' | SKU/Referencia: ' . $reference;
        }
        if ($price !== '') {
            $message .= ' | Precio publicado: ' . $price;
        }
        $message .= '. ¿Me pueden compartir su mejor precio y disponibilidad?';

        $waUrl = 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeWaUrl = htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8');

        return '
        <div style="margin:18px 0;padding:18px;border:1px solid #25D366;border-radius:10px;background:#f7fff9;text-align:center;box-sizing:border-box;">
            <div style="font-size:20px;font-weight:700;line-height:1.3;margin-bottom:8px;color:#222;">
                ¿Buscas el mejor precio?
            </div>
            <div style="font-size:15px;line-height:1.5;color:#444;margin-bottom:14px;">
                Solicita un <strong>precio especial</strong> para <strong>' . $safeName . '</strong>.
                Si necesitas varias piezas, podemos prepararte una <strong>cotización personalizada.</strong>
            </div>
            <a href="' . $safeWaUrl . '" target="_blank" rel="noopener noreferrer"
               style="display:block;width:100%;box-sizing:border-box;background:#25D366;color:#fff;text-decoration:none;font-size:16px;font-weight:700;padding:13px 18px;border-radius:7px;border:0;cursor:pointer;">
                💬 Solicitar mejor precio por WhatsApp
            </a>
            <div style="font-size:12px;color:#777;margin-top:9px;">
                Atención personalizada • Cotizaciones por volumen
            </div>
        </div>';
    }
}
