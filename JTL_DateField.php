<?php
/**
 * JTL_DateField.php
 *
 * @author: Jake
 * Date: 5/26/12
 * Time: 8:41 PM
 */

require_once 'JTL_SimpleField.php';
require_once 'JTL_CompositeField.php';


class JTL_DateField extends JTL_SimpleField {
    public $label = 'Date Field';

    public function draw_input($data) {
        printf('<input type="date" name="%s" id="%s" value="%s" />',
            $this->input_name,
            $this->input_name,
            $data
        );
    }
}

class JTL_DateRange extends JTL_CompositeField {
    protected $subfields = array(
        'range_start' => null,
        'range_end' => null
    );

    protected function subfield_init($role) {
        return new JTL_DateField($this->name . '_' . $role);
    }

    protected function subfield_label($role, $label) {
        switch ($role) {
            case 'range_start':
                return $label . ' began';
            case 'range_end':
                return $label . ' ended';
        }
    }

    public function display($post_id) {
        $data = $this->get($post_id);
        printf('%s - %s',
            $data['range_start'],
            $data['range_end']
        );
    }
}