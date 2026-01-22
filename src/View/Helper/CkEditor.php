<?php declare(strict_types=1);

namespace AdminAddon\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * View helper for loading scripts necessary to use CKEditor on a page.
 *
 * Override core view helper to load a specific config.
 *
 * Used in various modules:
 * @see \Omeka\View\Helper\CkEditor
 * @see \AdminAddon\View\Helper\CkEditor
 * @see \DataTypeRdf\View\Helper\CkEditor
 */
class CkEditor extends AbstractHelper
{
    /**
     * Load the scripts necessary to use CKEditor on a page.
     */
    public function __invoke(): void
    {
        static $loaded;

        if (!is_null($loaded)) {
            return;
        }

        $loaded = true;

        $view = $this->getView();
        $plugins = $view->getHelperPluginManager();
        $assetUrl = $plugins->get('assetUrl');
        $escapeJs = $plugins->get('escapeJs');
        $params = $view->params();

        $isAdmin = $params->fromRoute('__ADMIN__');
        $isSiteAdmin = $params->fromRoute('__SITEADMIN__');
        $controller = $params->fromRoute('__CONTROLLER__');
        $action = $params->fromRoute('action');

        $isSiteAdminPage = $isSiteAdmin
            && ($controller === 'Page' || $controller === 'page')
            && $action === 'edit';

        $isSiteAdminResource = $isAdmin
            && in_array($controller, ['Item', 'ItemSet', 'Media', 'Annotation', 'item', 'item-set', 'media', 'annotation'])
            && ($action === 'edit' || $action === 'add');

        $script = '';
        $customConfigJs = 'js/ckeditor/config.js';
        if ($isSiteAdminPage || $isSiteAdminResource) {
            $setting = $plugins->get('setting');
            $pageOrResource = $isSiteAdminPage ? 'page' : 'resource';
            $module = $isSiteAdminPage ? 'adminaddon' : 'datatyperdf';
            $editorMode = $setting($module . '_html_mode_' . $pageOrResource);
            if ($editorMode && $editorMode !== 'inline') {
                $script = <<<JS
                    CKEDITOR.config.customHtmlMode = '$editorMode';
                    JS . "\n";
            }

            $editorConfig = $setting($module . '_html_config_' . $pageOrResource);
            if ($editorConfig && $editorConfig !== 'default') {
                $customConfigJs = $editorConfig && $editorConfig !== 'default'
                    ? 'js/ckeditor/config_' . $editorConfig . '.js'
                    : 'js/ckeditor/config.js';
            }
        }

        $customConfigUrl = $escapeJs($assetUrl($customConfigJs, 'AdminAddon'));
        $script .= <<<JS
            CKEDITOR.config.language = 'uk';
            CKEDITOR.config.customConfig = '$customConfigUrl';
            JS;

        // The footnotes icon is not loaded automaically, so add css.
        // Only this css rule is needed.
        // The js for block-plus-admin is already loaded with the blocks.
        // $view->headLink()
            // ->appendStylesheet($assetUrl('css/block-plus-admin.css', 'AdminAddon'));

        $view->headScript()
            // Don't use defer for now.
            ->appendFile($assetUrl('vendor/ckeditor/ckeditor.js', 'Omeka'))
            // ->appendFile($assetUrl('vendor/ckeditor-footnotes/footnotes/plugin.js', 'AdminAddon'), 'text/javascript', ['defer' => 'defer'])
            ->appendFile($assetUrl('vendor/ckeditor/adapters/jquery.js', 'Omeka'))
            ->appendScript($script);
    }
}
