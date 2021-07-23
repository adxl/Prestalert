<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestAlert extends Module
{

    private $names = [];

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

        $this->names = [
            'banner_start_date',
            'banner_end_date',
            'banner_img',
            'banner_text',
            'banner_url',
            'banner_active',
        ];

        parent::__construct();

        $this->displayName = $this->l('PrestAlert');
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
            && $this->registerHook('displayHeader')
            && $this->registerHook('header')
            && Configuration::updateValue('PRESTALERT_NAME', 'PrestAlert'));
    }

    public function uninstall()
    {
        return (parent::uninstall()
            && Configuration::deleteByName('PRESTALERT_NAME'));
    }

    public function getContent()
    {
        $output = null;

        $error = 0;

        if (Tools::isSubmit('submit_form')) {


            if (Tools::getValue('banner_start_date') > Tools::getValue('banner_end_date')) {
                return $this->displayError($this->l("L'interval de dates est invalide")) . $this->displayForm();
            }

            foreach ($this->names as $name) {

                $currentValue = $name == 'banner_img' ? Configuration::get($name) : strval(Tools::getValue($name));

                if (is_null($currentValue) || strlen($currentValue) == 0) {
                    $output .= $this->displayError($this->l('Veuillez renseigner le champs ' . $name));
                } else {
                    if ($name == 'banner_img') {

                        $file = $_FILES['banner_img'];

                        if (!empty($file['name'])) {

                            $media = uniqid() . '-' . $file['name'];

                            if (!getimagesize($file["tmp_name"])) {
                                $output .= $this->displayError($this->l("L'image est invalide"));
                                continue;
                            }

                            $file_type = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $allowed_types = ['jpg', 'jpeg', 'png'];
                            if (!in_array($file_type, $allowed_types)) {
                                $output .= $this->displayError($this->l("Le format de l'image est invalide"));
                                continue;
                            }


                            $path = _PS_UPLOAD_DIR_ . $media;
                            if (!move_uploaded_file($file["tmp_name"], $path)) {
                                $output .= $this->displayError($this->l("La photo n'a pas pu être ajoutée"));
                                continue;
                            }

                            $currentValue = $media;
                        }
                    }

                    Configuration::updateValue($name, $currentValue);
                    $output .= $this->displayConfirmation($this->l('Settings updated'));
                }
            }
        }
        return $output . $this->displayForm();
    }

    public function displayForm()
    {
        // Récupère la langue par défaut
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Initialise les champs du formulaire dans un tableau
        $form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Configuration des banners'),
                ),
                'input' => [
                    [
                        'type' => 'datetime',
                        'label' => $this->l('Date de début d\'affichage'),
                        'name' => 'banner_start_date',
                        'size' => 20,
                        'required' => true
                    ],

                    [
                        'type' => 'datetime',
                        'label' => $this->l('Date de fin d\'affichage'),
                        'name' => 'banner_end_date',
                        'size' => 10,
                        'required' => true,
                    ],

                    [
                        'type' => 'file',
                        'label' => $this->l('Image banner'),
                        'name' => 'banner_img',
                        'accept' => 'image/*',
                        'class' => 'input',
                        'required' => true
                    ],

                    [
                        'type' => 'text',
                        'label' => $this->l('Texte à afficher'),
                        'name' => 'banner_text',
                        'class' => 'input',
                        'required' => true
                    ],

                    [
                        'type' => 'text',
                        'label' => $this->l('Lien du banner'),
                        'name' => 'banner_url',
                        'placeholder' => 'http://monsite/chemin/mapage',
                        'class' => 'input',
                        'required' => true
                    ],

                    [
                        'type' => 'radio',
                        'label' => $this->l('Visibilité'),
                        'name' => 'banner_active',
                        'class' => 'input',
                        'class'     => 't',
                        'is_bool' => true,
                        'values'  => [
                            [
                                'id' => 'visible',
                                'value' => 1,
                                'label' => $this->l('Visible'),
                            ],
                            [
                                'id' => 'hidden',
                                'value' => 0,
                                'label' => $this->l('Hidden'),
                            ],
                        ],
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Enregistrer'),
                    'name'  => 'submit_form'
                ]
            ),
        );

        $helper = new HelperForm();

        // Module, token et currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Langue
        $helper->default_form_language = $defaultLang;

        // Charge la valeur de $name depuis la base
        foreach ($this->names as $name)
            $helper->fields_value[$name] = Configuration::get($name);

        return $helper->generateForm(array($form));
    }

    public function hookDisplayHeader($params)
    {
        $this->context->smarty->assign([
            'prestalert_start' => Configuration::get('banner_start_date'),
            'prestalert_end' => Configuration::get('banner_end_date'),
            'prestalert_src' => Configuration::get('banner_img'),
            'prestalert_text' => Configuration::get('banner_text'),
            'prestalert_url' => Configuration::get('banner_url'),
            'prestalert_active' => Configuration::get('banner_active'),
        ]);

        $this->context->controller->addCSS(
            $this->_path . 'views/css/prestalert.css',
            ['server' => 'remote', 'position' => 'head', 'priority' => 150]
        );

        return $this->display(__FILE__, 'prestalert.tpl');
    }
}
