<?php
class mwrfctestshowModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->display_column_left = FALSE;
        $this->display_column_right = FALSE;

        parent::initContent();
        $mwr = new MWRFcTest();
        $id_lang = $this->context->language->id;
        $this->context->smarty->assign(
            [
                'mwrfctest' => [
                    'title' => Configuration::get($mwr->getConfigName('TITLE') . '_' . $id_lang),
                    'description' => Configuration::get($mwr->getConfigName('DESCRIPTION') . '_' . $id_lang),
                    'url' => Configuration::get($mwr->getConfigName('URL') . '_' . $id_lang),
                ]
            ]
        );

        $this->setTemplate('module:mwrfctest/views/templates/front/show.tpl');
    }
}
