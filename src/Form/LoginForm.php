<?php
namespace AdminAddon\Form;

use Laminas\Form\Form;
use Omeka\Form\Element\Recaptcha;
use AdminAddon\Common;

class LoginForm extends Form
{

    use Common;

    public function __construct($serviceLocator, $requestedName, $options)
    {
        $this->setServiceLocator($serviceLocator);
        parent::__construct();
    }

    public function init()
    {
        $this->setAttribute('class', 'disable-unsaved-warning');
        $this->add([
            'name' => 'email',
            'type' => 'Email',
            'options' => [
                'label' => 'Email', // @translate
            ],
            'attributes' => [
                'required' => true,
                'id' => 'email',
            ],
        ]);
        $this->add([
            'name' => 'password',
            'type' => 'Password',
            'options' => [
                'label' => 'Password', // @translate
            ],
            'attributes' => [
                'required' => true,
                'id' => 'password',
            ],
        ]);

        if($this->getSets('recaptcha_enable_on_login') == 'true' && !$this->hadIPInWLrecaptcha()){
            $this->add([
                'name' => 'recaptcha',
                'type' => Recaptcha::class,
                'attributes' => [
                    'type' => 'recaptcha',
                    'name' => 'g-recaptcha-response',
                    'class' => 'g-recaptcha',
                ],
            ]);
        }

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'email',
            'required' => true,
        ]);
        $inputFilter->add([
            'name' => 'password',
            'required' => true,
        ]);
    }
}
