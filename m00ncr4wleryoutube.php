<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class m00ncr4wlerYoutube extends Module
{
    public function __construct()
    {
        $this->name = 'm00ncr4wleryoutube';
        $this->tab = 'front_office_features';
        $this->version = '0.1.2';
        $this->author = 'm00ncr4wler - David Heinz';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Youtube');
        $this->description = $this->l('Product Youtube.');
    }

    public function install()
    {
        if (!parent::install()
            || !$this->alterTable('add')
            || !$this->registerHook('actionAdminControllerSetMedia')
            || !$this->registerHook('actionProductUpdate')
            || !$this->registerHook('displayAdminProductsExtra')
            || !$this->registerHook('displayFooterProduct')
            || !$this->registerHook('header')
        ) {
            return false;
        }
        return true;
    }

    public function alterTable($method)
    {
        switch ($method) {
            case 'add':
                $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'product_lang ADD `youtube` VARCHAR(255) NOT NULL';
                break;

            case 'remove':
                $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'product_lang DROP COLUMN `youtube`';
                break;
        }

        if (!Db::getInstance()->Execute($sql)) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() || !$this->alterTable('remove')
            || !$this->unregisterHook('actionAdminControllerSetMedia')
            || !$this->unregisterHook('actionProductUpdate')
            || !$this->unregisterHook('displayAdminProductsExtra')
            || !$this->unregisterHook('displayFooterProduct')
            || !$this->registerHook('header')
        ) {
            return false;
        }
        return true;
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        if (Validate::isLoadedObject($product = new Product((int)Tools::getValue('id_product')))) {
            $this->context->smarty->assign(
                array(
                    'youtube' => $this->getYoutubeField((int)Tools::getValue('id_product')),
                    'languages' => $this->context->controller->_languages,
                    'default_form_language' => (int)Configuration::get('PS_LANG_DEFAULT')
                )
            );
            return $this->display(__FILE__, 'views/templates/admin/' . $this->name . '.tpl');
        }
    }

    public function getYoutubeField($id_product)
    {
        $sql = 'SELECT youtube, id_lang FROM ' . _DB_PREFIX_ . 'product_lang WHERE id_product = ' . (int)$id_product . ';';
        $result = Db::getInstance()->ExecuteS($sql, true, false);

        if (!$result) {
            $fields = array();
        }
        foreach ($result as $field) {
            $fields[$field['id_lang']] = $field['youtube'];
        }
        return $fields;
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        // add necessary javascript to products back office
        if ($this->context->controller->controller_name == 'AdminProducts' && Tools::getValue('id_product')) {
            $this->context->controller->addJS($this->_path . '/views/templates/js/' . $this->name . '.js');
        }
    }

    public function hookActionProductUpdate($params)
    {
        if ((int)array_search("Youtube", Tools::getValue('submitted_tabs')) != null) {
            $id_product = (int)Tools::getValue('id_product');
            $languages = Language::getLanguages(true);
            foreach ($languages as $lang) {
                $url = Tools::getValue('youtube_' . $lang['id_lang']);
                if (!empty($url)) {
                    if (Validate::isUrl($url)) {
                        //get youtube id from url
                        //http://stackoverflow.com/questions/3392993/php-regex-to-get-youtube-video-id
                        $matches = array();
                        preg_match('#(?<=(?:v|i)=)[a-zA-Z0-9-]+(?=&)|(?<=(?:v|i)\/)[^&\n]+|(?<=embed\/)[^"&\n]+|(?<=(?:v|i)=)[^&\n]+|(?<=youtu.be\/)[^&\n]+#', $url, $matches);
                        if (isset($matches[0]) && is_string($matches[0])) {
                            $url = 'https://www.youtube-nocookie.com/embed/' . $matches[0];
                        } else {
                            $this->context->controller->errors[] = Tools::displayError(sprintf($this->l('[Error] Can\'t find youtube id in url for %s!'), $lang['name']));
                            $url = '';
                        }
                    } else {
                        $this->context->controller->errors[] = Tools::displayError(sprintf($this->l('[Error] No valid URL for %s!'), $lang['name']));
                        $url = '';
                    }
                }
                if (!Db::getInstance()->update('product_lang', array('youtube' => pSQL($url)), 'id_lang = ' . $lang['id_lang'] . ' AND id_product = ' . $id_product)) {
                    $this->context->controller->errors[] = Tools::displayError('Error: ') . mysql_error();
                }
            }
        }
    }

    public function hookDisplayFooterProduct($params)
    {
        $url = $this->getURL((int)Tools::getValue('id_product'));
        if (!empty($url)) {
            $this->context->smarty->assign('URL', $url);
            return $this->display(__FILE__, 'views/templates/front/' . $this->name . '.tpl');
        }
    }

    public function getURL($id_product)
    {
        $urls = $this->getYoutubeField($id_product);
        $lang_id = $this->context->language->id;

        if (empty($urls[$lang_id])) {
            return array();
        } else {
            return $urls[$lang_id];
        }
    }

    public function hookDisplayHeader($params)
    {
        $allowedControllers = array('product');
        $c = $this->context->controller;
        if (isset($c->php_self) && in_array($c->php_self, $allowedControllers)) {
            $this->context->controller->addCSS(($this->_path) . 'views/templates/css/' . $this->name . '.css', 'all');
        }
    }
}
