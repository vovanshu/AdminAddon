<?php declare(strict_types=1);

namespace AdminAddon\Form;

use Laminas\Form\Fieldset;
use Omeka\Form\Element\PropertySelect;
use AdminAddon\TraitGeneral;

class SiteSettingsFieldset extends Fieldset
{

    use TraitGeneral;

    public function addFields($form): void
    {

        $form->add([
            'name' => 'adminaddon_advsearch_autocomplete',
            'type' => 'checkbox',
            'options' => [
                'element_group' => 'search',
                'label' => 'Autocomplete on Advanced search', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'id' => 'adminaddon_advsearch_autocomplete',
                'value' => $this->getSiteSets('adminaddon_advsearch_autocomplete'),
            ],
        ]);

        $form->add([
            'name' => 'adminaddon_advsearch_autocomplete_fields',
            'type' => PropertySelect::class,
            'required' => false,
            'options' => [
                'element_group' => 'search',
                'label' => 'Properties for autocomplete input fields in Advanced search', // @translate
                'info' => 'Select properties for autocomplete in input fields. Leave field empty for all properties.', // @translate
                'term_as_value' => true,
                'allow_empty' => true,
            ],
            'attributes' => [
                'multiple' => true,
                'class' => 'chosen-select',
                'data-placeholder' => 'Select properties…', // @translate
                'id' => 'adminaddon_advsearch_autocomplete_fields',
                'value' => $this->getSiteSets('adminaddon_advsearch_autocomplete_fields'),
            ],
        ]);
        
        $this->FilterAllowEmpty[] = 'adminaddon_advsearch_autocomplete_fields';

        $form->add([
            'type' => 'checkbox',
            'name' => 'adminaddon_search_fasets_enable',
            'options' => [
                'element_group' => 'search',
                'label' => 'Enable search fasets', // @translate
                'info' => 'Enable search fasets config', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'value' => $this->getSiteSets('adminaddon_search_fasets_enable'),
                'id' => 'adminaddon_search_fasets_enable',
            ],
        ]);

        $form->add([
            'name' => 'adminaddon_render_by_js',
            'type' => 'checkbox',
            'options' => [
                'element_group' => 'search',
                'label' => 'JS render search fasets', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'id' => 'adminaddon_render_by_js',
                'value' => $this->getSiteSets('adminaddon_render_by_js'),
            ],
        ]);

        $form->add([
            'name' => 'adminaddon_search_fasets',
            'type' => 'textarea',
            'options' => [
                'element_group' => 'search',
                'label' => 'Search fasets config', // @translate
                'info' => '', // @translate
            ],
            'attributes' => [
                'value' => $this->getSiteSets('adminaddon_search_fasets'),
                'id' => 'adminaddon_search_fasets',
                'class' => 'edit-ini-textarea'
            ],
        ]);

        $this->FilterAllowEmpty[] = 'adminaddon_search_fasets';

    }

}
