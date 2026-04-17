<?php declare(strict_types=1);

namespace AdminAddon\Form;

use Laminas\Form\Fieldset;
use Omeka\Form\Element\PropertySelect;
use AdminAddon\General;

class SiteSettingsFieldset extends Fieldset
{

    use General;

    protected $form;

    public function setForm($form)
    {
        $this->form = $form;
    }

    public function init(): void
    {

        $options = $this->form->getOptions();

        $this->form->add([
                'name' => $this->getOps('advsearch_autocomplete'),
                'type' => 'checkbox',
                'options' => [
                    'element_group' => 'search',
                    'label' => 'Autocomplete on Advanced search', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => $this->getOps('advsearch_autocomplete'),
                    'value' => $this->getSiteSets('advsearch_autocomplete'),
                ],
            ]);

        $this->form->add([
                'name' => $this->getOps('advsearch_autocomplete_fields'),
                'type' => PropertySelect::class,
                'options' => [
                    'element_group' => 'search',
                    'empty_option' => '[Any Property]', // @translate 
                    'label' => 'Properties for autocomplete input fields in Advanced search', // @translate
                    'info' => 'Select properties for autocomplete in Advanced search input fields.', // @translate
                    'term_as_value' => true,
                ],
                'attributes' => [
                    'multiple' => true,
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select properties…', // @translate
                    'id' => $this->getOps('advsearch_autocomplete_fields'),
                    'value' => $this->getSiteSets('advsearch_autocomplete_fields'),
                ],
            ]);

        $inputFilter = $this->form->getInputFilter();
        $inputFilter->add([
            'name' => $this->getOps('advsearch_autocomplete'),
            'allow_empty' => true,
        ]);

        $inputFilter->add([
            'name' => $this->getOps('advsearch_autocomplete_fields'),
            'allow_empty' => true,
        ]);

    }

}
