<?php declare(strict_types=1);
namespace AdminAddon\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
// use Laminas\View\Model\ViewModel;
// use Laminas\Form\Form;
// use Omeka\Stdlib\Message;
use Laminas\View\Model\JsonModel;
use AdminAddon\General;

class AdminAddonController extends AbstractActionController
{

    use General;

    public function metadataFiltersAction()
    {

        $request = $this->getRequest();
        $query = $request->getQuery();

        return new JsonModel([
            'query' => $query
        ]);

    }

    public function autocompleteAction()
    {
        $request = $this->getRequest();
        $term = $request->getQuery('term', '');
        $property_id = $request->getQuery('property_id', '');
        $value = $request->getQuery('value', '');
        $total_count = 0;
        $suggestions = [];
        $success = False;
        if (!empty($term) || !empty($property_id)) {
            try {
                if(stripos($term, ':') !== False){
                    $needed = explode(':', $term);
                    $prefix = $needed[0];
                    $name = $needed[1];
                }else{
                    $prefix = False;
                    $name = $term;
                }
                $q = 'SELECT `value`.`id`, `value`.`value`, `value`.`type`, `value`.`lang`, `property`.`local_name`, `vocabulary`.`prefix` FROM `value`';
                $q .= ' LEFT JOIN `property` ON `property`.`id` = `value`.`property_id`';
                $q .= ' LEFT JOIN `vocabulary` ON `vocabulary`.`id` = `property`.`vocabulary_id`';

                if(!empty($property_id)){
                    $q .= ' WHERE `property`.`id` = \''.$property_id.'\'';
                }else{
                    $q .= ' WHERE `property`.`local_name` = \''.$name.'\'';
                    if($prefix){
                        $q .= ' AND `vocabulary`.`prefix` = \''.$prefix.'\'';
                    }
                }
                if(!empty($value)){
                    $q .= ' AND `value`.`value` LIKE \'%'.$value.'%\'';
                }
                $q .= ' GROUP BY `value`';
                $q .= ' ORDER BY `value`.`value` ASC';
                $q .= ' LIMIT 0, 20;';

                $rc = $this->getConnection()->executeQuery($q);
                if(!empty($rc)){
                    // $total_count[] = get_class_methods($rc);
                    // $total_count = ($rc->columnCount());
                    $total_count = ($rc->rowCount());
                    
                    foreach($rc->fetchAll() as $k => $v){

                        $suggestions[$k]['id'] = $v['id'];
                        $suggestions[$k]['label'] = $v['value'];
                        $suggestions[$k]['value'] = $v['value'];

                    }
                }
                $success = True;
            } catch (\Exception $e) {
                // Fallback: return empty suggestions if Reference API unavailable
                $suggestions = [];
                $success = False;
            }

        }
        
        return new JsonModel([
            'term' => $term,
            'property_id' => $property_id,
            'value' => $value,
            'total_count' => $total_count,
            'success' => $success,
            'suggestions' => $suggestions,
        ]);

    }

}
