<?php
/**
 * JTL_DateField.php
 *
 * @author: Jake
 * Date: 5/26/12
 * Time: 8:41 PM
 */

require_once 'JTL_SimpleField.php';


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

class JTL_DateRange extends JTL_Field{
    private $range_start;
    private $range_end;

    /**
     * Sets the label of this input element
     * @param $label string
     */
    public function set_label($label) {
        $label = parent::set_label($label);
        $this->range_start->label = $label . ' start';
        $this->range_end->label = $label . ' end';
    }

    public function __construct($name) {
        parent::__construct($name);
        $this->range_end = new JTL_DateField($this->name . '_end');
        $this->range_start = new JTL_DateField($this->name . '_start');

        $this->set_label($this->label);
    }

    public function save($post_id, $data) {
        $this->range_start->save($post_id, $data['start']);
        $this->range_end->save($post_id, $data['end']);
    }

    public function data_from_submission() {
        return array(
            'start' => $this->range_start->data_from_submission(),
            'end' => $this->range_end->data_from_submission()
        );
    }

    public function draw($post = null, $no_wrapper = false) {
        if (! $no_wrapper) $this->draw_header();
        $this->range_start->draw($post);
        $this->range_end->draw($post);
        if (! $no_wrapper) $this->draw_footer();
    }

    public function get($post_id) {
        return array(
            'start' => $this->range_start->get($post_id),
            'end' => $this->range_end->get($post_id)
        );
    }
}