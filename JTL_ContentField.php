<?php
/**
 * Text field that holds rich HTML content.
 * Editable with the GUI editor
 */

class JTL_ContentField extends JTL_SimpleField {
    function draw_fields($post = null) {
        $post_id = JTL_Field::Rectify_Post_Id($post);
        $data = $this->get($post_id);
        $data = $data ? $data : "";

        printf(
            '<textarea rows="5" cols="20" id="%s" class="jtl-content-field">%s</textarea>',
            $this->input_name,
            $data
        );
        ?>
        <script type="text/javascript">
            jQuery(function($){
                if (typeof tinyMCE === 'object' && typeof tinyMCE.execCommand === 'function')
                    tinyMCE.execCommand("mceAddControl", true, "<?php echo esc_attr($this->input_name); ?>");
            });
        </script><?php
    }
}
