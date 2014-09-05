<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dds\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class DownloadForm extends Form {

    public function __construct() {
        parent::__construct('download');

        $this->setAttribute('method', 'post')
                ->setHydrator(new ClassMethodsHydrator(false))
                ->setInputFilter(new InputFilter());
        $this->add(array(
            'name' => 'id',
            'options' => array(
                'label' => 'id',
            ),
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'scan-from',
            'options' => array(
                'value_options' => 'From',
                'label' => 'Download a Range of Sequential Image numbers:',
            ),
            'attribute' => array(
                'id' => 'scan-from',
                'required' => 'required',
                'filters' => array(
                    array('name' => 'Int'),
                ),
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'scan-to',
            'options' => array(
                'value_options' => 'To',
                'label' => 'to',
            ),
            'attribute' => array(
                'id' => 'scan-to',
                'required' => 'required',
                'pattern'  => '^0[1-68]([-. ]?[0-9]{2}){4}$',
                'filters' => array(
                    array('name' => 'Int'),
                ),
            ),
        ));

        $this->add(new Element\Csrf('security'));
        $this->add(array(
            'name' => 'send',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Download',
                'class' => 'btn btn-primary  btn-sm',
            ),
        ));
    }
    
     public function prepareElements()
    {
        // add() can take either an Element/Fieldset instance,
        // or a specification, from which the appropriate object
        // will be built.

       

        // We could also define the input filter here, or
        // lazy-create it in the getInputFilter() method.
    }

}

?>
