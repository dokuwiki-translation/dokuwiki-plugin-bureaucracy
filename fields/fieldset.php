<?php
class syntax_plugin_bureaucracy_field_fieldset extends syntax_plugin_bureaucracy_field {
    var $mandatory_args = 1;

    function syntax_plugin_bureaucracy_field_fieldset($args) {
        // get standard arguments
        $this->opt = array('cmd' => array_shift($args));

        if (count($args) > 0) {
            $this->opt['label'] = array_shift($args);
            $this->opt['display'] = $this->opt['label'];
        }

        if (count($args) > 0) {
            $this->depends_on = $args;
        }
    }

    function renderfield($params, Doku_Form $form) {
        $form->startFieldset(hsc($this->getParam('display')));
        if (isset($this->depends_on)) {
            $dependencies = array_map('hsc',(array) $this->depends_on);
            if (count($this->depends_on) > 1) {
                $msg = 'Only edit this fieldset if ' .
                       '“<span class="bureaucracy_depends_fname">%s</span>” '.
                       'is set to “<span class="bureaucracy_depends_fvalue">%s</span>”.';
            } else {
                $msg = 'Only edit this fieldset if ' .
                       '“<span class="bureaucracy_depends_fname">%s</span>” is set.';
            }
            $form->addElement('<p class="bureaucracy_depends">' . vsprintf($msg, $dependencies) . '</p>');
        }
    }

    /**
     *
     * @param array $params
     *  when fieldset $params is an array of entries:
     *    [0] field value
     *    [1] my_id
     *    [2] data of fields
     * @return array|bool
     */
    function handle_post(&$params) {
        $my_id = $params[1];
        $data = &$params[2];

        if(!isset($this->depends_on)) {
            return true;
        }
        for ($n = 0 ; $n < $my_id; ++$n) {
            if ($data[$n]->getParam('label') != $this->depends_on[0]) {
                continue;
            }
            $hidden = (count($this->depends_on) > 1) ?
                      ($data[$n]->getParam('value') != $this->depends_on[1]) :
                      !($data[$n]->isSet_());
            break;
        }
        if ($hidden) {
            $this->hidden = true;
            for ($n = $my_id + 1 ; $n < count($data) ; ++$n) {
                if ($data[$n]->getFieldType() === 'fieldset') {
                    break;
                }
                $data[$n]->hidden = true;
            }
        }
        return true;
    }

    function getParam($name) {
        return ($name === 'value') ? null : parent::getParam($name);
    }
}
