<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestAlert extends Module
{
    public function __construct()
    {
        $this->name = 'prestalert';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Adel Sen';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => '1.7.99',
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('PresAlert');
        $this->description = $this->l('Afficher une bannière en haut de votre site web de boutique.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('PRESTALERT_NAME')) {
            $this->warning = $this->l('No name provided');
        }
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        return (parent::install()
            && $this->registerHook('leftColumn')
            && $this->registerHook('header')
            && Configuration::updateValue('PRESTALERT_NAME', 'my friend'));
    }

    public function uninstall()
    {
        return (parent::uninstall()
            && Configuration::deleteByName('PRESTALERT_NAME'));
    }
}
