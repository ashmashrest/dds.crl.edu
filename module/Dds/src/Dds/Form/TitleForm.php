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

class TitleForm extends Form
{
    public function __construct()
    {
        parent::__construct('copy_agree_members');

        $this->setAttribute('method', 'post')
             ->setHydrator(new ClassMethodsHydrator(false))
             ->setInputFilter(new InputFilter());
         $this->add(array(
            'name' => 'tid',
            'options' => array(
                'label' => 'tid',
            ),
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));      
        $this->add(array(
            'name' => 'agree',
            'type' => 'Zend\Form\Element\Checkbox',
            'options' => array(
                'label' => '&nbsp; I agree to your terms and conditions',
            ),
            'attributes' => array(
                'type'  => 'checkbox',
                'required' => 'required',
                'checked' => false,
            ),
        ));
     
        $this->add(new Element\Csrf('security'));
        $this->add(array(
            'name' => 'send',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Continue',
                'class' => 'btn btn-primary',
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
