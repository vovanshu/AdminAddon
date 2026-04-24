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
                'allow_empty' => true,
            ],
            'attributes' => [
                'multiple' => true,
                'class' => 'chosen-select',
                'data-placeholder' => 'Select properties…', // @translate
                'id' => $this->getOps('advsearch_autocomplete_fields'),
                'value' => $this->getSiteSets('advsearch_autocomplete_fields'),
            ],
        ]);
        
        $allowedEmpty[] = $this->getOps('advsearch_autocomplete_fields');

        $this->form->add([
            'type' => 'checkbox',
            'name' => $this->getOps('search_fasets_enable'),
            'options' => [
                'element_group' => 'search',
                'label' => 'Enable search fasets', // @translate
                'info' => 'Enable search fasets config', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'value' => $this->getSiteSets('search_fasets_enable'),
                'id' => $this->getOps('search_fasets_enable'),
            ],
        ]);

        $this->form->add([
            'name' => $this->getOps('search_fasets'),
            'type' => 'textarea',
            'options' => [
                'element_group' => 'search',
                'label' => 'Search fasets config', // @translate
                'info' => '', // @translate
            ],
            'attributes' => [
                'value' => $this->getSiteSets('search_fasets'),
                'id' => $this->getOps('search_fasets'),
                'class' => 'edit-ini-textarea'
            ],
        ]);

        $allowedEmpty[] = $this->getOps('search_fasets');

        $inputFilter = $this->form->getInputFilter();
        $this->inputFilterAllowEmpty($inputFilter, $allowedEmpty);

    }

}
