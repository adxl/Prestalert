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
            'banner_url'
        ];

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

    public function getContent()
    {
        $output = null;

        $error = 0;

        if (Tools::isSubmit('submit_form')) {

            foreach ($this->names as $name) {

                $currentValue = strval(Tools::getValue($name));

                if ($name == 'banner_img') {
                    //Sauvergader l'image dans un dossier 
                    //$currentValue = $img_name;

                    $file = $_FILES['banner_img'];

                    if (!empty($file['name'])) {

                        $media = "./modules/prestalert/images/" . uniqid() . '-' . $file['name'];

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
                        if (!move_uploaded_file($file["tmp_name"], $media)) {
                            $output .= $this->displayError($this->l("La photo n'a pas pu être ajoutée"));
                            echo $media . ' - ' . $file['tmp_name'];
                            die();
                            continue;
                        }
                        $currentValue = "/var/www/html$media";
                    }
                }


                if (
                    !$currentValue ||
                    empty($currentValue)
                ) {
                    $output .= $this->displayError($this->l('Veuillez renseigner le champs ' . $name));
                } else {
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
                        'type' => 'date',
                        'label' => $this->l('Date de début d\'affichage'),
                        'name' => 'banner_start_date',
                        'size' => 20,
                        'class' => 'input',
                        'required' => true
                    ],

                    [
                        'type' => 'date',
                        'label' => $this->l('Date de fin d\'affichage'),
                        'name' => 'banner_end_date',
                        'size' => 20,
                        'class' => 'input',
                        'required' => true
                    ],

                    [
                        'type' => 'file',
                        'label' => $this->l('Image banner'),
                        'name' => 'banner_img',
                        'accept' => 'image/*',
                        'size' => 20,
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
}
