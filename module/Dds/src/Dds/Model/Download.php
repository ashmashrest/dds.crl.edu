<?php

namespace Dds\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\NotEmpty;

class Download implements InputFilterAwareInterface {

    public $id;
    public $scan_from; // Starting range of the download scan
    public $scan_to; // Ending range of the download scan
    protected $inputFilter;

    /**
     * Inititates the variables 
     * @param type $data
     */
    public function exchangeArray(array $data) {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->scan_from = (isset($data['scan-from'])) ? $data['scan-from'] : null;
        $this->scan_to = (isset($data['scan-to'])) ? $data['scan-to'] : null;
    }

    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new \Exception("Not used");
    }

    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();

            $inputFilter->add(
                    $factory->createInput(array(
                        'name' => 'id',
                        'required' => true,
                        'filters' => array(
                            array('name' => 'Int'),
                            array('name' => 'Zend\Filter\StringTrim'),
                        ),
                    ))
            );

            $inputFilter->add(
                    $factory->createInput(array(
                        'name' => 'scan-from',
                        'required' => true,
                        'allow_empty' => false,
                        'filters' => array(
                            array('name' => 'Int'),
                            array('name' => 'Zend\Filter\StringTrim'),
                        ),
                        'validators' => array(
                            array(
                                'name' => 'GreaterThan',
                                'options' => array(
                                    'min' => 1,
                                    'inclusive' => true,
                                     'messages' => array(
                                        \Zend\Validator\GreaterThan::NOT_GREATER => 'The range cannot be empty',
                                    ),
                                ),
                                'break_chain_on_failure' => true,
                            ),
                            array(
                                'name' => 'Callback',
                                'options' => array(
                                    'messages' => array(
                                        \Zend\Validator\Callback::INVALID_VALUE => 'The scan range from is greater than the scan range to',
                                    ),
                                    'callback' => function($value, $context = array()) {
                                // scan from value
                                $scan_from = $value;
                                // scan to value
                                $scan_to = $context['scan-to'];
                                // Check if the to value is greater than from
                                $isValid = $scan_to >= $scan_from;
                                return $isValid;
                            },
                                ),
                            ),
                            
                        ),
                    ))
            );
            $inputFilter->add(
                    $factory->createInput(array(
                        'name' => 'scan-to',
                        'required' => true,
                        'allow_empty' => false,
                        'filters' => array(
                            array('name' => 'Int'),
                        ),
                        'validators' => array(
                            array(
                                'name' => 'GreaterThan',
                                'options' => array(
                                    'min' => 1,
                                    'inclusive' => true,
                                    'messages' => array(
                                        \Zend\Validator\GreaterThan::NOT_GREATER => 'The range cannot be empty',
                                    ),
                                ),
                                'break_chain_on_failure' => true,
                            ),
                            
                            array(
                                'name' => 'Callback',
                                'options' => array(
                                    'messages' => array(
                                        \Zend\Validator\Callback::INVALID_VALUE => 'Cannot download more than 50 pages at a time',
                                        ),
                                    'callback' => function($value, $context = array()) {
                                        // scan from value
                                        $scan_to = $value;
                                        // scan to value
                                        $scan_from = $context['scan-from'];
                                        // check if the download pages are within the download limit
                                        $isValid = ($scan_to - $scan_from) < 50;
                                        return $isValid;
                                    },
                                ),
                            ),
                            
                        ),
                    ))
            );

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}
