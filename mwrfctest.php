<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
class MWRFcTest extends Module
{
    protected $config_form = false;
    protected $fields_list = [];
    protected $field_values = [];
    protected $config_name;
    protected $is_PS_17;
    protected $ps_17_hooks;
    protected $ps_16_hooks;
    protected $hooks_list;
    protected $_full_path;

    public function __construct()
    {
        $this->name = 'mwrfctest';
        $this->config_name = 'MWRFCTEST';
        $this->tab = 'merchandizing';
        $this->version = '1.0.0';
        $this->author = 'mywebpage rocks';
        $this->need_instance = 0;
        $this->is_PS_17 = (version_compare(_PS_VERSION_, '1.7.0.0', '>=') === true) ? true : false;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('MWR Front Controller Test');
        $this->description = $this->l('Zadanie 2');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module ?');
        $this->_full_path = dirname(__FILE__);
        $this->fields_list = [
            'TITLE' => [
                'type' => 'text',
                'data' => 'string',
                'default_values' => [
                    'en' => 'Hello X13',
                    'pl' => 'Cześć X13'
                ],
                'label' => $this->l('Title'),
                'col' => '4',
                'desc' => $this->l('Enter title'),
                'required' => 'true',
                'icon' => 'comments-o',
                'lang' => true
            ],
            'DESCRIPTION' => [
                'type' => 'textarea',
                'autoload_rte' => true,
                'data' => 'string',
                'default_value' => '',
                'label' => $this->l('Description'),
                'col' => '8',
                'desc' => $this->l('Enter description'),
                'required' => 'true',
                'icon' => 'comments-o',
                'lang' => true
            ],
            'URL' => [
                'type' => 'text',
                'data' => 'string',
                'default_values' => [
                    'en' => 'hello-world',
                    'pl' => 'cześć'
                ],
                'label' => $this->l('URL'),
                'col' => '4',
                'desc' => $this->l('Enter URL'),
                'required' => 'true',
                'icon' => 'comments-o',
                'lang' => true
            ],
        ];
        $this->field_values = [];
        foreach ($this->field_values as $key => $values) {
            $this->fields_list[$key] = array_merge($this->fields_list[$key], $values);
        }
        $this->hooks_list = [
            'moduleRoutes',
            'displayHeader',
            'actionFrontControllerSetVariables'
        ];
        $this->ps_17_hooks = [];
        $this->ps_16_hooks = [];
        if ($this->is_PS_17) {
            $this->hooks_list = array_merge($this->hooks_list, $this->ps_17_hooks);
        } else {
            $this->hooks_list = array_merge($this->hooks_list, $this->ps_16_hooks);
        }
    }
    public function install()
    {
        return parent::install()
            && $this->installSql()
            && $this->setDefaultValues()
            && $this->registerHooks($this->hooks_list);
    }
    public function uninstall()
    {
        return $this->clearDefaultValues()
            && $this->unregisterHooks($this->hooks_list)
            && $this->uninstallSql()
            && parent::uninstall();
    }
    private function registerHooks($hooks_list = false)
    {
        if (!$hooks_list) {
            return true;
        }
        foreach ($this->hooks_list as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }
        return true;
    }
    private function unregisterHooks($hooks_list = false)
    {
        if (!$hooks_list) {
            return true;
        }
        foreach ($this->hooks_list as $hook) {
            if (!$this->isRegisteredInHook($hook)) {
                return true;
            }
            if (!$this->unregisterHook($hook)) {
                return false;
            }
        }
        return true;
    }
    public function getConfigName($value)
    {
        return $this->config_name . '_' . $value;
    }
    private function setDefaultValues()
    {
        $languages = Language::getLanguages(false);
        foreach ($this->fields_list as $key => $property) {
            if (isset($property['default_values']) && $property['default_values']) {
                foreach ($property['default_values'] as $iso => $value) {
                    foreach ($languages as $lang) {
                        if ($iso == $lang['iso_code']) {
                            Configuration::updateValue($this->getConfigName($key) . '_' . $lang['id_lang'], $value);
                        }
                    }
                }
            }
        }
        return true;
    }
    private function clearDefaultValues()
    {
        foreach ($this->fields_list as $key => $values) {
            Configuration::deleteByName($this->getConfigName($key));
        }
        return true;
    }
    private function installSql()
    {
        include($this->_full_path . '/sql/install.php');
        return true;
    }
    private function uninstallSql()
    {
        include($this->_full_path . '/sql/uninstall.php');
        return true;
    }
    public function getContent()
    {
        $output = null;
        if (Tools::isSubmit('submit' . $this->name)) {
            $this->postProcess();
        }

        return $output . $this->renderForm();
    }
    public function validateFormField($name)
    {
        // echo $name;
        // die();
        $value = Tools::getValue($name);
        $field = $this->fields_list[$name];
        if (isset($field['required']) && $field['required']) {
            if (!isset($value)) {
                return false;
            }
        } else if (empty($field['required']) && !$value) {
            return true;
        }
        if (isset($value)) {
            if (isset($field['type']) && ($field['type'] == 'int' || $field['type'] == 'float')) {
                if (!is_numeric($value)) {
                    return false;
                } else {
                    if ($field['type'] == 'int') {
                        if ((int)$value != $value) {
                            return false;
                        }
                        $value = (int)$value;
                    } else if ($field['type'] == 'float') {
                        if ((float)$value != $value) {
                            return false;
                        }
                        $value = (float)$value;
                        return false;
                    }
                    if (isset($field['min'])) {
                        if ($value < $field['min']) {
                            return false;
                        }
                    }
                    if (isset($field['max']) && !empty($field['max'])) {
                        if ($value > $field['max']) {
                            return false;
                        }
                    }
                    if (isset($field['not_zero']) && $field['not_zero']) {
                        if ($value === 0) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }
    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        return $helper->generateForm(array($this->getConfigForm()));
    }
    protected function getConfigForm()
    {
        $form = [];
        $form['legend'] = [
            'title' => $this->l('Basic settings'),
            'icon' => 'icon-cogs'
        ];
        foreach ($this->fields_list as $name => $field) {
            $form_field = [
                'name' => $this->getConfigName($name),
                'col' => $field['col'],
                'type' => $field['type'],
                'label' => $field['label'],
                'desc' => $field['desc'],
                'autoload_rte' => (isset($field['autoload_rte'])) ? true : false,
            ];
            if (isset($field['lang']) && $field['lang']) {
                $form_field['lang'] = $field['lang'];
            }
            if (isset($field['required']) && $field['required']) {
                $form_field['required'] = 'true';
            } else {
                $form_field['required'] = 'false';
            }
            if (isset($field['icon']) && $field['icon']) {
                $form_field['prefix'] = '<i class="icon icon-' . $field['icon'] . '"></i>';
            } else {
                $form_field['prefix'] = '<i class="icon icon-cogs"></i>';
            }
            if (isset($field['values']) && $field['values']) {
                $form_field['values'] = $field['values'];
            }
            $form['input'][] = $form_field;
        }
        $form['submit'] = [
            'title' => $this->l('Save configuration'),
            'class' => 'btn btn-default pull-right'
        ];
        $form['buttons'] = [];
        $config_form['form'] = $form;
        return $config_form;
    }
    protected function normalizeUrl($str)
    {
        return Tools::str2url($str);
    }
    protected function getConfigFormValues()
    {
        $languages = Language::getLanguages(false);
        $form_values = [];
        foreach ($this->fields_list as $name => $field) {
            foreach ($languages as $lang) {
                if (str_contains($this->getConfigName($name), 'URL')) {
                    $form_values[$this->getConfigName($name)][$lang['id_lang']] = $this->normalizeUrl(Configuration::get($this->getConfigName($name) . '_' . $lang['id_lang']));
                } else {
                    $form_values[$this->getConfigName($name)][$lang['id_lang']] = Configuration::get($this->getConfigName($name) . '_' . $lang['id_lang']);
                }
            }
        }
        return $form_values;
    }
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        $languages = Language::getLanguages(false);
        foreach (array_keys($form_values) as $key) {
            foreach ($languages as $lang) {
                Configuration::updateValue(($key . '_' . $lang['id_lang']), Tools::getValue($key . '_' . $lang['id_lang']));
            }
        }
    }
    public function hookModuleRoutes($params)
    {
        $url = Configuration::get($this->getConfigName('URL') . '_' . $params['cookie']->id_lang);
        if (!$url) {
            return false;
        }
        $routes =  [
            'module-mwrfctest-show' => [
                'controller' => 'show',
                'rule' =>  $url,
                'keywords' => [
                    'link_rewrite' =>  [
                        'regexp' => '[_a-zA-Z0-9-\pL]*',
                        'param' => 'link_rewrite'
                    ],
                    'lang' => [
                        'regexp' => '[_a-zA-Z0-9-\pL]*',
                        'param' => 'language'
                    ]
                ],
                'params' => [
                    'lang' => true,
                    'fc' => 'module',
                    'module' => 'mwrfctest',
                    'link_rewrite' => $url,
                ],
            ]
        ];
        // echo '<pre>';
        // var_dump($routes);
        // die();
        return $routes;
    }
    public function hookActionFrontControllerSetVariables($params)
    {
        $language_ids = Language::getIDs();

        if (isset($this->context->language) && !in_array($this->context->language->id, $language_ids)) {
            $language_ids[] = (int)$this->context->language->id;
        }
        $fc_urls = [];
        foreach ($language_ids as $id_lang) {
            $fc_urls[Language::getIsoById($id_lang)] =  Configuration::get($this->getConfigName('URL') . '_' . $id_lang);
        }
        return $fc_urls;
    }
    public function hookDisplayHeader()
    {
        if (Tools::getValue('module') && Tools::getValue('module') == 'mwrfctest') {
            $this->context->controller->registerJavascript('modules' . $this->name . '-script', 'modules/' . $this->name . '/views/js/front.js', ['position' => 'bottom', 'priority' => 150]);
        }
    }
}
