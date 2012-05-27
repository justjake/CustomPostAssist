<?php
/**
 * Created by JetBrains PhpStorm.
 * User: just.jake@BERKELEY.EDU
 * Date: 5/25/12
 * Time: 2:35 PM
 * To change this template use File | Settings | File Templates.
 */

require_once 'JTL_Field.php';

class JTL_SimpleField extends JTL_Field{
    public $label = 'Simple Form Field';

    protected function draw_label() {
        ?><label for="<?php echo $this->input_name ?>">
        <?php echo esc_attr($this->label); ?>
    </label><?php
    }

    public function draw_input($data) {
        printf('<input type="text" id="%s" name="%s" value="%s" />',
               $this->input_name,
               $this->input_name,
               $data
        );
    }

    protected function draw_fields_with_data($data) {
        // print label
        $this->draw_label();

        // print imput
        $this->draw_input($data);
    }

    protected function draw_header() {
        echo "<p>\n";
    }

    protected function draw_footer() {
        echo "</p>\n";
    }

    public  function data_from_submission() {
        if ($_POST && isset($_POST[$this->input_name]))
            return $_POST[$this->input_name];
        return "";
    }

    public function display($post_id) {
        echo $this->get($post_id);
    }
}



class JTL_HiddenField extends JTL_SimpleField {
    protected function draw_fields_with_data($data) {
        printf('<input type="hidden" id="%s" name="%s" value="%s" />',
               $this->input_name,
               $this->input_name,
               $data
        );
    }
    protected function draw_label() {} // do nothing: hidden field needs no label
    protected function draw_header() {}
    protected function draw_footer() {}
}




